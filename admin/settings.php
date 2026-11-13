<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$adminUser = auth_require_admin();
$pageTitle = __('admin_settings');
$activeNav = 'settings';
$pdo       = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (isset($_POST['save_settings'])) {
        $keys = [
            'contact_phone', 'contact_email',
            'clinic_address_en', 'clinic_address_ar',
            'operating_hours_en', 'operating_hours_ar',
            'work_hours_start', 'work_hours_end', 'slot_duration_minutes'
        ];
        $stmtUpd = $pdo->prepare(
            "INSERT INTO clinic_settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        foreach ($keys as $k) {
            $val = trim((string)($_POST[$k] ?? ''));
            $stmtUpd->execute([':k' => $k, ':v' => $val]);
        }

        if (isset($_POST['about_text'])) {
            $aboutEn = trim($_POST['about_text']);
            $aboutAr = trim($_POST['about_text_ar'] ?? '');
            $stmtAbout = $pdo->prepare("UPDATE about_us SET about_text = :en, about_text_ar = :ar ORDER BY about_id DESC LIMIT 1");
            $stmtAbout->execute([':en' => $aboutEn, ':ar' => $aboutAr]);
        }

        audit_log('ADMIN_UPDATE_SETTINGS', 'Updated clinic settings & about narrative', (int)$adminUser['id']);
        flash_set('success', lang_raw('msg_profile_updated'));
    } elseif (isset($_POST['change_admin_pass'])) {
        $oldP = $_POST['old_password'] ?? '';
        $newP = $_POST['new_password'] ?? '';
        $cnfP = $_POST['confirm_password'] ?? '';

        $stmtCheck = $pdo->prepare("SELECT password FROM users WHERE id = :id");
        $stmtCheck->execute([':id' => (int)$adminUser['id']]);
        $row = $stmtCheck->fetch();

        if (!$row || !password_verify($oldP, $row['password'])) {
            flash_set('danger', lang_raw('err_old_password'));
        } elseif (!validate_password($newP)) {
            flash_set('danger', lang_raw('err_password_short'));
        } elseif ($newP !== $cnfP) {
            flash_set('danger', lang_raw('err_password_mismatch'));
        } else {
            $stmtPass = $pdo->prepare("UPDATE users SET password = :p WHERE id = :id");
            $stmtPass->execute([':p' => password_hash($newP, PASSWORD_DEFAULT), ':id' => (int)$adminUser['id']]);
            audit_log('ADMIN_PASSWORD_CHANGED', 'Administrator changed own password', (int)$adminUser['id']);
            flash_set('success', lang_raw('msg_password_updated'));
        }
    }

    header('Location: ' . DENTAL_BASE_URL . '/admin/settings.php');
    exit;
}

$settings = get_clinic_settings();
$aboutRow = $pdo->query("SELECT about_text, about_text_ar FROM about_us ORDER BY about_id DESC LIMIT 1")->fetch() ?: ['about_text' => '', 'about_text_ar' => ''];

require_once __DIR__ . '/../templates/admin/layout_start.php';
?>

