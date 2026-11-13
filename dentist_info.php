<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = __('doctor_profile_title');
$activeNav = 'doctors';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$doctor = null;
if ($id && $id > 0) {
    $stmt = db()->prepare("SELECT * FROM dentists WHERE dentist_id = :id AND is_active = 1 LIMIT 1");
    $stmt->execute([':id' => $id]);
    $doctor = $stmt->fetch();
}

if (!$doctor) {
    http_response_code(404);
}

require_once __DIR__ . '/templates/public/header.php';
?>

<section class="section-pad bg-ivory">
  <div class="container">
    <?php if ($doctor): ?>
      <div class="mb-4">
        <a href="<?php echo e(DENTAL_BASE_URL); ?>/doctors.php" class="text-decoration-none text-emerald fw-semibold small">
          <i class="bi bi-arrow-left icon-directional me-1"></i> <?php _e('back_to_doctors'); ?>
        </a>
      </div>
      <div class="luxury-card p-4 p-md-5">
        <div class="row g-5 align-items-center">
          <div class="col-lg-5">
            <div class="hero-image-frame">
              <img src="<?php echo e(get_dentist_photo_url($doctor['image'])); ?>" alt="<?php echo e(dentist_name($doctor)); ?>" style="height: 440px; object-position: top center;">
            </div>
          </div>
          <div class="col-lg-7">
            <?php $spec = dentist_specialization($doctor); ?>
            <?php if (!empty($spec)): ?>
              <span class="eyebrow-label"><?php echo e($spec); ?></span>
            <?php endif; ?>
            <h1 class="display-5 text-emerald mb-3"><?php echo e(dentist_name($doctor)); ?></h1>
            <div class="gold-divider"></div>

            <div class="row g-3 mb-4">
              <div class="col-sm-6">
                <div class="p-3 bg-ivory rounded border">
                  <div class="small text-muted-luxury"><?php _e('doctor_spec_label'); ?></div>
                  <div class="fw-semibold text-emerald"><?php echo e($spec ?: '-'); ?></div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="p-3 bg-ivory rounded border">
                  <div class="small text-muted-luxury"><?php _e('doctor_age_label'); ?></div>
                  <div class="fw-semibold text-emerald"><?php echo (int)$doctor['dentist_age']; ?> <?php _e('doctor_years'); ?></div>
                </div>
              </div>
            </div>

            <h2 class="h5 text-emerald mb-2"><?php _e('doctor_bio_label'); ?></h2>
            <p class="text-muted-luxury mb-4" style="line-height: 1.85;">
              <?php echo nl2br(e(dentist_bio($doctor))); ?>
            </p>

            <a href="<?php echo e(DENTAL_BASE_URL); ?>/booking.php?dentist_id=<?php echo (int)$doctor['dentist_id']; ?>" class="btn btn-luxury-primary">
              <i class="bi bi-calendar-check"></i> <?php _e('book_with_doctor'); ?>
            </a>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="luxury-card p-5 text-center">
        <h2 class="text-emerald mb-3"><?php _e('doctor_not_found'); ?></h2>
        <a href="<?php echo e(DENTAL_BASE_URL); ?>/doctors.php" class="btn btn-luxury-primary"><?php _e('back_to_doctors'); ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/templates/public/footer.php'; ?>
