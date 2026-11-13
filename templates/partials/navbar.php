<?php
/**
 * DENTAL — Role-Based Responsive Top Navigation Bar (Guest / Patient / Admin Public View)
 * Strictly excludes any vertical sidebar; sidebar is reserved exclusively for admin/*.
 */
global $CURRENT_LANG, $CURRENT_DIR;
$settings = $settings ?? get_clinic_settings();
$user     = $user ?? auth_user();
$activeNav = $activeNav ?? '';
$altLang   = ($CURRENT_LANG === 'ar') ? 'en' : 'ar';
$isPatient = ($user !== null && ($user['role'] ?? '') !== 'admin');
$isAdmin   = ($user !== null && ($user['role'] ?? '') === 'admin');
?>

<div class="luxury-top-strip d-none d-md-block">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-4">
      <span><i class="bi bi-gem me-1"></i> <?php _e('brand_tagline'); ?></span>
      <?php if (!empty($settings['contact_phone'])): ?>
        <span><i class="bi bi-telephone me-1"></i> <?php echo e($settings['contact_phone']); ?></span>
      <?php endif; ?>
      <?php
        $hoursStr = clinic_hours($settings);
        if ($hoursStr !== ''):
      ?>
        <span><i class="bi bi-clock me-1"></i> <?php echo e($hoursStr); ?></span>
      <?php endif; ?>
    </div>
    <div>
      <a href="<?php echo e(lang_switch_url($altLang)); ?>" class="text-decoration-none" style="color: var(--dental-gold-soft);">
        <i class="bi bi-translate me-1"></i> <?php _e('switch_to_lang'); ?>
      </a>
    </div>
  </div>
</div>

