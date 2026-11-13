<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (auth_is_logged_in()) {
    header('Location: ' . DENTAL_BASE_URL . (auth_is_admin() ? '/admin/index.php' : '/patient/index.php'));
    exit;
}

if (!empty($_GET['redirect'])) {
    auth_set_intended_url((string)$_GET['redirect']);
}
$intendedUrl = auth_get_intended_url();
$registerHref = DENTAL_BASE_URL . '/register.php' . ($intendedUrl ? ('?redirect=' . rawurlencode($intendedUrl)) : '');
$isBookingRedirect = ($intendedUrl !== null && str_contains(strtolower($intendedUrl), 'booking.php'));

$pageTitle = __('nav_login');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!validate_email($email)) {
        $errors[] = __('err_invalid_email');
    } else {
        $rate = rate_limit_check($email);
        if (!$rate['allowed']) {
            $errors[] = __('err_rate_limited', ['minutes' => $rate['remaining_minutes']]);
        } else {
            $stmt = db()->prepare(
                "SELECT id, username, email, password, role, CAST(active AS UNSIGNED) AS is_active
                 FROM users
                 WHERE email = :email
                 LIMIT 1"
            );
            $stmt->execute([':email' => $email]);
            $userRow = $stmt->fetch();

            if ($userRow && password_verify($password, $userRow['password'])) {
                if ((int)$userRow['is_active'] !== 1) {
                    $errors[] = __('err_account_inactive');
                } else {
                    rate_limit_clear($email);
                    $intended = auth_consume_intended_url();
                    auth_login($userRow);
                    if ($userRow['role'] === 'admin') {
                        header('Location: ' . DENTAL_BASE_URL . '/admin/index.php');
                    } else {
                        header('Location: ' . ($intended ?? (DENTAL_BASE_URL . '/patient/index.php')));
                    }
                    exit;
                }
            } else {
                rate_limit_record_failure($email);
                $errors[] = __('err_invalid_credentials');
            }
        }
    }
}

require_once __DIR__ . '/templates/public/header.php';
?>

<section class="section-pad bg-ivory">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-5">
        <div class="luxury-card p-4 p-md-5">
          <div class="text-center mb-4">
            <img src="<?php echo e(DENTAL_BASE_URL); ?>/assets/images/brand/logo-emblem.svg" alt="DENTAL" width="44" height="44" class="mb-3">
            <h1 class="h2 text-emerald mb-1"><?php _e('auth_login_title'); ?></h1>
            <p class="text-muted-luxury small mb-0"><?php _e('auth_login_sub'); ?></p>
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

          <form method="post" action="" data-prevent-double-submit="true">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
              <label for="loginEmail" class="luxury-form-label"><?php _e('field_email'); ?></label>
              <input type="email" id="loginEmail" name="email" class="form-control luxury-input" value="<?php echo e($_POST['email'] ?? ''); ?>" required autofocus>
            </div>
            <div class="mb-4">
              <label for="loginPass" class="luxury-form-label"><?php _e('field_password'); ?></label>
              <input type="password" id="loginPass" name="password" class="form-control luxury-input" required>
            </div>
            <button type="submit" class="btn btn-luxury-primary w-100 py-2 mb-3">
              <i class="bi bi-box-arrow-in-right me-1"></i> <?php _e('btn_sign_in'); ?>
            </button>
          </form>

          <div class="text-center small text-muted-luxury pt-3 border-top">
            <?php _e('no_account_prompt'); ?>
            <a href="<?php echo e($registerHref); ?>" class="text-emerald fw-semibold text-decoration-none ms-1">
              <?php _e('nav_register'); ?>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/templates/public/footer.php'; ?>