<div class="row g-4">
  <div class="col-lg-8">
    <form method="post" action="" class="luxury-card p-4 p-md-5">
      <?php echo csrf_field(); ?>
      <h2 class="h4 text-emerald mb-4"><?php _e('admin_settings'); ?></h2>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="luxury-form-label"><?php _e('contact_phone_label'); ?></label>
          <input type="text" name="contact_phone" class="form-control luxury-input" placeholder="<?php _e('settings_verified_phone'); ?>" value="<?php echo e($settings['contact_phone'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="luxury-form-label"><?php _e('contact_email_label'); ?></label>
          <input type="email" name="contact_email" class="form-control luxury-input" placeholder="<?php _e('settings_verified_email'); ?>" value="<?php echo e($settings['contact_email'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="luxury-form-label"><?php _e('settings_clinic_address_en'); ?></label>
          <input type="text" name="clinic_address_en" class="form-control luxury-input" dir="ltr" value="<?php echo e($settings['clinic_address_en'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="luxury-form-label"><?php _e('settings_clinic_address_ar'); ?></label>
          <input type="text" name="clinic_address_ar" class="form-control luxury-input" dir="rtl" value="<?php echo e($settings['clinic_address_ar'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="luxury-form-label"><?php _e('settings_hours_en'); ?></label>
          <input type="text" name="operating_hours_en" class="form-control luxury-input" dir="ltr" value="<?php echo e($settings['operating_hours_en'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="luxury-form-label"><?php _e('settings_hours_ar'); ?></label>
          <input type="text" name="operating_hours_ar" class="form-control luxury-input" dir="rtl" value="<?php echo e($settings['operating_hours_ar'] ?? ''); ?>">
        </div>
        <div class="col-md-4">
          <label class="luxury-form-label"><?php _e('settings_window_start'); ?></label>
          <input type="time" name="work_hours_start" class="form-control luxury-input" value="<?php echo e($settings['work_hours_start'] ?? '09:00'); ?>">
        </div>
        <div class="col-md-4">
          <label class="luxury-form-label"><?php _e('settings_window_end'); ?></label>
          <input type="time" name="work_hours_end" class="form-control luxury-input" value="<?php echo e($settings['work_hours_end'] ?? '17:00'); ?>">
        </div>
        <div class="col-md-4">
          <label class="luxury-form-label"><?php _e('settings_slot_interval'); ?></label>
          <select name="slot_duration_minutes" class="form-select luxury-select">
            <?php $iv = (int)($settings['slot_duration_minutes'] ?? 30); ?>
            <option value="15" <?php echo ($iv === 15) ? 'selected' : ''; ?>>15 <?php _e('minutes'); ?></option>
            <option value="30" <?php echo ($iv === 30) ? 'selected' : ''; ?>>30 <?php _e('minutes'); ?></option>
            <option value="45" <?php echo ($iv === 45) ? 'selected' : ''; ?>>45 <?php _e('minutes'); ?></option>
            <option value="60" <?php echo ($iv === 60) ? 'selected' : ''; ?>>60 <?php _e('minutes'); ?></option>
          </select>
        </div>
        <div class="col-12">
          <label class="luxury-form-label"><?php _e('settings_about_en'); ?></label>
          <textarea name="about_text" rows="4" class="form-control luxury-input" dir="ltr"><?php echo e($aboutRow['about_text']); ?></textarea>
        </div>
        <div class="col-12">
          <label class="luxury-form-label"><?php _e('settings_about_ar'); ?></label>
          <textarea name="about_text_ar" rows="4" class="form-control luxury-input" dir="rtl"><?php echo e($aboutRow['about_text_ar'] ?? ''); ?></textarea>
        </div>
      </div>

      <button type="submit" name="save_settings" value="1" class="btn btn-luxury-primary">
        <i class="bi bi-check2 me-1"></i> <?php _e('btn_save_changes'); ?>
      </button>
    </form>
  </div>

  <div class="col-lg-4">
    <form method="post" action="" class="luxury-card p-4">
      <?php echo csrf_field(); ?>
      <h2 class="h5 text-emerald mb-4"><?php _e('portal_security'); ?></h2>
      <div class="mb-3">
        <label class="luxury-form-label"><?php _e('field_old_password'); ?></label>
        <input type="password" name="old_password" class="form-control luxury-input" required>
      </div>
      <div class="mb-3">
        <label class="luxury-form-label"><?php _e('field_new_password'); ?></label>
        <input type="password" name="new_password" class="form-control luxury-input" minlength="8" required>
      </div>
      <div class="mb-4">
        <label class="luxury-form-label"><?php _e('field_confirm_password'); ?></label>
        <input type="password" name="confirm_password" class="form-control luxury-input" minlength="8" required>
      </div>
      <button type="submit" name="change_admin_pass" value="1" class="btn btn-luxury-outline w-100">
        <?php _e('btn_save_changes'); ?>
      </button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../templates/admin/layout_end.php'; ?>
