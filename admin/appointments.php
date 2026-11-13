<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$adminUser = auth_require_admin();
$pageTitle = __('admin_appointments');
$activeNav = 'appointments';
$pdo       = db();

$allowedStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled', 'Rejected'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (isset($_POST['update_status_id'], $_POST['new_status'])) {
        $apptId = filter_input(INPUT_POST, 'update_status_id', FILTER_VALIDATE_INT);
        $newStatus = trim($_POST['new_status']);
        if ($apptId && in_array($newStatus, $allowedStatuses, true)) {
            try {
                $stmtUpd = $pdo->prepare("UPDATE appointments SET appointment_status = :status WHERE appointment_id = :id");
                $stmtUpd->execute([':status' => $newStatus, ':id' => $apptId]);
                audit_log('ADMIN_UPDATE_APPOINTMENT_STATUS', "Appointment #$apptId -> $newStatus", (int)$adminUser['id']);
                flash_set('success', lang_raw('appt_status_updated', ['id' => $apptId, 'status' => translate_status($newStatus)]));
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    flash_set('danger', lang_raw('err_slot_conflict'));
                } else {
                    error_log('[ADMIN APPT UPDATE] ' . $e->getMessage());
                    flash_set('danger', lang_raw('appt_status_failed'));
                }
            }
        }
        header('Location: ' . DENTAL_BASE_URL . '/admin/appointments.php');
        exit;
    }

    if (isset($_POST['delete_id'])) {
        $delId = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
        if ($delId && $delId > 0) {
            $stmtDel = $pdo->prepare("DELETE FROM appointments WHERE appointment_id = :id");
            $stmtDel->execute([':id' => $delId]);
            audit_log('ADMIN_DELETE_APPOINTMENT', "Deleted appointment #$delId", (int)$adminUser['id']);
            flash_set('success', lang_raw('appt_deleted', ['id' => $delId]));
        }
        header('Location: ' . DENTAL_BASE_URL . '/admin/appointments.php');
        exit;
    }
}

$filterDate    = trim($_GET['date'] ?? '');
$filterDentist = filter_input(INPUT_GET, 'dentist_id', FILTER_VALIDATE_INT) ?: '';
$filterStatus  = trim($_GET['status'] ?? '');

$sql = "SELECT a.*, p.patient_name, p.patient_email, p.phone_number,
               d.dentist_name, d.dentist_name_ar,
               s.service_name, s.service_name_en, s.service_name_ar, s.service_price
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        JOIN dentists d ON a.dentist_id = d.dentist_id
        JOIN services s ON a.service_id = s.service_id";
$where = [];
$params = [];

