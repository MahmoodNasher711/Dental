<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = __('about_page_title');
$activeNav = 'about';

$aboutRow = db()->query("SELECT about_text, about_text_ar FROM about_us ORDER BY about_id DESC LIMIT 1")->fetch();
global $CURRENT_LANG;
$aboutText = '';
if ($aboutRow) {
    $aboutText = ($CURRENT_LANG === 'ar' && !empty($aboutRow['about_text_ar']))
        ? $aboutRow['about_text_ar']
        : $aboutRow['about_text'];
}

require_once __DIR__ . '/templates/public/header.php';
?>

<section class="py-5 bg-emerald-deep">
  <div class="container py-3">
    <span class="eyebrow-label" style="color: var(--dental-gold-soft);"><?php _e('about_eyebrow'); ?></span>
    <h1 class="display-5 text-white mb-0"><?php _e('about_page_title'); ?></h1>
  </div>
</section>

<section class="section-pad bg-ivory">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="hero-image-frame">
          <img src="<?php echo e(DENTAL_BASE_URL); ?>/assets/images/clinic/about-lounge.jpg" alt="<?php _e('about_page_title'); ?>">
        </div>
      </div>
      <div class="col-lg-6">
        <h2 class="h1 text-emerald mb-3"><?php _e('about_mission_title'); ?></h2>
        <div class="gold-divider"></div>
        <?php if ($aboutText !== ''): ?>
          <div class="text-muted-luxury" style="line-height: 1.9;">
            <?php echo nl2br(e($aboutText)); ?>
          </div>
        <?php else: ?>
          <div class="owner-placeholder-badge">[<?php _e('owner_info_pending'); ?>]</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="row g-4 mt-5 pt-4">
      <div class="col-md-4">
        <div class="luxury-card p-4 h-100">
          <div class="kpi-icon-box mb-3"><i class="bi bi-heart-pulse"></i></div>
          <h3 class="h4 text-emerald"><?php _e('about_values_1_title'); ?></h3>
          <p class="text-muted-luxury small mb-0"><?php _e('about_values_1_desc'); ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="luxury-card p-4 h-100">
          <div class="kpi-icon-box mb-3"><i class="bi bi-shield-check"></i></div>
          <h3 class="h4 text-emerald"><?php _e('about_values_2_title'); ?></h3>
          <p class="text-muted-luxury small mb-0"><?php _e('about_values_2_desc'); ?></p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="luxury-card p-4 h-100">
          <div class="kpi-icon-box mb-3"><i class="bi bi-calendar2-check"></i></div>
          <h3 class="h4 text-emerald"><?php _e('about_values_3_title'); ?></h3>
          <p class="text-muted-luxury small mb-0"><?php _e('about_values_3_desc'); ?></p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/templates/public/footer.php'; ?>
