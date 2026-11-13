<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = __('doctors_page_title');
$activeNav = 'doctors';

$dentists = db()->query("SELECT * FROM dentists WHERE is_active = 1 ORDER BY dentist_id ASC")->fetchAll();

require_once __DIR__ . '/templates/public/header.php';
?>

<section class="py-5 bg-emerald-deep">
  <div class="container py-3">
    <span class="eyebrow-label" style="color: var(--dental-gold-soft);"><?php _e('doctors_eyebrow'); ?></span>
    <h1 class="display-5 text-white mb-0"><?php _e('doctors_page_title'); ?></h1>
  </div>
</section>

<section class="section-pad bg-ivory">
  <div class="container">
    <div class="row g-4">
      <?php foreach ($dentists as $doc): ?>
        <div class="col-md-6 col-lg-4">
          <div class="luxury-card h-100 d-flex flex-column">
            <div class="dentist-card-img-wrap">
              <img src="<?php echo e(get_dentist_photo_url($doc['image'])); ?>" alt="<?php echo e(dentist_name($doc)); ?>">
              <?php $spec = dentist_specialization($doc); ?>
              <?php if (!empty($spec)): ?>
                <span class="dentist-spec-badge"><?php echo e($spec); ?></span>
              <?php endif; ?>
            </div>
            <div class="p-4 flex-grow-1 d-flex flex-column justify-content-between">
              <div>
                <h2 class="h3 text-emerald mb-2"><?php echo e(dentist_name($doc)); ?></h2>
                <div class="small text-muted-luxury mb-3">
                  <i class="bi bi-person-badge text-gold me-1"></i>
                  <?php _e('doctor_age_label'); ?>: <?php echo (int)$doc['dentist_age']; ?> <?php _e('doctor_years'); ?>
                </div>
                <p class="text-muted-luxury small mb-4" style="line-height: 1.75;">
                  <?php echo nl2br(e(dentist_bio($doc))); ?>
                </p>
              </div>
              <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo e(DENTAL_BASE_URL); ?>/dentist_info.php?id=<?php echo (int)$doc['dentist_id']; ?>" class="btn btn-luxury-outline btn-sm flex-grow-1">
                  <?php _e('view_profile'); ?>
                </a>
                <a href="<?php echo e(DENTAL_BASE_URL); ?>/booking.php?dentist_id=<?php echo (int)$doc['dentist_id']; ?>" class="btn btn-luxury-primary btn-sm flex-grow-1">
                  <?php _e('book_with_doctor'); ?>
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/templates/public/footer.php'; ?>
