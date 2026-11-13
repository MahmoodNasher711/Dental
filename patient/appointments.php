<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$portalUser = auth_require_patient();
$pageTitle  = __('portal_appointments');
$activeNav  = 'appointments';
$uid        = (int)$portalUser['id'];
$pdo        = db();

// Handle strict ownership-checked cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment_id'])) {
    csrf_verify();
    $apptId = filter_input(INPUT_POST, 'cancel_appointment_id', FILTER_VALIDATE_INT);

    if ($apptId && $apptId > 0) {
        $stmtVerify = $pdo->prepare(
            "SELECT a.appointment_id, a.appointment_date, a.appointment_status
             FROM appointments a
             JOIN patients p ON a.patient_id = p.patient_id
             WHERE a.appointment_id = :aid AND p.user_id = :uid
             LIMIT 1"
        );
        $stmtVerify->execute([':aid' => $apptId, ':uid' => $uid]);
        $row = $stmtVerify->fetch();

        if ($row) {
            $status = strtolower((string)($row['appointment_status'] ?? 'Pending'));
            if ($row['appointment_date'] >= date('Y-m-d') && !in_array($status, ['cancelled', 'completed', 'rejected'], true)) {
                $stmtCancel = $pdo->prepare("UPDATE appointments SET appointment_status = 'Cancelled' WHERE appointment_id = :aid");
                $stmtCancel->execute([':aid' => $apptId]);
                audit_log('PATIENT_CANCEL_APPOINTMENT', "Patient cancelled appointment #$apptId", $uid);
                flash_set('success', lang_raw('msg_appt_cancelled'));
            }
        } else {
            audit_log('IDOR_ATTEMPT_CANCEL', "User #$uid attempted to cancel appointment #$apptId", $uid);
            flash_set('danger', lang_raw('err_unauthorized'));
        }
    }
    header('Location: ' . DENTAL_BASE_URL . '/patient/appointments.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT a.*, d.dentist_name, d.dentist_name_ar, d.specialization, d.specialization_ar,
            s.service_name, s.service_name_en, s.service_name_ar, s.service_price
     FROM appointments a
     JOIN patients p ON a.patient_id = p.patient_id
     JOIN dentists d ON a.dentist_id = d.dentist_id
     JOIN services s ON a.service_id = s.service_id
     WHERE p.user_id = :uid
     ORDER BY a.appointment_date DESC, a.appointment_time DESC"
);
$stmt->execute([':uid' => $uid]);
$appointments = $stmt->fetchAll();

require_once __DIR__ . '/../templates/patient/layout_start.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <p class="text-muted-luxury small mb-0"><?php _e('portal_appointments'); ?></p>
  <a href="<?php echo e(DENTAL_BASE_URL); ?>/booking.php" class="btn btn-luxury-primary btn-sm">
    <i class="bi bi-plus-circle me-1"></i> <?php _e('nav_book'); ?>
  </a>
</div>

<div class="luxury-card p-0 overflow-hidden">
  <?php if (empty($appointments)): ?>
    <div class="p-5 text-center">
      <i class="bi bi-calendar2-x fs-1 text-muted-luxury d-block mb-3"></i>
      <p class="text-muted-luxury mb-3"><?php _e('portal_no_appointments'); ?></p>
      <a href="<?php echo e(DENTAL_BASE_URL); ?>/booking.php" class="btn btn-luxury-primary"><?php _e('hero_cta_book'); ?></a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="bg-ivory">
          <tr class="small text-uppercase text-muted-luxury">
            <th class="px-4 py-3">#</th>
            <th class="py-3"><?php _e('step_1_service'); ?></th>
            <th class="py-3"><?php _e('step_2_dentist'); ?></th>
            <th class="py-3"><?php _e('select_date'); ?></th>
            <th class="py-3"><?php _e('available_slots'); ?></th>
            <th class="py-3"><?php _e('table_status'); ?></th>
            <th class="px-4 py-3 text-end"><?php _e('action_view'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($appointments as $row): ?>
            <?php
              $status = strtolower((string)($row['appointment_status'] ?? 'Pending'));
              $canCancel = ($row['appointment_date'] >= date('Y-m-d')) && !in_array($status, ['cancelled', 'completed', 'rejected'], true);
            ?>
            <tr>
              <td class="px-4 fw-semibold text-emerald">#<?php echo (int)$row['appointment_id']; ?></td>
              <td>
                <div class="fw-semibold text-capitalize"><?php echo e(service_name($row)); ?></div>
                <div class="small text-muted-luxury"><?php echo e($row['service_price']); ?></div>
              </td>
              <td>
                <div><?php echo e(dentist_name($row)); ?></div>
                <?php $spec = dentist_specialization($row); ?>
                <?php if (!empty($spec)): ?>
                  <div class="small text-muted-luxury"><?php echo e($spec); ?></div>
                <?php endif; ?>
              </td>
              <td><?php echo e(format_localized_date($row['appointment_date'])); ?></td>
              <td><?php echo e(date('g:i A', strtotime($row['appointment_time']))); ?></td>
              <td>
                <span class="status-badge <?php echo e(status_badge_class($row['appointment_status'])); ?>">
                  <?php echo translate_status($row['appointment_status']); ?>
                </span>
              </td>
              <td class="px-4 text-end">
                <?php if ($canCancel): ?>
                  <form method="post" action="" class="d-inline" onsubmit="return confirm('<?php _e('confirm_cancel_prompt'); ?>');">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="cancel_appointment_id" value="<?php echo (int)$row['appointment_id']; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      <?php _e('btn_cancel_appt'); ?>
                    </button>
                  </form>
                <?php else: ?>
                  <span class="small text-muted-luxury">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/patient/layout_end.php'; ?>
