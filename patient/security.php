<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$portalUser = auth_require_patient();
$pageTitle  = __('portal_security');
$activeNav  = 'security';
$uid        = (int)$portalUser['id'];
$pdo        = db();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $oldPass     = $_POST['old_password'] ?? '';
    $newPass     = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :uid LIMIT 1");
    $stmt->execute([':uid' => $uid]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($oldPass, $row['password'])) {
        $errors[] = __('err_old_password');
    } elseif (!validate_password($newPass)) {
        $errors[] = __('err_password_short');
    } elseif ($newPass !== $confirmPass) {
        $errors[] = __('err_password_mismatch');
    } else {
        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        $stmtUpd = $pdo->prepare("UPDATE users SET password = :pass WHERE id = :uid");
        $stmtUpd->execute([':pass' => $newHash, ':uid' => $uid]);
        audit_log('PASSWORD_CHANGED', 'Patient changed password securely', $uid);
        flash_set('success', lang_raw('msg_password_updated'));
        header('Location: ' . DENTAL_BASE_URL . '/patient/security.php');
        exit;
    }
}

require_once __DIR__ . '/../templates/patient/layout_start.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger border-0 shadow-sm mb-4">
        <?php foreach ($errors as $err): ?>
          <div><?php echo e($err); ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="" class="luxury-card p-4 p-md-5" data-prevent-double-submit="true">
      <?php echo csrf_field(); ?>
      <h2 class="h4 text-emerald mb-4"><?php _e('portal_security'); ?></h2>

      <div class="mb-3">
        <label for="oldPass" class="luxury-form-label"><?php _e('field_old_password'); ?></label>
        <input type="password" id="oldPass" name="old_password" class="form-control luxury-input" required>
      </div>
      <div class="mb-3">
        <label for="newPass" class="luxury-form-label"><?php _e('field_new_password'); ?></label>
        <input type="password" id="newPass" name="new_password" class="form-control luxury-input" minlength="8" required>
      </div>
      <div class="mb-4">
        <label for="confPass" class="luxury-form-label"><?php _e('field_confirm_password'); ?></label>
        <input type="password" id="confPass" name="confirm_password" class="form-control luxury-input" minlength="8" required>
      </div>

      <button type="submit" class="btn btn-luxury-primary w-100">
        <i class="bi bi-shield-check me-1"></i> <?php _e('btn_save_changes'); ?>
      </button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../templates/patient/layout_end.php'; ?>
