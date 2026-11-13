<?php
/**
 * DENTAL — Patient Portal Layout Start (Full-Width Horizontal Top Navbar, No Sidebar)
 */
global $CURRENT_LANG, $CURRENT_DIR;
$portalUser = $portalUser ?? auth_require_patient();
$user       = $portalUser;
$pageTitle  = $pageTitle ?? __('nav_patient_portal');
$activeNav  = $activeNav ?? 'dashboard';
$settings   = get_clinic_settings();
$flashes    = flash_get();
$altLang    = ($CURRENT_LANG === 'ar') ? 'en' : 'ar';
$bsCss      = ($CURRENT_DIR === 'rtl') ? 'bootstrap.rtl.min.css' : 'bootstrap.min.css';
?>
<!DOCTYPE html>
<html lang="<?php echo e($CURRENT_LANG); ?>" dir="<?php echo e($CURRENT_DIR); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($pageTitle); ?> — <?php _e('nav_patient_portal'); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo e(DENTAL_BASE_URL); ?>/assets/css/<?php echo e($bsCss); ?>">
  <link rel="stylesheet" href="<?php echo e(DENTAL_BASE_URL); ?>/assets/css/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?php echo e(DENTAL_BASE_URL); ?>/assets/css/variables.css">
  <link rel="stylesheet" href="<?php echo e(DENTAL_BASE_URL); ?>/assets/css/luxury.css">
</head>
<body class="d-flex flex-column min-vh-100">

<?php require __DIR__ . '/../partials/navbar.php'; ?>

<section class="py-4 bg-emerald-deep border-bottom" style="border-color: rgba(198,164,106,0.28) !important;">
  <div class="container py-2 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
    <div>
      <span class="eyebrow-label" style="color: var(--dental-gold-soft);"><?php _e('patient_portal_badge'); ?></span>
      <h1 class="h2 text-white mb-0"><?php echo e($pageTitle); ?></h1>
    </div>
    <div class="d-flex align-items-center gap-2 small" style="color: var(--dental-gold-soft);">
      <i class="bi bi-calendar3"></i>
      <span><?php echo e(format_localized_date(date('Y-m-d'))); ?></span>
    </div>
  </div>
</section>

<main class="flex-grow-1 bg-ivory py-4 py-lg-5">
  <div class="container">
    <?php foreach ($flashes as $f): ?>
      <div class="alert alert-<?php echo e($f['type']); ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
        <?php echo e($f['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?php _e('aria_close'); ?>"></button>
      </div>
    <?php endforeach; ?>
