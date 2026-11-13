<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$adminUser = auth_require_admin();
$pageTitle = __('admin_patients');
$activeNav = 'patients';
$pdo       = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pid    = filter_input(INPUT_POST, 'edit_patient_id', FILTER_VALIDATE_INT);
    $name   = trim($_POST['patient_name'] ?? '');
    $phone  = trim($_POST['phone_number'] ?? '');
    $age    = $_POST['patient_age'] ?? '';
    $gender = $_POST['patient_gender'] ?? '1';

    if ($pid && validate_name($name) && validate_phone($phone) && validate_age($age)) {
        $stmt = $pdo->prepare(
            "UPDATE patients SET patient_name = :name, phone_number = :phone,
                    patient_age = :age, patient_gender = :gen WHERE patient_id = :id"
        );
        $stmt->execute([
            ':name'  => $name,
            ':phone' => $phone,
            ':age'   => (int)$age,
            ':gen'   => ($gender === '2') ? '2' : '1',
            ':id'    => $pid
        ]);
        audit_log('ADMIN_EDIT_PATIENT', "Updated patient #$pid ($name)", (int)$adminUser['id']);
        flash_set('success', lang_raw('msg_profile_updated'));
    } else {
        flash_set('danger', lang_raw('err_required_fields'));
    }
    header('Location: ' . DENTAL_BASE_URL . '/admin/patients.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
$sql = "SELECT p.*, COUNT(a.appointment_id) AS total_visits
        FROM patients p
        LEFT JOIN appointments a ON p.patient_id = a.patient_id";
$params = [];
if ($search !== '') {
    $sql .= " WHERE p.patient_name LIKE :kw OR p.patient_email LIKE :kw2 OR p.phone_number LIKE :kw3";
    $like = '%' . $search . '%';
    $params = [':kw' => $like, ':kw2' => $like, ':kw3' => $like];
}
$sql .= " GROUP BY p.patient_id ORDER BY p.patient_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll();

require_once __DIR__ . '/../templates/admin/layout_start.php';
?>

<form method="get" action="" class="luxury-card p-3 p-md-4 mb-4">
  <div class="row g-3 align-items-center">
    <div class="col-md-8">
      <input type="search" name="q" class="form-control luxury-input" placeholder="<?php _e('search_placeholder'); ?>" value="<?php echo e($search); ?>">
    </div>
    <div class="col-md-4 d-flex gap-2">
      <button type="submit" class="btn btn-luxury-primary flex-grow-1"><?php _e('filter_apply'); ?></button>
      <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/patients.php" class="btn btn-luxury-outline"><?php _e('filter_reset'); ?></a>
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
          <th class="py-3"><?php _e('patient_email'); ?></th>
          <th class="py-3"><?php _e('patient_phone'); ?></th>
          <th class="py-3"><?php _e('patient_age'); ?></th>
          <th class="py-3"><?php _e('patient_gender'); ?></th>
          <th class="py-3"><?php _e('admin_appointments'); ?></th>
          <th class="px-4 py-3 text-end"><?php _e('table_actions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($patients)): ?>
          <tr><td colspan="8" class="text-center py-5 text-muted-luxury"><?php _e('no_patients_found'); ?></td></tr>
        <?php else: ?>
          <?php foreach ($patients as $row): ?>
            <tr>
              <td class="px-4 fw-semibold">#<?php echo (int)$row['patient_id']; ?></td>
              <td>
                <div class="fw-semibold text-emerald"><?php echo e($row['patient_name']); ?></div>
                <?php if ($row['user_id']): ?>
                  <span class="badge bg-light text-dark border" style="font-size:0.7rem;"><?php _e('user_header'); ?> #<?php echo (int)$row['user_id']; ?></span>
                <?php endif; ?>
              </td>
              <td><?php echo e($row['patient_email']); ?></td>
              <td><?php echo e($row['phone_number']); ?></td>
              <td><?php echo (int)$row['patient_age']; ?></td>
              <td><?php echo ($row['patient_gender'] === '2') ? __('gender_female') : __('gender_male'); ?></td>
              <td><span class="badge bg-ivory text-emerald border"><?php echo (int)$row['total_visits']; ?></span></td>
              <td class="px-4 text-end">
                <button type="button" class="btn btn-sm btn-luxury-outline py-1 px-2" data-bs-toggle="modal" data-bs-target="#editPatModal<?php echo (int)$row['patient_id']; ?>" aria-label="<?php _e('action_edit'); ?>">
                  <i class="bi bi-pencil me-1"></i> <?php _e('action_edit'); ?>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Edit Patient Modals (Placed cleanly outside table hierarchy) -->
<?php foreach ($patients as $row): ?>
  <div class="modal fade" id="editPatModal<?php echo (int)$row['patient_id']; ?>" tabindex="-1" aria-labelledby="editPatModalLabel<?php echo (int)$row['patient_id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form method="post" action="" class="modal-content" data-prevent-double-submit="true">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="edit_patient_id" value="<?php echo (int)$row['patient_id']; ?>">
        <div class="modal-header">
          <h5 class="modal-title text-emerald" id="editPatModalLabel<?php echo (int)$row['patient_id']; ?>">
            <i class="bi bi-person-lines-fill text-gold me-2"></i><?php _e('action_edit'); ?> — <?php echo e($row['patient_name']); ?>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php _e('modal_close'); ?>"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="luxury-form-label"><?php _e('patient_full_name'); ?> <span class="text-danger">*</span></label>
            <input type="text" name="patient_name" class="form-control luxury-input" value="<?php echo e($row['patient_name']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="luxury-form-label"><?php _e('patient_phone'); ?> <span class="text-danger">*</span></label>
            <input type="text" name="phone_number" class="form-control luxury-input" value="<?php echo e($row['phone_number']); ?>" required>
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label class="luxury-form-label"><?php _e('patient_age'); ?> <span class="text-danger">*</span></label>
              <input type="number" name="patient_age" class="form-control luxury-input" min="1" max="120" value="<?php echo (int)$row['patient_age']; ?>" required>
            </div>
            <div class="col-6">
              <label class="luxury-form-label"><?php _e('patient_gender'); ?></label>
              <select name="patient_gender" class="form-select luxury-select">
                <option value="1" <?php echo ($row['patient_gender'] === '1') ? 'selected' : ''; ?>><?php _e('gender_male'); ?></option>
                <option value="2" <?php echo ($row['patient_gender'] === '2') ? 'selected' : ''; ?>><?php _e('gender_female'); ?></option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-luxury-outline" data-bs-dismiss="modal"><?php _e('modal_close'); ?></button>
          <button type="submit" class="btn btn-luxury-primary"><?php _e('btn_save_changes'); ?></button>
        </div>
      </form>
    </div>
  </div>
<?php endforeach; ?>

<?php require_once __DIR__ . '/../templates/admin/layout_end.php'; ?>
