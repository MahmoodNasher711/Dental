<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$adminUser = auth_require_admin();
$pageTitle = __('admin_dashboard');
$activeNav = 'dashboard';
$pdo       = db();

// Real metrics derived strictly from the database (zero fabricated metrics)
$totalPatients  = (int)$pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$activeDentists = (int)$pdo->query("SELECT COUNT(*) FROM dentists WHERE is_active = 1")->fetchColumn();
$totalAppts     = (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$pendingAppts   = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE COALESCE(appointment_status, 'Pending') = 'Pending'")->fetchColumn();
$confirmedAppts = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_status = 'Confirmed'")->fetchColumn();
$completedAppts = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_status = 'Completed'")->fetchColumn();
$cancelledAppts = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_status IN ('Cancelled', 'Rejected')")->fetchColumn();
$unreadMessages = (int)$pdo->query("SELECT COUNT(*) FROM contact_us WHERE is_read = 0")->fetchColumn();

$recentAppointments = $pdo->query(
    "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.appointment_status,
            p.patient_name, d.dentist_name, d.dentist_name_ar,
            s.service_name, s.service_name_en, s.service_name_ar
     FROM appointments a
     JOIN patients p ON a.patient_id = p.patient_id
     JOIN dentists d ON a.dentist_id = d.dentist_id
     JOIN services s ON a.service_id = s.service_id
     ORDER BY a.created_at DESC, a.appointment_id DESC
     LIMIT 6"
)->fetchAll();

$recentUsers = $pdo->query(
    "SELECT id, username, email, role, add_date
     FROM users
     ORDER BY id DESC
     LIMIT 5"
)->fetchAll();

require_once __DIR__ . '/../templates/admin/layout_start.php';
?>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div>
        <div class="small text-muted-luxury"><?php _e('kpi_total_patients'); ?></div>
        <div class="h3 text-emerald fw-bold mb-0"><?php echo $totalPatients; ?></div>
      </div>
      <div class="kpi-icon-box"><i class="bi bi-people"></i></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div>
        <div class="small text-muted-luxury"><?php _e('kpi_total_dentists'); ?></div>
        <div class="h3 text-emerald fw-bold mb-0"><?php echo $activeDentists; ?></div>
      </div>
      <div class="kpi-icon-box"><i class="bi bi-person-badge"></i></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div>
        <div class="small text-muted-luxury"><?php _e('kpi_total_appointments'); ?></div>
        <div class="h3 text-emerald fw-bold mb-0"><?php echo $totalAppts; ?></div>
      </div>
      <div class="kpi-icon-box"><i class="bi bi-calendar2-check"></i></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div>
        <div class="small text-muted-luxury"><?php _e('kpi_pending_appts'); ?></div>
        <div class="h3 text-gold-dark fw-bold mb-0"><?php echo $pendingAppts; ?></div>
      </div>
      <div class="kpi-icon-box"><i class="bi bi-hourglass-split"></i></div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div>
        <div class="small text-muted-luxury"><?php _e('kpi_confirmed_appts'); ?></div>
        <div class="h4 text-emerald fw-bold mb-0"><?php echo $confirmedAppts; ?></div>
      </div>
      <div class="kpi-icon-box"><i class="bi bi-check-circle"></i></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div>
        <div class="small text-muted-luxury"><?php _e('kpi_completed_appts'); ?></div>
        <div class="h4 text-emerald fw-bold mb-0"><?php echo $completedAppts; ?></div>
      </div>
      <div class="kpi-icon-box"><i class="bi bi-clipboard2-check"></i></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div>
        <div class="small text-muted-luxury"><?php _e('kpi_cancelled_appts'); ?></div>
        <div class="h4 text-danger fw-bold mb-0"><?php echo $cancelledAppts; ?></div>
      </div>
      <div class="kpi-icon-box"><i class="bi bi-x-circle"></i></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div>
        <div class="small text-muted-luxury"><?php _e('kpi_unread_messages'); ?></div>
        <div class="h4 text-emerald fw-bold mb-0"><?php echo $unreadMessages; ?></div>
      </div>
      <div class="kpi-icon-box"><i class="bi bi-envelope"></i></div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="luxury-card p-0 overflow-hidden h-100">
      <div class="p-3 px-4 border-bottom d-flex justify-content-between align-items-center bg-pure-white">
        <h2 class="h6 text-emerald fw-bold mb-0"><?php _e('recent_appointments'); ?></h2>
        <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/appointments.php" class="btn btn-luxury-outline btn-sm py-1 px-3"><?php _e('action_view'); ?></a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-ivory small text-muted-luxury">
            <tr>
              <th class="px-4 py-2">#</th>
              <th class="py-2"><?php _e('patient_full_name'); ?></th>
              <th class="py-2"><?php _e('step_2_dentist'); ?></th>
              <th class="py-2"><?php _e('select_date'); ?></th>
              <th class="px-4 py-2"><?php _e('table_status'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentAppointments)): ?>
              <tr><td colspan="5" class="text-center py-4 text-muted-luxury small"><?php _e('portal_no_appointments'); ?></td></tr>
            <?php else: ?>
              <?php foreach ($recentAppointments as $r): ?>
                <tr>
                  <td class="px-4 fw-semibold">#<?php echo (int)$r['appointment_id']; ?></td>
                  <td><?php echo e($r['patient_name']); ?></td>
                  <td><?php echo e(dentist_name($r)); ?></td>
                  <td class="small">
                    <?php echo e(format_localized_date($r['appointment_date'])); ?><br>
                    <span class="text-muted-luxury"><?php echo e(date('g:i A', strtotime($r['appointment_time']))); ?></span>
                  </td>
                  <td class="px-4">
                    <span class="status-badge <?php echo e(status_badge_class($r['appointment_status'])); ?>">
                      <?php echo translate_status($r['appointment_status']); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="luxury-card p-0 overflow-hidden h-100">
      <div class="p-3 px-4 border-bottom d-flex justify-content-between align-items-center bg-pure-white">
        <h2 class="h6 text-emerald fw-bold mb-0"><?php _e('admin_users'); ?></h2>
        <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/users.php" class="btn btn-luxury-outline btn-sm py-1 px-3"><?php _e('action_view'); ?></a>
      </div>
      <div class="list-group list-group-flush">
        <?php foreach ($recentUsers as $u): ?>
          <div class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
            <div class="min-w-0">
              <div class="fw-semibold text-emerald text-truncate small"><?php echo e($u['username']); ?></div>
              <div class="text-muted-luxury text-truncate" style="font-size: 0.78rem;"><?php echo e($u['email']); ?></div>
            </div>
            <span class="badge <?php echo ($u['role'] === 'admin') ? 'bg-emerald-deep text-gold' : 'bg-light text-dark border'; ?>">
              <?php echo e(translate_role($u['role'])); ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../templates/admin/layout_end.php'; ?>
