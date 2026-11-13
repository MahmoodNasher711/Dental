<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$adminUser = auth_require_admin();
$pageTitle = __('admin_messages');
$activeNav = 'messages';
$pdo       = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (isset($_POST['toggle_read_id'])) {
        $mid = filter_input(INPUT_POST, 'toggle_read_id', FILTER_VALIDATE_INT);
        if ($mid) {
            $stmt = $pdo->prepare("UPDATE contact_us SET is_read = IF(is_read = 1, 0, 1) WHERE u_id = :id");
            $stmt->execute([':id' => $mid]);
        }
    } elseif (isset($_POST['delete_id'])) {
        $mid = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
        if ($mid) {
            $stmt = $pdo->prepare("DELETE FROM contact_us WHERE u_id = :id");
            $stmt->execute([':id' => $mid]);
            audit_log('ADMIN_DELETE_MESSAGE', "Deleted contact message #$mid", (int)$adminUser['id']);
            flash_set('success', lang_raw('msg_delete_success'));
        }
    }
    header('Location: ' . DENTAL_BASE_URL . '/admin/messages.php');
    exit;
}

$messages = $pdo->query("SELECT * FROM contact_us ORDER BY is_read ASC, u_id DESC")->fetchAll();

require_once __DIR__ . '/../templates/admin/layout_start.php';
?>

<div class="luxury-card p-0 overflow-hidden">
  <?php if (empty($messages)): ?>
    <div class="p-5 text-center text-muted-luxury">
      <i class="bi bi-envelope-open fs-1 d-block mb-3"></i>
      <?php _e('no_messages_found'); ?>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="bg-ivory small text-muted-luxury text-uppercase">
          <tr>
            <th class="px-4 py-3"><?php _e('msg_sender'); ?></th>
            <th class="py-3"><?php _e('msg_content'); ?></th>
            <th class="py-3"><?php _e('msg_received'); ?></th>
            <th class="px-4 py-3 text-end"><?php _e('msg_actions'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($messages as $m): ?>
            <tr class="<?php echo !$m['is_read'] ? 'fw-semibold' : 'opacity-75'; ?>">
              <td class="px-4">
                <div class="text-emerald"><?php echo e($m['cont_user_name']); ?></div>
                <div class="small text-muted-luxury"><?php echo e($m['cont_user_email']); ?></div>
              </td>
              <td style="max-width: 420px;"><?php echo nl2br(e($m['cont_user_text'])); ?></td>
              <td class="small text-muted-luxury"><?php echo e(format_localized_date($m['created_at'])); ?></td>
              <td class="px-4 text-end">
                <div class="d-flex justify-content-end gap-2">
                  <form method="post" action="">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="toggle_read_id" value="<?php echo (int)$m['u_id']; ?>">
                    <button type="submit" class="btn btn-sm btn-luxury-outline">
                      <?php echo $m['is_read'] ? __('msg_mark_unread') : __('msg_mark_read'); ?>
                    </button>
                  </form>
                  <form method="post" action="" onsubmit="return confirm('<?php _e('msg_confirm_delete'); ?>');">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="delete_id" value="<?php echo (int)$m['u_id']; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="<?php _e('action_delete'); ?>"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/admin/layout_end.php'; ?>
