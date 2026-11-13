<?php
/**
 * DENTAL — Executive Administration Platform Layout Start
 */
global $CURRENT_LANG, $CURRENT_DIR;
$adminUser = $adminUser ?? auth_require_admin();
$pageTitle = $pageTitle ?? __('admin_dashboard');
$activeNav = $activeNav ?? 'dashboard';
$flashes   = flash_get();
$altLang   = ($CURRENT_LANG === 'ar') ? 'en' : 'ar';
$bsCss     = ($CURRENT_DIR === 'rtl') ? 'bootstrap.rtl.min.css' : 'bootstrap.min.css';
?>
<!DOCTYPE html>
<html lang="<?php echo e($CURRENT_LANG); ?>" dir="<?php echo e($CURRENT_DIR); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($pageTitle); ?> — <?php _e('nav_admin_portal'); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo e(DENTAL_BASE_URL); ?>/assets/css/<?php echo e($bsCss); ?>">
  <link rel="stylesheet" href="<?php echo e(DENTAL_BASE_URL); ?>/assets/css/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?php echo e(DENTAL_BASE_URL); ?>/assets/css/variables.css">
  <link rel="stylesheet" href="<?php echo e(DENTAL_BASE_URL); ?>/assets/css/luxury.css">
</head>
<body>
<div class="portal-shell">
  <aside id="portalSidebar" class="portal-sidebar">
    <div class="sidebar-brand d-flex align-items-center justify-content-between">
      <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/index.php" class="brand-wordmark text-white" style="font-size:1.25rem;">
        <img src="<?php echo e(DENTAL_BASE_URL); ?>/assets/images/brand/logo-emblem.svg" alt="DENTAL" width="28" height="28">
        <span>DENTAL</span>
      </a>
      <button type="button" id="portalSidebarClose" class="btn btn-sm text-white d-lg-none border-0">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <div class="p-3 border-bottom" style="border-color: rgba(198,164,106,0.18) !important;">
      <div class="d-flex align-items-center gap-3">
        <img src="<?php echo e(get_user_avatar_url($adminUser['image'])); ?>" alt="Admin" class="avatar-circle" width="44" height="44">
        <div class="min-w-0">
          <div class="fw-semibold text-truncate small"><?php echo e($adminUser['username']); ?></div>
          <div class="text-truncate" style="font-size: 0.72rem; color: var(--dental-gold-soft); text-transform:uppercase;">
            <i class="bi bi-shield-fill-check me-1"></i><?php echo e(translate_role($adminUser['role'])); ?>
          </div>
        </div>
      </div>
    </div>

    <nav class="py-3 flex-grow-1">
      <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/index.php" class="nav-link-portal <?php echo $activeNav === 'dashboard' ? 'active' : ''; ?>">
        <i class="bi bi-speedometer2"></i> <span><?php _e('admin_dashboard'); ?></span>
      </a>
      <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/appointments.php" class="nav-link-portal <?php echo $activeNav === 'appointments' ? 'active' : ''; ?>">
        <i class="bi bi-calendar2-check"></i> <span><?php _e('admin_appointments'); ?></span>
      </a>
      <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/patients.php" class="nav-link-portal <?php echo $activeNav === 'patients' ? 'active' : ''; ?>">
        <i class="bi bi-people"></i> <span><?php _e('admin_patients'); ?></span>
      </a>
      <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/dentists.php" class="nav-link-portal <?php echo $activeNav === 'dentists' ? 'active' : ''; ?>">
        <i class="bi bi-person-badge"></i> <span><?php _e('admin_dentists'); ?></span>
      </a>
      <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/services.php" class="nav-link-portal <?php echo $activeNav === 'services' ? 'active' : ''; ?>">
        <i class="bi bi-heart-pulse"></i> <span><?php _e('admin_services'); ?></span>
      </a>
      <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/messages.php" class="nav-link-portal <?php echo $activeNav === 'messages' ? 'active' : ''; ?>">
        <i class="bi bi-envelope-paper"></i> <span><?php _e('admin_messages'); ?></span>
      </a>
      <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/users.php" class="nav-link-portal <?php echo $activeNav === 'users' ? 'active' : ''; ?>">
        <i class="bi bi-person-gear"></i> <span><?php _e('admin_users'); ?></span>
      </a>
      <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/settings.php" class="nav-link-portal <?php echo $activeNav === 'settings' ? 'active' : ''; ?>">
        <i class="bi bi-sliders"></i> <span><?php _e('admin_settings'); ?></span>
      </a>
      <hr class="mx-3 my-2" style="border-color: rgba(198,164,106,0.2);">
      <a href="<?php echo e(DENTAL_BASE_URL); ?>/index.php" class="nav-link-portal">
        <i class="bi bi-globe"></i> <span><?php _e('nav_return_site'); ?></span>
      </a>
    </nav>

    <div class="p-3 border-top" style="border-color: rgba(198,164,106,0.18) !important;">
      <form method="post" action="<?php echo e(DENTAL_BASE_URL); ?>/logout.php">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn btn-luxury-outline-light w-100 py-2 small">
          <i class="bi bi-box-arrow-right"></i> <?php _e('nav_logout'); ?>
        </button>
      </form>
    </div>
  </aside>

  <div class="portal-main">
    <header class="portal-topbar">
      <div class="d-flex align-items-center gap-3">
        <button type="button" id="portalSidebarToggle" class="btn btn-outline-secondary btn-sm d-lg-none">
          <i class="bi bi-list fs-5"></i>
        </button>
        <h1 class="h5 mb-0 text-emerald fw-bold"><?php echo e($pageTitle); ?></h1>
      </div>
      <div class="d-flex align-items-center gap-3">
        <span class="small text-muted-luxury d-none d-sm-inline"><?php echo e(format_localized_date(date('Y-m-d'))); ?></span>
        <a href="<?php echo e(lang_switch_url($altLang)); ?>" class="lang-switcher-btn">
          <i class="bi bi-translate"></i> <?php _e('switch_to_lang'); ?>
        </a>
      </div>
    </header>

    <main class="portal-content">
      <?php foreach ($flashes as $f): ?>
        <div class="alert alert-<?php echo e($f['type']); ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
          <?php echo e($f['message']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?php _e('aria_close'); ?>"></button>
        </div>
      <?php endforeach; ?>
