<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/upload.php';

if (auth_is_logged_in()) {
    header('Location: ' . DENTAL_BASE_URL . (auth_is_admin() ? '/admin/index.php' : '/patient/index.php'));
    exit;
}

if (!empty($_GET['redirect'])) {
    auth_set_intended_url((string)$_GET['redirect']);
}
$intendedUrl = auth_get_intended_url();
$loginHref   = DENTAL_BASE_URL . '/login.php' . ($intendedUrl ? ('?redirect=' . rawurlencode($intendedUrl)) : '');
$isBookingRedirect = ($intendedUrl !== null && str_contains(strtolower($intendedUrl), 'booking.php'));

$pageTitle = __('nav_register');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name      = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';

    if (!validate_name($name)) {
        $errors[] = __('err_invalid_name');
    }
    if (!validate_email($email)) {
        $errors[] = __('err_invalid_email');
    }
    if (!validate_password($password)) {
        $errors[] = __('err_password_short');
    }
    if ($password !== $cpassword) {
        $errors[] = __('err_password_mismatch');
    }

    $uploadedImage = '';
    if (empty($errors) && !empty($_FILES['image']['name'])) {
        $up = handle_secure_image_upload($_FILES['image'], DENTAL_UPLOADS_DIR . DIRECTORY_SEPARATOR . 'avatars');
        if ($up['success']) {
            $uploadedImage = $up['filename'];
        } else {
            $errors[] = __('err_upload_failed');
        }
    }

    if (empty($errors)) {
        try {
            $stmtCheck = db()->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmtCheck->execute([':email' => $email]);
            if ($stmtCheck->fetch()) {
                $errors[] = __('err_email_exists');
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmtIns = db()->prepare(
                    "INSERT INTO users (username, email, password, add_date, active, role, image)
                     VALUES (:name, :email, :pass, NOW(), b'1', 'user', :img)"
                );
                $stmtIns->execute([
                    ':name'  => $name,
                    ':email' => $email,
                    ':pass'  => $hash,
                    ':img'   => $uploadedImage
                ]);
                flash_set('success', lang_raw('msg_registered_ok'));
                header('Location: ' . $loginHref);
                exit;
            }
        } catch (PDOException $e) {
            error_log('[DENTAL REGISTER ERROR] ' . $e->getMessage());
            $errors[] = __('err_required_fields');
        }
    }
}

require_once __DIR__ . '/templates/public/header.php';
?>

<section class="section-pad bg-ivory">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-9 col-lg-6">
        <div class="luxury-card p-4 p-md-5">
          <div class="text-center mb-4">
            <img src="<?php echo e(DENTAL_BASE_URL); ?>/assets/images/brand/logo-emblem.svg" alt="DENTAL" width="44" height="44" class="mb-3">
            <h1 class="h2 text-emerald mb-1"><?php _e('auth_register_title'); ?></h1>
            <p class="text-muted-luxury small mb-0"><?php _e('auth_register_sub'); ?></p>
          </div>

          <?php if ($isBookingRedirect && empty($errors)): ?>
            <div class="alert alert-info border-0 shadow-sm small mb-4">
              <div class="fw-semibold mb-1"><i class="bi bi-calendar2-check me-1"></i> <?php _e('booking_auth_notice_title'); ?></div>
              <div><?php _e('booking_auth_required'); ?></div>
            </div>
          <?php endif; ?>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger border-0 shadow-sm small mb-4">
              <?php foreach ($errors as $err): ?>
                <div><?php echo e($err); ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form method="post" action="" enctype="multipart/form-data" data-prevent-double-submit="true">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
              <label for="regName" class="luxury-form-label"><?php _e('field_username'); ?></label>
              <input type="text" id="regName" name="name" class="form-control luxury-input" value="<?php echo e($_POST['name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
              <label for="regEmail" class="luxury-form-label"><?php _e('field_email'); ?></label>
              <input type="email" id="regEmail" name="email" class="form-control luxury-input" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="regPass" class="luxury-form-label"><?php _e('field_password'); ?></label>
                <input type="password" id="regPass" name="password" class="form-control luxury-input" minlength="8" required>
              </div>
              <div class="col-md-6">
                <label for="regCPass" class="luxury-form-label"><?php _e('field_confirm_password'); ?></label>
                <input type="password" id="regCPass" name="cpassword" class="form-control luxury-input" minlength="8" required>
              </div>
            </div>
            <div class="mb-4">
              <label for="regImage" class="luxury-form-label"><?php _e('field_avatar'); ?></label>
              <input type="file" id="regImage" name="image" class="form-control luxury-input" accept="image/jpeg,image/png">
            </div>
            <button type="submit" class="btn btn-luxury-primary w-100 py-2 mb-3">
              <i class="bi bi-person-plus me-1"></i> <?php _e('btn_create_account'); ?>
            </button>
          </form>

          <div class="text-center small text-muted-luxury pt-3 border-top">
            <?php _e('have_account_prompt'); ?>
            <a href="<?php echo e($loginHref); ?>" class="text-emerald fw-semibold text-decoration-none ms-1">
              <?php _e('nav_login'); ?>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/templates/public/footer.php'; ?>
