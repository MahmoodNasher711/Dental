<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$portalUser = auth_require_patient();
$pageTitle  = __('portal_dashboard');
$activeNav  = 'dashboard';

$uid = (int)$portalUser['id'];
$pdo = db();

// Fetch strictly user-owned appointments
$stmtAppts = $pdo->prepare(
    "SELECT a.*, d.dentist_name, d.dentist_name_ar, d.specialization, d.specialization_ar, d.image AS dentist_image,
            s.service_name, s.service_name_en, s.service_name_ar, s.service_price
     FROM appointments a
     JOIN patients p ON a.patient_id = p.patient_id
     JOIN dentists d ON a.dentist_id = d.dentist_id
     JOIN services s ON a.service_id = s.service_id
     WHERE p.user_id = :uid
     ORDER BY a.appointment_date ASC, a.appointment_time ASC"
);
$stmtAppts->execute([':uid' => $uid]);
$allAppointments = $stmtAppts->fetchAll();

$today = date('Y-m-d');
$upcoming = [];
$completedCount = 0;

foreach ($allAppointments as $appt) {
    $status = strtolower((string)($appt['appointment_status'] ?? 'Pending'));
    if ($status === 'completed') {
        $completedCount++;
    }
    if ($appt['appointment_date'] >= $today && !in_array($status, ['cancelled', 'rejected', 'completed'], true)) {
        $upcoming[] = $appt;
    }
}

$nextAppt = $upcoming[0] ?? null;

require_once __DIR__ . '/../templates/patient/layout_start.php';
?>

<div class="mb-4">
  <h2 class="h3 text-emerald mb-1"><?php _e('portal_welcome', ['name' => $portalUser['username']]); ?></h2>
  <p class="text-muted-luxury small mb-0"><?php _e('brand_tagline'); ?></p>
</div>

<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="kpi-card">
      <div>
        <div class="small text-muted-luxury"><?php _e('portal_upcoming'); ?></div>
        <div class="h2 text-emerald mb-0 fw-bold"><?php echo count($upcoming); ?></div>
      </div>
      <div class="kpi-icon-box"><i class="bi bi-calendar2-event"></i></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="kpi-card">
      <div>
        <div class="small text-muted-luxury"><?php _e('kpi_completed_appts'); ?></div>
        <div class="h2 text-emerald mb-0 fw-bold"><?php echo $completedCount; ?></div>
      </div>
      <div class="kpi-icon-box"><i class="bi bi-check2-circle"></i></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="kpi-card">
      <div>
        <div class="small text-muted-luxury"><?php _e('kpi_total_appointments'); ?></div>
        <div class="h2 text-emerald mb-0 fw-bold"><?php echo count($allAppointments); ?></div>
      </div>
      <div class="kpi-icon-box"><i class="bi bi-journal-medical"></i></div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="luxury-card p-4 h-100">
      <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <h3 class="h5 text-emerald mb-0"><?php _e('portal_upcoming'); ?></h3>
        <a href="<?php echo e(DENTAL_BASE_URL); ?>/booking.php" class="btn btn-luxury-primary btn-sm">
          <i class="bi bi-plus-circle me-1"></i> <?php _e('nav_book'); ?>
        </a>
      </div>

      <?php if ($nextAppt): ?>
        <div class="p-4 rounded bg-ivory border d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          <div class="d-flex align-items-center gap-3">
            <img src="<?php echo e(get_dentist_photo_url($nextAppt['dentist_image'])); ?>" alt="Doctor" class="avatar-circle" width="60" height="60">
            <div>
              <span class="status-badge <?php echo e(status_badge_class($nextAppt['appointment_status'])); ?> mb-2">
                <?php echo translate_status($nextAppt['appointment_status']); ?>
              </span>
              <h4 class="h5 text-emerald mb-1"><?php echo e(dentist_name($nextAppt)); ?></h4>
              <div class="small text-muted-luxury text-capitalize">
                <?php echo e(service_name($nextAppt) . ' — ' . $nextAppt['service_price']); ?>
              </div>
            </div>
          </div>
          <div class="text-md-end">
            <div class="fw-bold text-emerald"><?php echo e(format_localized_date($nextAppt['appointment_date'])); ?></div>
            <div class="small text-muted-luxury mb-2"><?php echo e(date('g:i A', strtotime($nextAppt['appointment_time']))); ?></div>
            <a href="<?php echo e(DENTAL_BASE_URL); ?>/patient/appointments.php" class="btn btn-luxury-outline btn-sm py-1 px-3">
              <?php _e('action_view'); ?>
            </a>
          </div>
        </div>
      <?php else: ?>
        <div class="text-center py-5">
          <i class="bi bi-calendar2-x fs-1 text-muted-luxury d-block mb-3"></i>
          <p class="text-muted-luxury mb-3"><?php _e('portal_no_appointments'); ?></p>
          <a href="<?php echo e(DENTAL_BASE_URL); ?>/booking.php" class="btn btn-luxury-primary"><?php _e('hero_cta_book'); ?></a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="luxury-card p-4 h-100">
      <h3 class="h5 text-emerald mb-3 pb-2 border-bottom"><?php _e('portal_profile'); ?></h3>
      <div class="text-center mb-3">
        <img src="<?php echo e(get_user_avatar_url($portalUser['image'])); ?>" alt="Avatar" class="avatar-circle mb-2" width="84" height="84">
        <div class="fw-bold text-emerald"><?php echo e($portalUser['username']); ?></div>
        <div class="small text-muted-luxury"><?php echo e($portalUser['email']); ?></div>
      </div>
      <div class="d-grid gap-2 mt-4">
        <a href="<?php echo e(DENTAL_BASE_URL); ?>/patient/profile.php" class="btn btn-luxury-outline btn-sm"><?php _e('portal_profile'); ?></a>
        <a href="<?php echo e(DENTAL_BASE_URL); ?>/patient/security.php" class="btn btn-luxury-outline btn-sm"><?php _e('portal_security'); ?></a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../templates/patient/layout_end.php'; ?>
