<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = __('contact_page_title');
$activeNav = 'contact';
$settings  = get_clinic_settings();
global $CURRENT_LANG;
$addrKey   = ($CURRENT_LANG === 'ar') ? 'clinic_address_ar' : 'clinic_address_en';
$hoursKey  = ($CURRENT_LANG === 'ar') ? 'operating_hours_ar' : 'operating_hours_en';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name    = trim($_POST['name_sender'] ?? '');
    $email   = trim($_POST['email_sender'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $errors[] = __('err_required_fields');
    } elseif (!validate_name($name)) {
        $errors[] = __('err_invalid_name');
    } elseif (!validate_email($email)) {
        $errors[] = __('err_invalid_email');
    } elseif (mb_strlen($message) > 5000) {
        $errors[] = __('err_required_fields');
    } else {
        $stmt = db()->prepare("INSERT INTO contact_us (cont_user_name, cont_user_email, cont_user_text, is_read, created_at) VALUES (:name, :email, :msg, 0, NOW())");
        $stmt->execute([':name' => $name, ':email' => $email, ':msg' => $message]);
        $success = true;
    }
}

require_once __DIR__ . '/templates/public/header.php';
?>

<section class="py-5 bg-emerald-deep">
  <div class="container py-3">
    <span class="eyebrow-label" style="color: var(--dental-gold-soft);"><?php _e('contact_eyebrow'); ?></span>
    <h1 class="display-5 text-white mb-0"><?php _e('contact_title'); ?></h1>
  </div>
</section>

<section class="section-pad bg-ivory">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-5">
        <div class="luxury-card p-4 p-md-5 h-100">
          <h2 class="h3 text-emerald mb-3"><?php _e('contact_info_title'); ?></h2>
          <div class="gold-divider"></div>
          <p class="text-muted-luxury small mb-4"><?php _e('contact_subtitle'); ?></p>

          <div class="d-flex flex-column gap-4">
            <div class="d-flex gap-3 align-items-start">
              <div class="kpi-icon-box flex-shrink-0"><i class="bi bi-telephone"></i></div>
              <div>
                <div class="small text-muted-luxury"><?php _e('contact_phone_label'); ?></div>
                <?php if (!empty($settings['contact_phone'])): ?>
                  <div class="fw-semibold text-emerald"><?php echo e($settings['contact_phone']); ?></div>
                <?php else: ?>
                  <span class="owner-placeholder-badge">[<?php _e('owner_info_pending'); ?>]</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="d-flex gap-3 align-items-start">
              <div class="kpi-icon-box flex-shrink-0"><i class="bi bi-envelope"></i></div>
              <div>
                <div class="small text-muted-luxury"><?php _e('contact_email_label'); ?></div>
                <?php if (!empty($settings['contact_email'])): ?>
                  <div class="fw-semibold text-emerald"><?php echo e($settings['contact_email']); ?></div>
                <?php else: ?>
                  <span class="owner-placeholder-badge">[<?php _e('owner_info_pending'); ?>]</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="d-flex gap-3 align-items-start">
              <div class="kpi-icon-box flex-shrink-0"><i class="bi bi-geo-alt"></i></div>
              <div>
                <div class="small text-muted-luxury"><?php _e('contact_address_label'); ?></div>
                <?php if (!empty($settings[$addrKey])): ?>
                  <div class="fw-semibold text-emerald"><?php echo e($settings[$addrKey]); ?></div>
                <?php else: ?>
                  <span class="owner-placeholder-badge">[<?php _e('owner_info_pending'); ?>]</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="d-flex gap-3 align-items-start">
              <div class="kpi-icon-box flex-shrink-0"><i class="bi bi-clock"></i></div>
              <div>
                <div class="small text-muted-luxury"><?php _e('contact_hours_label'); ?></div>
                <?php if (!empty($settings[$hoursKey])): ?>
                  <div class="fw-semibold text-emerald"><?php echo e($settings[$hoursKey]); ?></div>
                <?php else: ?>
                  <span class="owner-placeholder-badge">[<?php _e('owner_info_pending'); ?>]</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="luxury-card p-4 p-md-5">
          <?php if ($success): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4">
              <i class="bi bi-check-circle-fill me-2"></i> <?php _e('contact_success'); ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4">
              <?php foreach ($errors as $err): ?>
                <div><?php echo e($err); ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form method="post" action="" data-prevent-double-submit="true">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
              <label for="contactName" class="luxury-form-label"><?php _e('contact_form_name'); ?></label>
              <input type="text" id="contactName" name="name_sender" class="form-control luxury-input" maxlength="100" value="<?php echo $success ? '' : e($_POST['name_sender'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
              <label for="contactEmail" class="luxury-form-label"><?php _e('contact_form_email'); ?></label>
              <input type="email" id="contactEmail" name="email_sender" class="form-control luxury-input" maxlength="255" value="<?php echo $success ? '' : e($_POST['email_sender'] ?? ''); ?>" required>
            </div>
            <div class="mb-4">
              <label for="contactMsg" class="luxury-form-label"><?php _e('contact_form_message'); ?></label>
              <textarea id="contactMsg" name="message" rows="5" class="form-control luxury-input" maxlength="5000" required><?php echo $success ? '' : e($_POST['message'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-luxury-primary">
              <i class="bi bi-send me-1"></i> <?php _e('contact_form_submit'); ?>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/templates/public/footer.php'; ?>
