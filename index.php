<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = __('nav_home');
$activeNav = 'home';

$services = db()->query("SELECT * FROM services WHERE is_active = 1 ORDER BY service_id ASC")->fetchAll();
$dentists = db()->query("SELECT * FROM dentists WHERE is_active = 1 ORDER BY dentist_id ASC")->fetchAll();
$aboutRow = db()->query("SELECT about_text, about_text_ar FROM about_us ORDER BY about_id DESC LIMIT 1")->fetch();

global $CURRENT_LANG;
$aboutSnippet = '';
if ($aboutRow) {
    $rawAbout = ($CURRENT_LANG === 'ar' && !empty($aboutRow['about_text_ar']))
        ? $aboutRow['about_text_ar']
        : $aboutRow['about_text'];
    $aboutSnippet = mb_substr(trim($rawAbout), 0, 260) . '...';
}

require_once __DIR__ . '/templates/public/header.php';
?>

<section class="luxury-hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <span class="eyebrow-label"><?php _e('hero_eyebrow'); ?></span>
        <h1 class="luxury-hero-title"><?php _e('hero_title'); ?></h1>
        <p class="luxury-hero-subtitle"><?php _e('hero_subtitle'); ?></p>
        <div class="d-flex flex-wrap gap-3">
          <a href="<?php echo e(DENTAL_BASE_URL); ?>/booking.php" class="btn btn-luxury-primary">
            <i class="bi bi-calendar-check"></i> <?php _e('hero_cta_book'); ?>
          </a>
          <a href="<?php echo e(DENTAL_BASE_URL); ?>/services.php" class="btn btn-luxury-outline">
            <?php _e('hero_cta_services'); ?>
            <i class="bi bi-arrow-right icon-directional"></i>
          </a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-image-frame">
          <img src="<?php echo e(DENTAL_BASE_URL); ?>/assets/images/clinic/hero-suite.jpg" alt="<?php _e('hero_suite_caption'); ?>">
          <div class="p-3 d-flex align-items-center justify-content-between" style="background: rgba(18, 61, 54, 0.94); color: var(--dental-ivory-warm);">
            <span class="small"><i class="bi bi-stars text-gold me-2"></i><?php _e('hero_suite_caption'); ?></span>
            <span class="small text-gold fw-semibold">DENTAL</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section-pad bg-pure-white border-bottom" style="border-color: var(--dental-border-subtle) !important;">
  <div class="container">
    <div class="text-center mb-5">
      <span class="eyebrow-label"><?php _e('pillars_eyebrow'); ?></span>
      <h2 class="h1 text-emerald"><?php _e('pillars_title'); ?></h2>
      <div class="gold-divider mx-auto"></div>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="luxury-card p-4 h-100">
          <div class="kpi-icon-box mb-3"><i class="bi bi-gem"></i></div>
          <h3 class="h4 text-emerald mb-2"><?php _e('pillar_1_title'); ?></h3>
          <p class="text-muted-luxury mb-0 small"><?php _e('pillar_1_desc'); ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="luxury-card p-4 h-100">
          <div class="kpi-icon-box mb-3"><i class="bi bi-award"></i></div>
          <h3 class="h4 text-emerald mb-2"><?php _e('pillar_2_title'); ?></h3>
          <p class="text-muted-luxury mb-0 small"><?php _e('pillar_2_desc'); ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="luxury-card p-4 h-100">
          <div class="kpi-icon-box mb-3"><i class="bi bi-shield-lock"></i></div>
          <h3 class="h4 text-emerald mb-2"><?php _e('pillar_3_title'); ?></h3>
          <p class="text-muted-luxury mb-0 small"><?php _e('pillar_3_desc'); ?></p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section-pad bg-ivory">
  <div class="container">
    <div class="row align-items-end mb-5 g-3">
      <div class="col-lg-8">
        <span class="eyebrow-label"><?php _e('home_services_eyebrow'); ?></span>
        <h2 class="h1 text-emerald mb-2"><?php _e('home_services_title'); ?></h2>
        <p class="text-muted-luxury mb-0"><?php _e('home_services_sub'); ?></p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a href="<?php echo e(DENTAL_BASE_URL); ?>/services.php" class="btn btn-luxury-outline">
          <?php _e('view_all_services'); ?> <i class="bi bi-arrow-right icon-directional"></i>
        </a>
      </div>
    </div>

    <div class="row g-4">
      <?php foreach ($services as $srv): ?>
        <div class="col-md-6">
          <div class="luxury-card p-4 h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <h3 class="h3 text-emerald text-capitalize mb-0"><?php echo e(service_name($srv)); ?></h3>
                <span class="badge fs-6 fw-semibold px-3 py-2" style="background: var(--dental-ivory-warm); color: var(--dental-emerald-deep); border: 1px solid var(--dental-gold-champagne);">
                  <?php echo e($srv['service_price']); ?>
                </span>
              </div>
              <?php $srvDesc = service_description($srv); ?>
              <?php if (!empty($srvDesc)): ?>
                <p class="text-muted-luxury small mb-3"><?php echo e($srvDesc); ?></p>
              <?php endif; ?>
              <div class="small text-muted-luxury mb-4">
                <i class="bi bi-clock text-gold me-1"></i>
                <?php _e('services_duration'); ?>: <?php echo (int)$srv['duration_minutes']; ?> <?php _e('minutes'); ?>
              </div>
            </div>
            <div>
              <a href="<?php echo e(DENTAL_BASE_URL); ?>/booking.php?service_id=<?php echo (int)$srv['service_id']; ?>" class="btn btn-luxury-primary btn-sm">
                <?php _e('book_this_service'); ?> <i class="bi bi-arrow-right icon-directional"></i>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-pad bg-pure-white border-top border-bottom" style="border-color: var(--dental-border-subtle) !important;">
  <div class="container">
    <div class="row align-items-end mb-5 g-3">
      <div class="col-lg-8">
        <span class="eyebrow-label"><?php _e('home_doctors_eyebrow'); ?></span>
        <h2 class="h1 text-emerald mb-2"><?php _e('home_doctors_title'); ?></h2>
        <p class="text-muted-luxury mb-0"><?php _e('home_doctors_sub'); ?></p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a href="<?php echo e(DENTAL_BASE_URL); ?>/doctors.php" class="btn btn-luxury-outline">
          <?php _e('view_all_doctors'); ?> <i class="bi bi-arrow-right icon-directional"></i>
        </a>
      </div>
    </div>

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
                <h3 class="h3 text-emerald mb-2"><?php echo e(dentist_name($doc)); ?></h3>
                <p class="text-muted-luxury small mb-4">
                  <?php echo e(mb_substr(trim(dentist_bio($doc)), 0, 145)); ?>...
                </p>
              </div>
              <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo e(DENTAL_BASE_URL); ?>/dentist_info.php?id=<?php echo (int)$doc['dentist_id']; ?>" class="btn btn-luxury-outline btn-sm flex-grow-1">
                  <?php _e('view_profile'); ?>
                </a>
                <a href="<?php echo e(DENTAL_BASE_URL); ?>/booking.php?dentist_id=<?php echo (int)$doc['dentist_id']; ?>" class="btn btn-luxury-primary btn-sm flex-grow-1">
                  <?php _e('nav_book'); ?>
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($aboutSnippet !== ''): ?>
<section class="section-pad bg-ivory">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="hero-image-frame">
          <img src="<?php echo e(DENTAL_BASE_URL); ?>/assets/images/clinic/about-lounge.jpg" alt="DENTAL Lounge" style="height:390px;">
        </div>
      </div>
      <div class="col-lg-6">
        <span class="eyebrow-label"><?php _e('home_about_eyebrow'); ?></span>
        <h2 class="h1 text-emerald mb-3"><?php _e('home_about_title'); ?></h2>
        <div class="gold-divider"></div>
        <p class="text-muted-luxury mb-4" style="line-height: 1.85;"><?php echo e($aboutSnippet); ?></p>
        <a href="<?php echo e(DENTAL_BASE_URL); ?>/about.php" class="btn btn-luxury-primary">
          <?php _e('read_our_story'); ?> <i class="bi bi-arrow-right icon-directional"></i>
        </a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="py-5 bg-emerald-deep">
  <div class="container py-3 text-center">
    <h2 class="h1 text-white mb-3"><?php _e('cta_banner_title'); ?></h2>
    <p class="mb-4 mx-auto" style="color: var(--dental-gold-soft); max-width: 580px;">
      <?php _e('cta_banner_sub'); ?>
    </p>
    <a href="<?php echo e(DENTAL_BASE_URL); ?>/booking.php" class="btn btn-luxury-gold px-4 py-3">
      <i class="bi bi-calendar-check me-1"></i> <?php _e('hero_cta_book'); ?>
    </a>
  </div>
</section>

<?php require_once __DIR__ . '/templates/public/footer.php'; ?>