if ($filterDate !== '' && validate_date($filterDate)) {
    $where[] = "a.appointment_date = :adate";
    $params[':adate'] = $filterDate;
}
if ($filterDentist !== '' && $filterDentist > 0) {
    $where[] = "a.dentist_id = :did";
    $params[':did'] = (int)$filterDentist;
}
if ($filterStatus !== '' && in_array($filterStatus, $allowedStatuses, true)) {
    $where[] = "COALESCE(a.appointment_status, 'Pending') = :status";
    $params[':status'] = $filterStatus;
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

$dentists = $pdo->query("SELECT dentist_id, dentist_name, dentist_name_ar FROM dentists ORDER BY dentist_name ASC")->fetchAll();

require_once __DIR__ . '/../templates/admin/layout_start.php';
?>

<form method="get" action="" class="luxury-card p-3 p-md-4 mb-4">
  <div class="row g-3 align-items-end">
    <div class="col-md-3">
      <label class="luxury-form-label"><?php _e('select_date'); ?></label>
      <input type="date" name="date" class="form-control luxury-input" value="<?php echo e($filterDate); ?>">
    </div>
    <div class="col-md-3">
      <label class="luxury-form-label"><?php _e('step_2_dentist'); ?></label>
      <select name="dentist_id" class="form-select luxury-select">
        <option value="">— <?php _e('filter_all'); ?> —</option>
        <?php foreach ($dentists as $d): ?>
          <option value="<?php echo (int)$d['dentist_id']; ?>" <?php echo ((int)$filterDentist === (int)$d['dentist_id']) ? 'selected' : ''; ?>>
            <?php echo e(dentist_name($d)); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="luxury-form-label"><?php _e('table_status'); ?></label>
      <select name="status" class="form-select luxury-select">
        <option value="">— <?php _e('filter_all'); ?> —</option>
        <?php foreach ($allowedStatuses as $st): ?>
          <option value="<?php echo e($st); ?>" <?php echo ($filterStatus === $st) ? 'selected' : ''; ?>>
            <?php echo translate_status($st); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <button type="submit" class="btn btn-luxury-primary flex-grow-1"><?php _e('filter_apply'); ?></button>
      <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/appointments.php" class="btn btn-luxury-outline"><?php _e('filter_reset'); ?></a>
    </div>
  </div>
</form>

<div class="luxury-card-static p-0 overflow-hidden" style="border-top: 2px solid var(--dental-gold-champagne);">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="bg-ivory small text-muted-luxury text-uppercase">
        <tr>
          <th class="px-4 py-3"><?php _e('table_id'); ?></th>
          <th class="py-3"><?php _e('patient_full_name'); ?></th>
          <th class="py-3"><?php _e('step_2_dentist'); ?></th>
          <th class="py-3"><?php _e('step_1_service'); ?></th>
          <th class="py-3"><?php _e('select_date'); ?></th>
          <th class="py-3"><?php _e('table_status'); ?></th>
          <th class="px-4 py-3 text-end"><?php _e('action_update_status'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($appointments)): ?>
          <tr><td colspan="7" class="text-center py-5 text-muted-luxury"><?php _e('portal_no_appointments'); ?></td></tr>
        <?php else: ?>
          <?php foreach ($appointments as $row): ?>
            <tr>
              <td class="px-4 fw-semibold text-emerald">#<?php echo (int)$row['appointment_id']; ?></td>
              <td>
                <div class="fw-semibold"><?php echo e($row['patient_name']); ?></div>
                <div class="small text-muted-luxury"><?php echo e($row['phone_number']); ?></div>
              </td>
              <td><?php echo e(dentist_name($row)); ?></td>
              <td>
                <div class="fw-semibold text-capitalize"><?php echo e(service_name($row)); ?></div>
                <div class="small text-muted-luxury"><?php echo e($row['service_price']); ?></div>
              </td>
              <td>
                <div><?php echo e(format_localized_date($row['appointment_date'])); ?></div>
                <div class="small text-muted-luxury"><?php echo e(date('g:i A', strtotime($row['appointment_time']))); ?> - <?php echo e(date('g:i A', strtotime($row['end_time']))); ?></div>
              </td>
              <td>
                <span class="status-badge <?php echo e(status_badge_class($row['appointment_status'])); ?>">
                  <?php echo translate_status($row['appointment_status']); ?>
                </span>
              </td>
              <td class="px-4 text-end">
                <div class="d-flex justify-content-end gap-2 align-items-center">
                  <form method="post" action="" class="d-flex gap-1">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="update_status_id" value="<?php echo (int)$row['appointment_id']; ?>">
                    <select name="new_status" class="form-select form-select-sm luxury-select py-1" style="min-height: 32px; width: 130px;">
                      <?php foreach ($allowedStatuses as $st): ?>
                        <option value="<?php echo e($st); ?>" <?php echo (($row['appointment_status'] ?? 'Pending') === $st) ? 'selected' : ''; ?>>
                          <?php echo translate_status($st); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-luxury-outline py-1 px-2" aria-label="<?php _e('action_update_status'); ?>">
                      <i class="bi bi-check2"></i>
                    </button>
                  </form>
                  <form method="post" action="" onsubmit="return confirm('<?php _e('confirm_cancel_prompt'); ?>');">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="delete_id" value="<?php echo (int)$row['appointment_id']; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="<?php _e('action_delete'); ?>" aria-label="<?php _e('action_delete'); ?>">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../templates/admin/layout_end.php'; ?>
