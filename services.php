<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = __('services_page_title');
$activeNav = 'services';

$services = db()->query("SELECT * FROM services WHERE is_active = 1 ORDER BY service_id ASC")->fetchAll();

require_once __DIR__ . '/templates/public/header.php';
?>

<section class="py-5 bg-emerald-deep">
  <div class="container py-3">
    <span class="eyebrow-label" style="color: var(--dental-gold-soft);"><?php _e('services_eyebrow'); ?></span>
    <h1 class="display-5 text-white mb-0"><?php _e('services_page_title'); ?></h1>
  </div>
</section>

<section class="section-pad bg-ivory">
  <div class="container">
    <?php if (empty($services)): ?>
      <div class="luxury-card p-5 text-center">
        <p class="text-muted-luxury mb-0"><?php _e('no_services_found'); ?></p>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($services as $srv): ?>
          <div class="col-md-6 col-lg-4">
            <div class="luxury-card p-4 h-100 d-flex flex-column justify-content-between">
              <div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div class="kpi-icon-box"><i class="bi bi-stars"></i></div>
                  <span class="badge px-3 py-2 fs-6" style="background: var(--dental-emerald-deep); color: var(--dental-gold-soft);">
                    <?php echo e($srv['service_price']); ?>
                  </span>
                </div>
                <h2 class="h3 text-emerald text-capitalize mb-2"><?php echo e(service_name($srv)); ?></h2>
                <div class="gold-divider"></div>
                <p class="text-muted-luxury small mb-3">
                  <?php echo !empty(service_description($srv)) ? e(service_description($srv)) : __('home_services_sub'); ?>
                </p>
                <p class="small text-muted-luxury mb-4">
                  <i class="bi bi-clock text-gold me-1"></i>
                  <?php _e('services_duration'); ?>: <strong><?php echo (int)$srv['duration_minutes']; ?> <?php _e('minutes'); ?></strong>
                </p>
              </div>
              <a href="<?php echo e(DENTAL_BASE_URL); ?>/booking.php?service_id=<?php echo (int)$srv['service_id']; ?>" class="btn btn-luxury-primary w-100">
                <?php _e('book_this_service'); ?> <i class="bi bi-arrow-right icon-directional"></i>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/templates/public/footer.php'; ?>
