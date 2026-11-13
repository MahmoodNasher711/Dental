<?php
/**
 * DENTAL Management System — Concurrency-Safe Appointment Booking Engine
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

/**
 * Computes candidate clinical time slots and marks active/overlapping conflicts.
 */
function booking_get_available_slots(int $dentistId, string $date, int $serviceId = 0): array
{
    if (!validate_date($date) || $dentistId <= 0) {
        return [];
    }

    if ($date < date('Y-m-d')) {
        return [];
    }

    $pdo = db();
    $settings = get_clinic_settings();
    $workStart = $settings['work_hours_start'] ?? '09:00';
    $workEnd   = $settings['work_hours_end'] ?? '17:00';
    $interval  = max(15, (int)($settings['slot_duration_minutes'] ?? 30));

    // Determine requested service duration
    $serviceDuration = $interval;
    if ($serviceId > 0) {
        $stmtSrv = $pdo->prepare("SELECT duration_minutes FROM services WHERE service_id = :sid AND is_active = 1 LIMIT 1");
        $stmtSrv->execute([':sid' => $serviceId]);
        $srv = $stmtSrv->fetch();
        if ($srv && (int)$srv['duration_minutes'] > 0) {
            $serviceDuration = (int)$srv['duration_minutes'];
        }
    }

    // Fetch existing active appointments for this dentist & date
    $stmtBusy = $pdo->prepare(
        "SELECT appointment_time, COALESCE(end_time, ADDTIME(appointment_time, '00:30:00')) AS end_time
         FROM appointments
         WHERE dentist_id = :did
           AND appointment_date = :adate
           AND COALESCE(appointment_status, 'Pending') NOT IN ('Cancelled', 'Rejected')"
    );
    $stmtBusy->execute([':did' => $dentistId, ':adate' => $date]);
    $busyRanges = $stmtBusy->fetchAll();

    $slots = [];
    $startTs = strtotime("$date $workStart:00");
    $endTs   = strtotime("$date $workEnd:00");
    $nowTs   = time();

    for ($cursor = $startTs; ($cursor + ($serviceDuration * 60)) <= $endTs; $cursor += ($interval * 60)) {
        $slotStartStr = date('H:i:s', $cursor);
        $slotEndStr   = date('H:i:s', $cursor + ($serviceDuration * 60));
        $available    = true;

        // Disable past times if booking for today
        if ($date === date('Y-m-d') && $cursor <= $nowTs + 900) {
            $available = false;
        }

        // Interval overlap test: (existing_start < candidate_end) AND (existing_end > candidate_start)
        if ($available) {
            foreach ($busyRanges as $busy) {
                if ($busy['appointment_time'] < $slotEndStr && $busy['end_time'] > $slotStartStr) {
                    $available = false;
                    break;
                }
            }
        }

        $slots[] = [
            'time'      => $slotStartStr,
            'label'     => date('g:i A', $cursor),
            'available' => $available,
        ];
    }

    return $slots;
}

/**
 * Executes a transaction-locked, concurrency-safe appointment reservation.
 * Strictly requires an authenticated user and binds patient ownership from session.
 */
