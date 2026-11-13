<?php
/**
 * DENTAL — Public Website Luxury Header Template (Role-Aware Top Navbar)
 */
global $CURRENT_LANG, $CURRENT_DIR;
$pageTitle = $pageTitle ?? __('brand_name');
$activeNav = $activeNav ?? '';
$settings  = get_clinic_settings();
$user      = auth_user();
$flashes   = flash_get();
$altLang   = ($CURRENT_LANG === 'ar') ? 'en' : 'ar';
$bsCss     = ($CURRENT_DIR === 'rtl') ? 'bootstrap.rtl.min.css' : 'bootstrap.min.css';
?>
<!DOCTYPE html>
<html lang="<?php echo e($CURRENT_LANG); ?>" dir="<?php echo e($CURRENT_DIR); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($pageTitle); ?> — <?php _e('brand_name'); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo e(DENTAL_BASE_URL); ?>/assets/css/<?php echo e($bsCss); ?>">
  <link rel="stylesheet" href="<?php echo e(DENTAL_BASE_URL); ?>/assets/css/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?php echo e(DENTAL_BASE_URL); ?>/assets/css/variables.css">
  <link rel="stylesheet" href="<?php echo e(DENTAL_BASE_URL); ?>/assets/css/luxury.css">
</head>
<body class="d-flex flex-column min-vh-100">

<?php require __DIR__ . '/../partials/navbar.php'; ?>

<?php if (!empty($flashes)): ?>
  <div class="container mt-3">
    <?php foreach ($flashes as $f): ?>
      <div class="alert alert-<?php echo e($f['type']); ?> alert-dismissible fade show shadow-sm mb-3" role="alert">
        <?php echo e($f['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?php _e('aria_close'); ?>"></button>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
