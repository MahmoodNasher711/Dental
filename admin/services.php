<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$adminUser = auth_require_admin();
$pageTitle = __('admin_services');
$activeNav = 'services';
$pdo       = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action_type'] ?? '';

    if ($action === 'create') {
        $nameEn = trim($_POST['service_name_en'] ?? '');
        $nameAr = trim($_POST['service_name_ar'] ?? '');
        $price  = trim($_POST['service_price'] ?? '');
        $descEn = trim($_POST['service_description_en'] ?? '');
        $descAr = trim($_POST['service_description_ar'] ?? '');
        $dur    = max(15, min(480, (int)($_POST['duration_minutes'] ?? 30)));
        $active = isset($_POST['is_active']) ? 1 : 0;

        // Validation: primary English name and price are required
        if ($nameEn !== '' && $price !== '') {
            $legacyName = $nameEn;
            $legacyDesc = $descEn ?: $descAr;

            $stmt = $pdo->prepare(
                "INSERT INTO services (service_name, service_name_en, service_name_ar, service_description_en, service_description_ar, description, service_price, duration_minutes, is_active)
                 VALUES (:name_leg, :name_en, :name_ar, :desc_en, :desc_ar, :desc_leg, :price, :dur, :act)"
            );
            $stmt->execute([
                ':name_leg' => $legacyName,
                ':name_en'  => $nameEn,
                ':name_ar'  => ($nameAr !== '') ? $nameAr : null,
                ':desc_en'  => ($descEn !== '') ? $descEn : null,
                ':desc_ar'  => ($descAr !== '') ? $descAr : null,
                ':desc_leg' => ($legacyDesc !== '') ? $legacyDesc : null,
                ':price'    => $price,
                ':dur'      => $dur,
                ':act'      => $active
            ]);
            $displayName = ($CURRENT_LANG === 'ar' && $nameAr !== '') ? $nameAr : $nameEn;
            audit_log('ADMIN_CREATE_SERVICE', "Created service: $nameEn / $nameAr ($price)", (int)$adminUser['id']);
            flash_set('success', lang_raw('srv_add_success', ['name' => $displayName]));
        } else {
            flash_set('danger', lang_raw('err_required_fields'));
        }
    } elseif ($action === 'edit') {
        $sid    = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
        $nameEn = trim($_POST['service_name_en'] ?? '');
        $nameAr = trim($_POST['service_name_ar'] ?? '');
        $price  = trim($_POST['service_price'] ?? '');
        $descEn = trim($_POST['service_description_en'] ?? '');
        $descAr = trim($_POST['service_description_ar'] ?? '');
        $dur    = max(15, min(480, (int)($_POST['duration_minutes'] ?? 30)));
        $active = isset($_POST['is_active']) ? 1 : 0;

        if ($sid && $sid > 0 && $nameEn !== '' && $price !== '') {
            $legacyName = $nameEn;
            $legacyDesc = $descEn ?: $descAr;

            $stmt = $pdo->prepare(
                "UPDATE services
                 SET service_name = :name_leg,
                     service_name_en = :name_en,
                     service_name_ar = :name_ar,
                     service_description_en = :desc_en,
                     service_description_ar = :desc_ar,
                     description = :desc_leg,
                     service_price = :price,
                     duration_minutes = :dur,
                     is_active = :act
                 WHERE service_id = :id"
            );
            $stmt->execute([
                ':name_leg' => $legacyName,
                ':name_en'  => $nameEn,
                ':name_ar'  => ($nameAr !== '') ? $nameAr : null,
                ':desc_en'  => ($descEn !== '') ? $descEn : null,
                ':desc_ar'  => ($descAr !== '') ? $descAr : null,
                ':desc_leg' => ($legacyDesc !== '') ? $legacyDesc : null,
                ':price'    => $price,
                ':dur'      => $dur,
                ':act'      => $active,
                ':id'       => $sid
            ]);
            $displayName = ($CURRENT_LANG === 'ar' && $nameAr !== '') ? $nameAr : $nameEn;
            audit_log('ADMIN_EDIT_SERVICE', "Updated service #$sid ($nameEn)", (int)$adminUser['id']);
            flash_set('success', lang_raw('srv_update_success', ['name' => $displayName]));
        } else {
            flash_set('danger', lang_raw('err_required_fields'));
        }
    }
    header('Location: ' . DENTAL_BASE_URL . '/admin/services.php');
    exit;
}