<nav class="navbar navbar-expand-xl luxury-navbar sticky-top">
  <div class="container">
    <a class="brand-wordmark" href="<?php echo e(DENTAL_BASE_URL); ?>/index.php">
      <img src="<?php echo e(DENTAL_BASE_URL); ?>/assets/images/brand/logo-emblem.svg" alt="DENTAL" width="34" height="34">
      <span>DENTAL</span>
    </a>

    <div class="d-flex align-items-center gap-2 d-xl-none">
      <a href="<?php echo e(lang_switch_url($altLang)); ?>" class="lang-switcher-btn">
        <i class="bi bi-translate"></i> <?php _e('switch_to_lang'); ?>
      </a>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainRoleNavbar" aria-controls="mainRoleNavbar" aria-expanded="false" aria-label="<?php _e('toggle_navigation'); ?>">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="mainRoleNavbar">
      <?php if ($isPatient): ?>
        <!-- Authenticated Patient Horizontal Top Navigation -->
        <ul class="navbar-nav mx-auto mb-2 mb-xl-0 gap-xl-1">
          <li class="nav-item">
            <a class="nav-link luxury-nav-link <?php echo $activeNav === 'home' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/index.php"><?php _e('nav_home'); ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link luxury-nav-link <?php echo $activeNav === 'about' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/about.php"><?php _e('nav_about'); ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link luxury-nav-link <?php echo $activeNav === 'services' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/services.php"><?php _e('nav_services'); ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link luxury-nav-link <?php echo $activeNav === 'doctors' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/doctors.php"><?php _e('nav_doctors'); ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link luxury-nav-link <?php echo $activeNav === 'booking' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/booking.php"><?php _e('nav_book'); ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link luxury-nav-link <?php echo $activeNav === 'appointments' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/patient/appointments.php"><?php _e('portal_appointments'); ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link luxury-nav-link <?php echo $activeNav === 'profile' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/patient/profile.php"><?php _e('portal_profile'); ?></a>
          </li>
        </ul>

        <div class="d-flex flex-column flex-xl-row align-items-xl-center gap-2 pt-2 pt-xl-0">
          <a href="<?php echo e(lang_switch_url($altLang)); ?>" class="lang-switcher-btn d-none d-xl-inline-flex text-nowrap">
            <i class="bi bi-translate"></i> <?php _e('switch_to_lang'); ?>
          </a>

          <div class="dropdown">
            <button class="btn btn-luxury-outline dropdown-toggle py-2 px-3 d-inline-flex align-items-center gap-2 text-nowrap w-100 justify-content-center" type="button" id="patientAccountMenu" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="<?php echo e(get_user_avatar_url($user['image'])); ?>" alt="" class="avatar-circle" width="24" height="24">
              <span class="text-truncate" style="max-width: 120px;"><?php echo e($user['username']); ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="border-color: var(--dental-border-subtle) !important;" aria-labelledby="patientAccountMenu">
              <li>
                <a class="dropdown-item py-2 <?php echo $activeNav === 'dashboard' ? 'active bg-emerald-deep text-white' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/patient/index.php">
                  <i class="bi bi-grid me-2"></i><?php _e('portal_dashboard'); ?>
                </a>
              </li>
              <li>
                <a class="dropdown-item py-2 <?php echo $activeNav === 'appointments' ? 'active bg-emerald-deep text-white' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/patient/appointments.php">
                  <i class="bi bi-calendar2-week me-2"></i><?php _e('portal_appointments'); ?>
                </a>
              </li>
              <li>
                <a class="dropdown-item py-2 <?php echo $activeNav === 'profile' ? 'active bg-emerald-deep text-white' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/patient/profile.php">
                  <i class="bi bi-person me-2"></i><?php _e('portal_profile'); ?>
                </a>
              </li>
              <li>
                <a class="dropdown-item py-2 <?php echo $activeNav === 'security' ? 'active bg-emerald-deep text-white' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/patient/security.php">
                  <i class="bi bi-shield-lock me-2"></i><?php _e('portal_security'); ?>
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="post" action="<?php echo e(DENTAL_BASE_URL); ?>/logout.php" class="px-2">
                  <?php echo csrf_field(); ?>
                  <button type="submit" class="dropdown-item text-danger rounded py-2">
                    <i class="bi bi-box-arrow-right me-2"></i><?php _e('nav_logout'); ?>
                  </button>
                </form>
              </li>
            </ul>
          </div>

          <form method="post" action="<?php echo e(DENTAL_BASE_URL); ?>/logout.php" class="d-grid d-xl-inline-block">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-luxury-primary py-2 px-3 text-nowrap">
              <i class="bi bi-box-arrow-right"></i> <?php _e('nav_logout'); ?>
            </button>
          </form>
        </div>

      <?php elseif ($isAdmin): ?>
        <!-- Authenticated Admin on Public Pages -->
        <ul class="navbar-nav mx-auto mb-2 mb-xl-0 gap-xl-1">
          <li class="nav-item"><a class="nav-link luxury-nav-link <?php echo $activeNav === 'home' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/index.php"><?php _e('nav_home'); ?></a></li>
          <li class="nav-item"><a class="nav-link luxury-nav-link <?php echo $activeNav === 'about' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/about.php"><?php _e('nav_about'); ?></a></li>
          <li class="nav-item"><a class="nav-link luxury-nav-link <?php echo $activeNav === 'services' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/services.php"><?php _e('nav_services'); ?></a></li>
          <li class="nav-item"><a class="nav-link luxury-nav-link <?php echo $activeNav === 'doctors' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/doctors.php"><?php _e('nav_doctors'); ?></a></li>
          <li class="nav-item"><a class="nav-link luxury-nav-link <?php echo $activeNav === 'contact' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/contact.php"><?php _e('nav_contact'); ?></a></li>
        </ul>

        <div class="d-flex flex-column flex-xl-row align-items-xl-center gap-2 pt-2 pt-xl-0">
          <a href="<?php echo e(lang_switch_url($altLang)); ?>" class="lang-switcher-btn d-none d-xl-inline-flex text-nowrap">
            <i class="bi bi-translate"></i> <?php _e('switch_to_lang'); ?>
          </a>
          <a href="<?php echo e(DENTAL_BASE_URL); ?>/admin/index.php" class="btn btn-luxury-primary py-2 px-3 text-nowrap">
            <i class="bi bi-shield-check"></i> <?php _e('nav_admin_portal'); ?>
          </a>
          <form method="post" action="<?php echo e(DENTAL_BASE_URL); ?>/logout.php" class="d-grid d-xl-inline-block">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-luxury-outline py-2 px-3 text-nowrap">
              <i class="bi bi-box-arrow-right"></i> <?php _e('nav_logout'); ?>
            </button>
          </form>
        </div>

      <?php else: ?>
        <!-- Public Guest Top Navigation -->
        <ul class="navbar-nav mx-auto mb-2 mb-xl-0 gap-xl-1">
          <li class="nav-item"><a class="nav-link luxury-nav-link <?php echo $activeNav === 'home' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/index.php"><?php _e('nav_home'); ?></a></li>
          <li class="nav-item"><a class="nav-link luxury-nav-link <?php echo $activeNav === 'about' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/about.php"><?php _e('nav_about'); ?></a></li>
          <li class="nav-item"><a class="nav-link luxury-nav-link <?php echo $activeNav === 'services' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/services.php"><?php _e('nav_services'); ?></a></li>
          <li class="nav-item"><a class="nav-link luxury-nav-link <?php echo $activeNav === 'doctors' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/doctors.php"><?php _e('nav_doctors'); ?></a></li>
          <li class="nav-item"><a class="nav-link luxury-nav-link <?php echo $activeNav === 'contact' ? 'active' : ''; ?>" href="<?php echo e(DENTAL_BASE_URL); ?>/contact.php"><?php _e('nav_contact'); ?></a></li>
        </ul>

        <div class="d-flex flex-column flex-xl-row align-items-xl-center gap-2 pt-2 pt-xl-0">
          <a href="<?php echo e(lang_switch_url($altLang)); ?>" class="lang-switcher-btn d-none d-xl-inline-flex text-nowrap">
            <i class="bi bi-translate"></i> <?php _e('switch_to_lang'); ?>
          </a>
          <a href="<?php echo e(DENTAL_BASE_URL); ?>/login.php" class="btn btn-luxury-outline py-2 px-3 text-nowrap">
            <i class="bi bi-box-arrow-in-right"></i> <?php _e('nav_login'); ?>
          </a>
          <a href="<?php echo e(DENTAL_BASE_URL); ?>/register.php" class="btn btn-luxury-primary py-2 px-3 text-nowrap">
            <i class="bi bi-person-plus"></i> <?php _e('nav_register'); ?>
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>