function booking_create_appointment(array $input, ?array $authUser = null): array
{
    if ($authUser === null || empty($authUser['id']) || empty($authUser['email'])) {
        return ['success' => false, 'error_key' => 'booking_auth_required'];
    }

    $authUserId = (int)$authUser['id'];
    $serviceId  = filter_var($input['service_id'] ?? null, FILTER_VALIDATE_INT);
    $dentistId  = filter_var($input['dentist_id'] ?? null, FILTER_VALIDATE_INT);
    $date       = trim((string)($input['appointment_date'] ?? ''));
    $rawTime    = trim((string)($input['appointment_time'] ?? ''));
    $notes      = trim((string)($input['notes'] ?? ''));

    // Derive authoritative email strictly from authenticated user (never trust client-submitted email/ID)
    $pEmail  = trim((string)$authUser['email']);
    $pName   = trim((string)($input['patient_name'] ?? $authUser['username'] ?? ''));
    $pPhone  = trim((string)($input['patient_phone'] ?? ''));
    $pAge    = $input['patient_age'] ?? '';
    $pGender = trim((string)($input['patient_gender'] ?? ''));

    // 1. Validate core inputs
    if (!$serviceId || !$dentistId || $date === '' || $rawTime === '' || $pName === '' || $pEmail === '' || $pPhone === '' || $pAge === '' || $pGender === '') {
        return ['success' => false, 'error_key' => 'err_required_fields'];
    }
    if (!validate_name($pName)) {
        return ['success' => false, 'error_key' => 'err_invalid_name'];
    }
    if (!validate_email($pEmail)) {
        return ['success' => false, 'error_key' => 'err_invalid_email'];
    }
    if (!validate_phone($pPhone)) {
        return ['success' => false, 'error_key' => 'err_invalid_phone'];
    }
    if (!validate_age($pAge)) {
        return ['success' => false, 'error_key' => 'err_invalid_age'];
    }
    if (!in_array($pGender, ['1', '2', 'Male', 'Female'], true)) {
        return ['success' => false, 'error_key' => 'err_required_fields'];
    }
    if (!validate_date($date) || $date < date('Y-m-d')) {
        return ['success' => false, 'error_key' => 'err_past_date'];
    }
    if (!validate_time($rawTime)) {
        return ['success' => false, 'error_key' => 'err_required_fields'];
    }

    $startTime = normalize_time($rawTime);
    $pdo = db();

    try {
        // Fetch active service duration
        $stmtSrv = $pdo->prepare("SELECT service_id, duration_minutes FROM services WHERE service_id = :sid AND is_active = 1 LIMIT 1");
        $stmtSrv->execute([':sid' => $serviceId]);
        $srvRow = $stmtSrv->fetch();
        if (!$srvRow) {
            return ['success' => false, 'error_key' => 'err_required_fields'];
        }
        $duration = max(15, (int)$srvRow['duration_minutes']);
        $endTimeTs = strtotime("$date $startTime") + ($duration * 60);
        $endTime   = date('H:i:s', $endTimeTs);

        // Verify slot falls inside clinic hours
        $settings  = get_clinic_settings();
        $workStart = ($settings['work_hours_start'] ?? '09:00') . ':00';
        $workEnd   = ($settings['work_hours_end'] ?? '17:00') . ':00';
        if ($startTime < $workStart || $endTime > $workEnd) {
            return ['success' => false, 'error_key' => 'err_slot_conflict'];
        }

        // 2. Begin atomic serialized transaction
        $pdo->beginTransaction();

        // Pessimistic Lock on the existing Dentist row serializes all concurrent bookings for this dentist
        $stmtLock = $pdo->prepare("SELECT dentist_id FROM dentists WHERE dentist_id = :did AND is_active = 1 FOR UPDATE");
        $stmtLock->execute([':did' => $dentistId]);
        if (!$stmtLock->fetch()) {
            $pdo->rollBack();
            return ['success' => false, 'error_key' => 'err_required_fields'];
        }

        // Overlap check inside locked transaction
        $stmtOverlap = $pdo->prepare(
            "SELECT appointment_id
             FROM appointments
             WHERE dentist_id = :did
               AND appointment_date = :adate
               AND COALESCE(appointment_status, 'Pending') NOT IN ('Cancelled', 'Rejected')
               AND appointment_time < :new_end
               AND COALESCE(end_time, ADDTIME(appointment_time, '00:30:00')) > :new_start
             LIMIT 1"
        );
        $stmtOverlap->execute([
            ':did'       => $dentistId,
            ':adate'     => $date,
            ':new_end'   => $endTime,
            ':new_start' => $startTime,
        ]);

        if ($stmtOverlap->fetch()) {
            $pdo->rollBack();
            return ['success' => false, 'error_key' => 'err_slot_conflict'];
        }

        // 3. Resolve or Create Single Patient Record strictly bound to authenticated user_id
        $patientId = null;
        $stmtPatUser = $pdo->prepare("SELECT patient_id FROM patients WHERE user_id = :uid LIMIT 1");
        $stmtPatUser->execute([':uid' => $authUserId]);
        $rowPat = $stmtPatUser->fetch();
        if ($rowPat) {
            $patientId = (int)$rowPat['patient_id'];
        }

        if ($patientId === null) {
            $stmtPatEmail = $pdo->prepare("SELECT patient_id, user_id FROM patients WHERE patient_email = :email LIMIT 1");
            $stmtPatEmail->execute([':email' => $pEmail]);
            $rowPatEmail = $stmtPatEmail->fetch();
            if ($rowPatEmail) {
                if ($rowPatEmail['user_id'] !== null && (int)$rowPatEmail['user_id'] !== $authUserId) {
                    $pdo->rollBack();
                    audit_log('IDOR_ATTEMPT_BOOKING', "User #$authUserId email collision on patient #" . (int)$rowPatEmail['patient_id'], $authUserId);
                    return ['success' => false, 'error_key' => 'err_unauthorized'];
                }
                $patientId = (int)$rowPatEmail['patient_id'];
                if ($rowPatEmail['user_id'] === null) {
                    $stmtLink = $pdo->prepare("UPDATE patients SET user_id = :uid WHERE patient_id = :pid AND user_id IS NULL");
                    $stmtLink->execute([':uid' => $authUserId, ':pid' => $patientId]);
                }
            }
        }

        if ($patientId !== null) {
            // Update contact info on strictly owned patient record
            $stmtUpd = $pdo->prepare(
                "UPDATE patients SET patient_name = :name, patient_age = :age, phone_number = :phone, patient_gender = :gender WHERE patient_id = :pid AND user_id = :uid"
            );
            $stmtUpd->execute([
                ':name'   => $pName,
                ':age'    => (int)$pAge,
                ':phone'  => $pPhone,
                ':gender' => $pGender,
                ':pid'    => $patientId,
                ':uid'    => $authUserId
            ]);
        } else {
            // Insert single new patient record linked to authenticated user
            $stmtInsPat = $pdo->prepare(
                "INSERT INTO patients (user_id, patient_name, patient_age, patient_email, ppassword, phone_number, patient_gender)
                 VALUES (:uid, :name, :age, :email, NULL, :phone, :gender)"
            );
            $stmtInsPat->execute([
                ':uid'    => $authUserId,
                ':name'   => $pName,
                ':age'    => (int)$pAge,
                ':email'  => $pEmail,
                ':phone'  => $pPhone,
                ':gender' => $pGender
            ]);
            $patientId = (int)$pdo->lastInsertId();
        }

        // Prevent duplicate/overlapping active booking by the same patient on the same date/time
        $stmtDupPat = $pdo->prepare(
            "SELECT appointment_id
             FROM appointments
             WHERE patient_id = :pid
               AND appointment_date = :adate
               AND COALESCE(appointment_status, 'Pending') NOT IN ('Cancelled', 'Rejected')
               AND appointment_time < :new_end
               AND COALESCE(end_time, ADDTIME(appointment_time, '00:30:00')) > :new_start
             LIMIT 1"
        );
        $stmtDupPat->execute([
            ':pid'       => $patientId,
            ':adate'     => $date,
            ':new_end'   => $endTime,
            ':new_start' => $startTime,
        ]);
        if ($stmtDupPat->fetch()) {
            $pdo->rollBack();
            return ['success' => false, 'error_key' => 'booking_duplicate_error'];
        }

        // 4. Insert Appointment
        $stmtApp = $pdo->prepare(
            "INSERT INTO appointments (patient_id, dentist_id, service_id, appointment_date, appointment_time, duration_minutes, end_time, appointment_status, notes)
             VALUES (:pid, :did, :sid, :adate, :atime, :dur, :etime, 'Pending', :notes)"
        );
        $stmtApp->execute([
            ':pid'   => $patientId,
            ':did'   => $dentistId,
            ':sid'   => $serviceId,
            ':adate' => $date,
            ':atime' => $startTime,
            ':dur'   => $duration,
            ':etime' => $endTime,
            ':notes' => $notes !== '' ? $notes : null
        ]);

        $appointmentId = (int)$pdo->lastInsertId();
        $pdo->commit();

        audit_log('APPOINTMENT_CREATED', "Appointment #$appointmentId reserved for Dentist #$dentistId on $date $startTime", $authUserId);

        return [
            'success'        => true,
            'appointment_id' => $appointmentId,
            'patient_id'     => $patientId
        ];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Catch MySQL unique index duplicate slot collision (SQLSTATE 23000)
        if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'idx_unique_active_slot')) {
            return ['success' => false, 'error_key' => 'err_slot_conflict'];
        }
        error_log('[DENTAL BOOKING ENGINE ERROR] ' . $e->getMessage());
        return ['success' => false, 'error_key' => 'err_required_fields'];
    }
}