$services = $pdo->query("SELECT * FROM services ORDER BY service_id ASC")->fetchAll();

require_once __DIR__ . '/../templates/admin/layout_start.php';
?>

<div class="d-flex justify-content-end mb-4">
  <button type="button" class="btn btn-luxury-primary" data-bs-toggle="modal" data-bs-target="#addSrvModal">
    <i class="bi bi-plus-circle me-1"></i> <?php _e('action_add_new'); ?>
  </button>
</div>

<div class="luxury-card-static p-0 overflow-hidden" style="border-top: 2px solid var(--dental-gold-champagne);">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="bg-ivory small text-muted-luxury text-uppercase">
        <tr>
          <th class="px-4 py-3"><?php _e('table_id'); ?></th>
          <th class="py-3"><?php _e('srv_name_en'); ?> / <?php _e('srv_name_ar'); ?></th>
          <th class="py-3"><?php _e('srv_price'); ?></th>
          <th class="py-3"><?php _e('srv_duration'); ?></th>
          <th class="py-3"><?php _e('table_status'); ?></th>
          <th class="px-4 py-3 text-end"><?php _e('table_actions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($services)): ?>
          <tr><td colspan="6" class="text-center py-5 text-muted-luxury"><?php _e('no_services_found'); ?></td></tr>
        <?php else: ?>
          <?php foreach ($services as $s): ?>
            <tr>
              <td class="px-4 fw-semibold">#<?php echo (int)$s['service_id']; ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span class="fw-semibold text-emerald text-capitalize"><?php echo e(service_name($s)); ?></span>
                  <?php if (empty($s['service_name_ar'])): ?>
                    <span class="badge bg-warning-subtle text-dark border" style="font-size:0.7rem; font-weight: 500;">
                      <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i><?php _e('srv_missing_ar_badge'); ?>
                    </span>
                  <?php endif; ?>
                </div>
                <div class="small text-muted-luxury mt-1">
                  <?php if ($CURRENT_LANG === 'ar'): ?>
                    <span dir="ltr" class="text-muted"><i class="bi bi-translate me-1"></i>EN: <?php echo e($s['service_name_en'] ?? $s['service_name']); ?></span>
                  <?php else: ?>
                    <span dir="rtl" class="text-muted"><i class="bi bi-translate me-1"></i>AR: <?php echo !empty($s['service_name_ar']) ? e($s['service_name_ar']) : '<em>(' . __('srv_missing_ar') . ')</em>'; ?></span>
                  <?php endif; ?>
                </div>
                <?php $desc = service_description($s); ?>
                <?php if (!empty($desc)): ?>
                  <div class="small text-muted-luxury mt-1" style="max-width: 480px;"><?php echo e(mb_substr($desc, 0, 95)); ?><?php echo mb_strlen($desc) > 95 ? '...' : ''; ?></div>
                <?php endif; ?>
              </td>
              <td class="fw-semibold"><?php echo e($s['service_price']); ?></td>
              <td><?php echo (int)$s['duration_minutes']; ?> <?php _e('minutes'); ?></td>
              <td>
                <span class="badge <?php echo $s['is_active'] ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?>">
                  <?php echo $s['is_active'] ? __('badge_active') : __('badge_inactive'); ?>
                </span>
              </td>
              <td class="px-4 text-end">
                <button type="button" class="btn btn-sm btn-luxury-outline" data-bs-toggle="modal" data-bs-target="#editSrvModal<?php echo (int)$s['service_id']; ?>" aria-label="<?php _e('action_edit'); ?>">
                  <i class="bi bi-pencil me-1"></i> <?php _e('action_edit'); ?>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Edit Service Modals (Bilingual Support, Centered, Zero Clipping) -->
<?php foreach ($services as $s): ?>
  <div class="modal fade" id="editSrvModal<?php echo (int)$s['service_id']; ?>" tabindex="-1" aria-labelledby="editSrvModalLabel<?php echo (int)$s['service_id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <form method="post" action="" class="modal-content" data-prevent-double-submit="true">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action_type" value="edit">
        <input type="hidden" name="service_id" value="<?php echo (int)$s['service_id']; ?>">
        <div class="modal-header">
          <h5 class="modal-title text-emerald" id="editSrvModalLabel<?php echo (int)$s['service_id']; ?>">
            <i class="bi bi-pencil-square text-gold me-2"></i><?php _e('action_edit'); ?> — <?php echo e(service_name($s)); ?>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php _e('modal_close'); ?>"></button>
        </div>
        <div class="modal-body p-4">
          <p class="small text-muted-luxury mb-3"><i class="bi bi-info-circle me-1"></i> <?php _e('srv_bilingual_hint'); ?></p>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="srvNameEn<?php echo (int)$s['service_id']; ?>" class="luxury-form-label">
                <?php _e('srv_name_en'); ?> <span class="text-danger">*</span>
              </label>
              <input type="text" id="srvNameEn<?php echo (int)$s['service_id']; ?>" name="service_name_en" class="form-control luxury-input" dir="ltr" value="<?php echo e($s['service_name_en'] ?? $s['service_name']); ?>" required>
            </div>
            <div class="col-md-6">
              <label for="srvNameAr<?php echo (int)$s['service_id']; ?>" class="luxury-form-label">
                <?php _e('srv_name_ar'); ?>
              </label>
              <input type="text" id="srvNameAr<?php echo (int)$s['service_id']; ?>" name="service_name_ar" class="form-control luxury-input" dir="rtl" value="<?php echo e($s['service_name_ar'] ?? ''); ?>" placeholder="<?php _e('placeholder_srv_ar'); ?>">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="srvPrice<?php echo (int)$s['service_id']; ?>" class="luxury-form-label">
                <?php _e('srv_price'); ?> <span class="text-danger">*</span>
              </label>
              <input type="text" id="srvPrice<?php echo (int)$s['service_id']; ?>" name="service_price" class="form-control luxury-input" value="<?php echo e($s['service_price']); ?>" required>
            </div>
            <div class="col-md-6">
              <label for="srvDur<?php echo (int)$s['service_id']; ?>" class="luxury-form-label">
                <?php _e('srv_duration'); ?> <span class="text-danger">*</span>
              </label>
              <input type="number" id="srvDur<?php echo (int)$s['service_id']; ?>" name="duration_minutes" class="form-control luxury-input" min="15" max="480" step="15" value="<?php echo (int)$s['duration_minutes']; ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="srvDescEn<?php echo (int)$s['service_id']; ?>" class="luxury-form-label"><?php _e('srv_desc_en'); ?></label>
            <textarea id="srvDescEn<?php echo (int)$s['service_id']; ?>" name="service_description_en" rows="2" class="form-control luxury-input" dir="ltr"><?php echo e($s['service_description_en'] ?? $s['description'] ?? ''); ?></textarea>
          </div>

          <div class="mb-3">
            <label for="srvDescAr<?php echo (int)$s['service_id']; ?>" class="luxury-form-label"><?php _e('srv_desc_ar'); ?></label>
            <textarea id="srvDescAr<?php echo (int)$s['service_id']; ?>" name="service_description_ar" rows="2" class="form-control luxury-input" dir="rtl"><?php echo e($s['service_description_ar'] ?? ''); ?></textarea>
          </div>

          <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" name="is_active" id="srvAct<?php echo (int)$s['service_id']; ?>" <?php echo $s['is_active'] ? 'checked' : ''; ?>>
            <label class="form-check-label" for="srvAct<?php echo (int)$s['service_id']; ?>"><?php _e('srv_active'); ?></label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-luxury-outline" data-bs-dismiss="modal"><?php _e('modal_close'); ?></button>
          <button type="submit" class="btn btn-luxury-primary"><?php _e('btn_save_changes'); ?></button>
        </div>
      </form>
    </div>
  </div>
<?php endforeach; ?>

<!-- Add Service Modal (Bilingual Support, Centered) -->
<div class="modal fade" id="addSrvModal" tabindex="-1" aria-labelledby="addSrvModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form method="post" action="" class="modal-content" data-prevent-double-submit="true">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action_type" value="create">
      <div class="modal-header">
        <h5 class="modal-title text-emerald" id="addSrvModalLabel">
          <i class="bi bi-plus-circle text-gold me-2"></i><?php _e('action_add_new'); ?> — <?php _e('admin_services'); ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php _e('modal_close'); ?>"></button>
      </div>
      <div class="modal-body p-4">
        <p class="small text-muted-luxury mb-3"><i class="bi bi-info-circle me-1"></i> <?php _e('srv_bilingual_hint'); ?></p>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="addSrvNameEn" class="luxury-form-label">
              <?php _e('srv_name_en'); ?> <span class="text-danger">*</span>
            </label>
            <input type="text" id="addSrvNameEn" name="service_name_en" class="form-control luxury-input" dir="ltr" placeholder="<?php _e('placeholder_srv_en'); ?>" required>
          </div>
          <div class="col-md-6">
            <label for="addSrvNameAr" class="luxury-form-label">
              <?php _e('srv_name_ar'); ?>
            </label>
            <input type="text" id="addSrvNameAr" name="service_name_ar" class="form-control luxury-input" dir="rtl" placeholder="<?php _e('placeholder_srv_ar'); ?>">
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="addSrvPrice" class="luxury-form-label">
              <?php _e('srv_price'); ?> <span class="text-danger">*</span>
            </label>
            <input type="text" id="addSrvPrice" name="service_price" class="form-control luxury-input" placeholder="<?php _e('placeholder_price'); ?>" required>
          </div>
          <div class="col-md-6">
            <label for="addSrvDur" class="luxury-form-label">
              <?php _e('srv_duration'); ?> <span class="text-danger">*</span>
            </label>
            <input type="number" id="addSrvDur" name="duration_minutes" class="form-control luxury-input" min="15" max="480" step="15" value="30" required>
          </div>
        </div>

        <div class="mb-3">
          <label for="addSrvDescEn" class="luxury-form-label"><?php _e('srv_desc_en'); ?></label>
          <textarea id="addSrvDescEn" name="service_description_en" rows="2" class="form-control luxury-input" dir="ltr" placeholder="<?php _e('placeholder_desc_en'); ?>"></textarea>
        </div>

        <div class="mb-3">
          <label for="addSrvDescAr" class="luxury-form-label"><?php _e('srv_desc_ar'); ?></label>
          <textarea id="addSrvDescAr" name="service_description_ar" rows="2" class="form-control luxury-input" dir="rtl" placeholder="<?php _e('placeholder_desc_ar'); ?>"></textarea>
        </div>

        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" name="is_active" id="addSrvAct" checked>
          <label class="form-check-label" for="addSrvAct"><?php _e('srv_active'); ?></label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-luxury-outline" data-bs-dismiss="modal"><?php _e('modal_close'); ?></button>
        <button type="submit" class="btn btn-luxury-primary"><?php _e('action_add_new'); ?></button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../templates/admin/layout_end.php'; ?>
