<?php
require_once __DIR__ . '/includes/bootstrap.php';

$authUser  = auth_require_login();
$pageTitle = __('booking_success_title');
$activeNav = 'booking';
$ref       = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT) ?: ($_SESSION['last_booking_ref'] ?? null);

$appointment = null;
if ($ref) {
    $stmt = db()->prepare(
        "SELECT a.*, d.dentist_name, d.dentist_name_ar, d.specialization, d.specialization_ar,
                s.service_name, s.service_name_en, s.service_name_ar, s.service_price, p.user_id
         FROM appointments a
         JOIN dentists d ON a.dentist_id = d.dentist_id
         JOIN services s ON a.service_id = s.service_id
         JOIN patients p ON a.patient_id = p.patient_id
         WHERE a.appointment_id = :id
         LIMIT 1"
    );
    $stmt->execute([':id' => (int)$ref]);
    $row = $stmt->fetch();
    if ($row && ((int)$row['user_id'] === (int)$authUser['id'] || ($authUser['role'] ?? '') === 'admin')) {
        $appointment = $row;
    }
}

if (!$appointment) {
    flash_set('warning', lang_raw('err_unauthorized'));
    header('Location: ' . DENTAL_BASE_URL . (($authUser['role'] ?? '') === 'admin' ? '/admin/appointments.php' : '/patient/appointments.php'));
    exit;
}

require_once __DIR__ . '/templates/public/header.php';
?>

<section class="section-pad bg-ivory">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="luxury-card p-5 text-center">
          <div class="kpi-icon-box mx-auto mb-4" style="width: 72px; height: 72px; font-size: 2rem; color: var(--dental-emerald-deep);">
            <i class="bi bi-check2-circle"></i>
          </div>
          <h1 class="h2 text-emerald mb-2"><?php _e('booking_success_title'); ?></h1>
          <p class="text-muted-luxury mb-4"><?php _e('booking_success_msg'); ?></p>

          <?php if ($appointment): ?>
            <div class="bg-ivory p-4 rounded border text-start mb-4">
              <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted-luxury small"><?php _e('booking_ref'); ?></span>
                <strong class="text-emerald">#<?php echo (int)$appointment['appointment_id']; ?></strong>
              </div>
              <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted-luxury small"><?php _e('step_1_service'); ?></span>
                <strong class="text-capitalize"><?php echo e(service_name($appointment) . ' (' . $appointment['service_price'] . ')'); ?></strong>
              </div>
              <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted-luxury small"><?php _e('step_2_dentist'); ?></span>
                <strong><?php echo e(dentist_name($appointment)); ?></strong>
              </div>
              <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted-luxury small"><?php _e('select_date'); ?></span>
                <strong><?php echo e(format_localized_date($appointment['appointment_date'])); ?></strong>
              </div>
              <div class="d-flex justify-content-between py-2">
                <span class="text-muted-luxury small"><?php _e('available_slots'); ?></span>
                <strong><?php echo e(date('g:i A', strtotime($appointment['appointment_time']))); ?></strong>
              </div>
            </div>
          <?php endif; ?>

          <div class="d-flex flex-wrap justify-content-center gap-3">
            <?php if (auth_is_logged_in()): ?>
              <a href="<?php echo e(DENTAL_BASE_URL); ?>/patient/appointments.php" class="btn btn-luxury-primary"><?php _e('portal_appointments'); ?></a>
            <?php endif; ?>
            <a href="<?php echo e(DENTAL_BASE_URL); ?>/index.php" class="btn btn-luxury-outline"><?php _e('nav_home'); ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/templates/public/footer.php'; ?>
