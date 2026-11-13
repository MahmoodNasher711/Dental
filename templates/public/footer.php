<?php
/**
 * DENTAL — Public Website Luxury Footer Template
 */
global $CURRENT_LANG;
$settings = get_clinic_settings();
$addrKey  = ($CURRENT_LANG === 'ar') ? 'clinic_address_ar' : 'clinic_address_en';
$hoursKey = ($CURRENT_LANG === 'ar') ? 'operating_hours_ar' : 'operating_hours_en';
?>
<footer class="luxury-footer mt-auto">
  <div class="container">
    <div class="row g-4 pb-4 border-bottom" style="border-color: rgba(198, 164, 106, 0.22) !important;">
      <div class="col-lg-4">
        <div class="d-flex align-items-center gap-2 mb-3">
          <img src="<?php echo e(DENTAL_BASE_URL); ?>/assets/images/brand/logo-emblem.svg" alt="DENTAL" width="32" height="32">
          <span class="brand-wordmark text-white" style="font-size:1.35rem;">DENTAL</span>
        </div>
        <p class="small mb-3" style="color: rgba(250, 248, 243, 0.78); max-width: 340px;">
          <?php _e('footer_desc'); ?>
        </p>
        <div class="small" style="color: var(--dental-gold-soft);">
          <?php _e('brand_tagline'); ?>
        </div>
      </div>

      <div class="col-sm-6 col-lg-4">
        <h5><?php _e('footer_quick_links'); ?></h5>
        <ul class="list-unstyled d-flex flex-column gap-2 small">
          <li><a href="<?php echo e(DENTAL_BASE_URL); ?>/index.php"><?php _e('nav_home'); ?></a></li>
          <li><a href="<?php echo e(DENTAL_BASE_URL); ?>/about.php"><?php _e('nav_about'); ?></a></li>
          <li><a href="<?php echo e(DENTAL_BASE_URL); ?>/services.php"><?php _e('nav_services'); ?></a></li>
          <li><a href="<?php echo e(DENTAL_BASE_URL); ?>/doctors.php"><?php _e('nav_doctors'); ?></a></li>
          <li><a href="<?php echo e(DENTAL_BASE_URL); ?>/booking.php"><?php _e('nav_book'); ?></a></li>
          <li><a href="<?php echo e(DENTAL_BASE_URL); ?>/contact.php"><?php _e('nav_contact'); ?></a></li>
        </ul>
      </div>

      <div class="col-sm-6 col-lg-4">
        <h5><?php _e('footer_clinic_hours'); ?></h5>
        <ul class="list-unstyled d-flex flex-column gap-2 small">
          <li>
            <i class="bi bi-telephone text-gold me-2"></i>
            <?php if (!empty($settings['contact_phone'])): ?>
              <span><?php echo e($settings['contact_phone']); ?></span>
            <?php else: ?>
              <span class="opacity-75">[<?php _e('contact_phone_label'); ?>: <?php _e('owner_info_pending'); ?>]</span>
            <?php endif; ?>
          </li>
          <li>
            <i class="bi bi-envelope text-gold me-2"></i>
            <?php if (!empty($settings['contact_email'])): ?>
              <span><?php echo e($settings['contact_email']); ?></span>
            <?php else: ?>
              <span class="opacity-75">[<?php _e('contact_email_label'); ?>: <?php _e('owner_info_pending'); ?>]</span>
            <?php endif; ?>
          </li>
          <li>
            <i class="bi bi-geo-alt text-gold me-2"></i>
            <?php if (!empty($settings[$addrKey])): ?>
              <span><?php echo e($settings[$addrKey]); ?></span>
            <?php else: ?>
              <span class="opacity-75">[<?php _e('contact_address_label'); ?>: <?php _e('owner_info_pending'); ?>]</span>
            <?php endif; ?>
          </li>
          <li>
            <i class="bi bi-clock text-gold me-2"></i>
            <?php if (!empty($settings[$hoursKey])): ?>
              <span><?php echo e($settings[$hoursKey]); ?></span>
            <?php else: ?>
              <span class="opacity-75">[<?php _e('contact_hours_label'); ?>: <?php _e('owner_info_pending'); ?>]</span>
            <?php endif; ?>
          </li>
        </ul>
      </div>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center pt-4 gap-2 small" style="color: rgba(250, 248, 243, 0.65);">
      <div><?php _e('footer_rights'); ?></div>
      <div class="d-flex gap-3">
        <a href="<?php echo e(lang_switch_url('en')); ?>">English</a>
        <span>|</span>
        <a href="<?php echo e(lang_switch_url('ar')); ?>">العربية</a>
      </div>
    </div>
  </div>
</footer>

<script src="<?php echo e(DENTAL_BASE_URL); ?>/assets/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo e(DENTAL_BASE_URL); ?>/assets/js/luxury.js"></script>
</body>
</html>
