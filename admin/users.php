<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$adminUser = auth_require_admin();
$pageTitle = __('admin_users');
$activeNav = 'users';
$pdo       = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $targetId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if ($targetId && $targetId === (int)$adminUser['id']) {
        flash_set('warning', lang_raw('user_cannot_modify_self'));
    } elseif ($targetId && isset($_POST['toggle_active'])) {
        $stmt = $pdo->prepare("UPDATE users SET active = IF(CAST(active AS UNSIGNED) = 1, b'0', b'1') WHERE id = :id");
        $stmt->execute([':id' => $targetId]);
        audit_log('ADMIN_TOGGLE_USER_ACTIVE', "Toggled active status for user #$targetId", (int)$adminUser['id']);
        flash_set('success', lang_raw('user_status_updated'));
    } elseif ($targetId && isset($_POST['change_role'])) {
        $newRole = ($_POST['role'] === 'admin') ? 'admin' : 'user';
        $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
        $stmt->execute([':role' => $newRole, ':id' => $targetId]);
        audit_log('ADMIN_CHANGE_USER_ROLE', "Changed role of user #$targetId to $newRole", (int)$adminUser['id']);
        flash_set('success', lang_raw('user_role_updated', ['role' => translate_role($newRole)]));
    } elseif ($targetId && isset($_POST['reset_password'])) {
        $tempPass = $_POST['new_password'] ?? '';
        if (!validate_password($tempPass)) {
            flash_set('danger', lang_raw('err_password_short'));
        } else {
            $hash = password_hash($tempPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = :pass WHERE id = :id");
            $stmt->execute([':pass' => $hash, ':id' => $targetId]);
            audit_log('ADMIN_PASSWORD_RESET', "Reset password for user #$targetId", (int)$adminUser['id']);
            flash_set('success', lang_raw('user_pw_reset_success', ['id' => $targetId]));
        }
    }
    header('Location: ' . DENTAL_BASE_URL . '/admin/users.php');
    exit;
}

$users = $pdo->query("SELECT id, username, email, role, add_date, image, CAST(active AS UNSIGNED) AS is_active FROM users ORDER BY id ASC")->fetchAll();

require_once __DIR__ . '/../templates/admin/layout_start.php';
?>

<div class="luxury-card-static p-0 overflow-hidden" style="border-top: 2px solid var(--dental-gold-champagne);">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="bg-ivory small text-muted-luxury text-uppercase">
        <tr>
          <th class="px-4 py-3"><?php _e('user_header'); ?></th>
          <th class="py-3"><?php _e('user_role'); ?></th>
          <th class="py-3"><?php _e('table_status'); ?></th>
          <th class="px-4 py-3 text-end"><?php _e('user_controls'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td class="px-4">
              <div class="d-flex align-items-center gap-3">
                <img src="<?php echo e(get_user_avatar_url($u['image'])); ?>" alt="" class="avatar-circle" width="38" height="38">
                <div>
                  <div class="fw-semibold text-emerald"><?php echo e($u['username']); ?></div>
                  <div class="small text-muted-luxury"><?php echo e($u['email']); ?></div>
                </div>
              </div>
            </td>
            <td>
              <span class="badge <?php echo ($u['role'] === 'admin') ? 'bg-emerald-deep text-gold' : 'bg-light text-dark border'; ?>">
                <?php echo e(translate_role($u['role'])); ?>
              </span>
            </td>
            <td>
              <span class="badge <?php echo $u['is_active'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                <?php echo $u['is_active'] ? __('badge_active') : __('badge_suspended'); ?>
              </span>
            </td>
            <td class="px-4 text-end">
              <?php if ((int)$u['id'] === (int)$adminUser['id']): ?>
                <span class="small text-muted-luxury"><i class="bi bi-shield-lock me-1"></i><?php _e('current_session'); ?></span>
              <?php else: ?>
                <div class="d-flex justify-content-end gap-2">
                  <form method="post" action="" class="d-flex gap-1">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                    <select name="role" class="form-select form-select-sm luxury-select py-1" style="min-height:30px;width:110px;">
                      <option value="user" <?php echo ($u['role'] === 'user') ? 'selected' : ''; ?>><?php _e('role_user'); ?></option>
                      <option value="admin" <?php echo ($u['role'] === 'admin') ? 'selected' : ''; ?>><?php _e('role_admin'); ?></option>
                    </select>
                    <button type="submit" name="change_role" value="1" class="btn btn-sm btn-luxury-outline py-1"><?php _e('user_role'); ?></button>
                  </form>
                  <form method="post" action="">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                    <button type="submit" name="toggle_active" value="1" class="btn btn-sm btn-outline-secondary py-1">
                      <?php echo $u['is_active'] ? __('badge_suspended') : __('badge_active'); ?>
                    </button>
                  </form>
                  <button type="button" class="btn btn-sm btn-outline-warning py-1" data-bs-toggle="modal" data-bs-target="#resetPassModal<?php echo (int)$u['id']; ?>">
                    <?php _e('user_reset_pw'); ?>
                  </button>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Password Reset Modals (Placed cleanly outside table hierarchy) -->
<?php foreach ($users as $u): ?>
  <?php if ((int)$u['id'] !== (int)$adminUser['id']): ?>
    <div class="modal fade" id="resetPassModal<?php echo (int)$u['id']; ?>" tabindex="-1" aria-labelledby="resetPassModalLabel<?php echo (int)$u['id']; ?>" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="" class="modal-content" data-prevent-double-submit="true">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
          <div class="modal-header">
            <h5 class="modal-title text-emerald" id="resetPassModalLabel<?php echo (int)$u['id']; ?>">
              <i class="bi bi-key text-gold me-2"></i><?php _e('user_reset_pw'); ?> — <?php echo e($u['username']); ?>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php _e('modal_close'); ?>"></button>
          </div>
          <div class="modal-body p-4">
            <label class="luxury-form-label"><?php _e('field_new_password'); ?> <span class="text-danger">*</span></label>
            <input type="password" name="new_password" class="form-control luxury-input" minlength="8" required>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-luxury-outline" data-bs-dismiss="modal"><?php _e('modal_close'); ?></button>
            <button type="submit" name="reset_password" value="1" class="btn btn-luxury-primary"><?php _e('user_confirm_reset'); ?></button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>
<?php endforeach; ?>

<?php require_once __DIR__ . '/../templates/admin/layout_end.php'; ?>
