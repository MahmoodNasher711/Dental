<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/upload.php';

$adminUser = auth_require_admin();
$pageTitle = __('admin_dentists');
$activeNav = 'dentists';
$pdo       = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (isset($_POST['action_type']) && $_POST['action_type'] === 'create') {
        $nameEn = trim($_POST['dentist_name_en'] ?? '');
        $nameAr = trim($_POST['dentist_name_ar'] ?? '');
        $age    = $_POST['dentist_age'] ?? '';
        $email  = trim($_POST['dentist_email'] ?? '');
        $specEn = trim($_POST['specialization_en'] ?? '');
        $specAr = trim($_POST['specialization_ar'] ?? '');
        $bioEn  = trim($_POST['dentist_brief_en'] ?? '');
        $bioAr  = trim($_POST['dentist_brief_ar'] ?? '');

        if (!validate_name($nameEn) || !validate_age($age) || !validate_email($email)) {
            flash_set('danger', lang_raw('err_required_fields'));
        } else {
            $img = null;
            if (!empty($_FILES['image']['name'])) {
                $up = handle_secure_image_upload($_FILES['image'], DENTAL_DOCTORS_IMG_DIR);
                if ($up['success']) {
                    $img = $up['filename'];
                } else {
                    flash_set('danger', lang_raw('err_upload_failed'));
                    header('Location: ' . DENTAL_BASE_URL . '/admin/dentists.php');
                    exit;
                }
            }
            $stmt = $pdo->prepare(
                "INSERT INTO dentists (dentist_name, dentist_name_ar, dentist_age, dentist_email, specialization, specialization_ar, dentist_brief, dentist_brief_ar, image, is_active)
                 VALUES (:name, :name_ar, :age, :email, :spec, :spec_ar, :brief, :brief_ar, :img, 1)"
            );
            $stmt->execute([
                ':name'     => $nameEn,
                ':name_ar'  => ($nameAr !== '') ? $nameAr : null,
                ':age'      => (int)$age,
                ':email'    => $email,
                ':spec'     => ($specEn !== '') ? $specEn : null,
                ':spec_ar'  => ($specAr !== '') ? $specAr : null,
                ':brief'    => $bioEn ?: ($bioAr ?: lang_raw('doctor_profile_title')),
                ':brief_ar' => ($bioAr !== '') ? $bioAr : null,
                ':img'      => $img
            ]);
            $displayName = ($CURRENT_LANG === 'ar' && $nameAr !== '') ? $nameAr : $nameEn;
            audit_log('ADMIN_CREATE_DENTIST', "Added Dr. $nameEn / $nameAr", (int)$adminUser['id']);
            flash_set('success', lang_raw('dentist_add_success', ['name' => $displayName]));
        }
        header('Location: ' . DENTAL_BASE_URL . '/admin/dentists.php');
        exit;
    }

    if (isset($_POST['action_type']) && $_POST['action_type'] === 'edit') {
        $did    = filter_input(INPUT_POST, 'dentist_id', FILTER_VALIDATE_INT);
        $nameEn = trim($_POST['dentist_name_en'] ?? '');
        $nameAr = trim($_POST['dentist_name_ar'] ?? '');
        $age    = $_POST['dentist_age'] ?? '';
        $email  = trim($_POST['dentist_email'] ?? '');
        $specEn = trim($_POST['specialization_en'] ?? '');
        $specAr = trim($_POST['specialization_ar'] ?? '');
        $bioEn  = trim($_POST['dentist_brief_en'] ?? '');
        $bioAr  = trim($_POST['dentist_brief_ar'] ?? '');
        $active = isset($_POST['is_active']) ? 1 : 0;

        if ($did && validate_name($nameEn) && validate_age($age) && validate_email($email)) {
            $stmtCur = $pdo->prepare("SELECT image FROM dentists WHERE dentist_id = :id");
            $stmtCur->execute([':id' => $did]);
            $curRow = $stmtCur->fetch();
            $img = $curRow ? $curRow['image'] : null;

            if (!empty($_FILES['image']['name'])) {
                $up = handle_secure_image_upload($_FILES['image'], DENTAL_DOCTORS_IMG_DIR);
                if ($up['success']) {
                    $img = $up['filename'];
                } else {
                    flash_set('danger', lang_raw('err_upload_failed'));
                    header('Location: ' . DENTAL_BASE_URL . '/admin/dentists.php');
                    exit;
                }
            }

            $stmt = $pdo->prepare(
                "UPDATE dentists
                 SET dentist_name = :name,
                     dentist_name_ar = :name_ar,
                     dentist_age = :age,
                     dentist_email = :email,
                     specialization = :spec,
                     specialization_ar = :spec_ar,
                     dentist_brief = :brief,
                     dentist_brief_ar = :brief_ar,
                     image = :img,
                     is_active = :act
                 WHERE dentist_id = :id"
            );
            $stmt->execute([
                ':name'     => $nameEn,
                ':name_ar'  => ($nameAr !== '') ? $nameAr : null,
                ':age'      => (int)$age,
                ':email'    => $email,
                ':spec'     => ($specEn !== '') ? $specEn : null,
                ':spec_ar'  => ($specAr !== '') ? $specAr : null,
                ':brief'    => $bioEn ?: ($bioAr ?: lang_raw('doctor_profile_title')),
                ':brief_ar' => ($bioAr !== '') ? $bioAr : null,
                ':img'      => $img,
                ':act'      => $active,
                ':id'       => $did
            ]);
            $displayName = ($CURRENT_LANG === 'ar' && $nameAr !== '') ? $nameAr : $nameEn;
            audit_log('ADMIN_EDIT_DENTIST', "Updated dentist #$did ($nameEn)", (int)$adminUser['id']);
            flash_set('success', lang_raw('dentist_update_success', ['name' => $displayName]));
        } else {
            flash_set('danger', lang_raw('err_required_fields'));
        }
        header('Location: ' . DENTAL_BASE_URL . '/admin/dentists.php');
        exit;
    }

    if (isset($_POST['delete_id'])) {
        $delId = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
        if ($delId) {
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE dentist_id = :id");
            $stmtCount->execute([':id' => $delId]);
            if ((int)$stmtCount->fetchColumn() > 0) {
                $stmtDeact = $pdo->prepare("UPDATE dentists SET is_active = 0 WHERE dentist_id = :id");
                $stmtDeact->execute([':id' => $delId]);
                audit_log('ADMIN_DEACTIVATE_DENTIST', "Deactivated dentist #$delId (preserved appointments)", (int)$adminUser['id']);
                flash_set('warning', lang_raw('dentist_has_appointments', ['id' => $delId]));
            } else {
                $stmtDel = $pdo->prepare("DELETE FROM dentists WHERE dentist_id = :id");
                $stmtDel->execute([':id' => $delId]);
                audit_log('ADMIN_DELETE_DENTIST', "Deleted dentist #$delId", (int)$adminUser['id']);
                flash_set('success', lang_raw('dentist_delete_success', ['id' => $delId]));
            }
        }
        header('Location: ' . DENTAL_BASE_URL . '/admin/dentists.php');
        exit;
    }
}

$dentists = $pdo->query("SELECT * FROM dentists ORDER BY dentist_id ASC")->fetchAll();

require_once __DIR__ . '/../templates/admin/layout_start.php';
?>

<div class="d-flex justify-content-end mb-4">
  <button type="button" class="btn btn-luxury-primary" data-bs-toggle="modal" data-bs-target="#addDentistModal">
    <i class="bi bi-plus-circle me-1"></i> <?php _e('action_add_new'); ?>
  </button>
</div>

<div class="row g-4">
  <?php foreach ($dentists as $doc): ?>
    <div class="col-md-6 col-xl-4">
      <div class="luxury-card h-100 d-flex flex-column">
        <div class="dentist-card-img-wrap" style="height: 240px;">
          <img src="<?php echo e(get_dentist_photo_url($doc['image'])); ?>" alt="<?php echo e(dentist_name($doc)); ?>">
          <?php $spec = dentist_specialization($doc); ?>
          <?php if (!empty($spec)): ?>
            <span class="dentist-spec-badge"><?php echo e($spec); ?></span>
          <?php endif; ?>
        </div>
        <div class="p-4 flex-grow-1 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h3 class="h5 text-emerald mb-0"><?php echo e(dentist_name($doc)); ?></h3>
              <span class="badge <?php echo $doc['is_active'] ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?>">
                <?php echo $doc['is_active'] ? __('badge_active') : __('badge_inactive'); ?>
              </span>
            </div>
            <div class="small text-muted-luxury mb-2">
              <?php echo e($doc['dentist_email']); ?> | <?php _e('doctor_age_label'); ?>: <?php echo (int)$doc['dentist_age']; ?>
            </div>
            <?php $bio = dentist_bio($doc); ?>
            <p class="small text-muted-luxury mb-3" style="line-height: 1.6;"><?php echo e(mb_substr($bio, 0, 130)); ?><?php echo mb_strlen($bio) > 130 ? '...' : ''; ?></p>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-luxury-outline flex-grow-1" data-bs-toggle="modal" data-bs-target="#editDocModal<?php echo (int)$doc['dentist_id']; ?>">
              <i class="bi bi-pencil me-1"></i> <?php _e('action_edit'); ?>
            </button>
            <form method="post" action="" onsubmit="return confirm('<?php _e('confirm_cancel_prompt'); ?>');">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="delete_id" value="<?php echo (int)$doc['dentist_id']; ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="<?php _e('action_delete'); ?>"><i class="bi bi-trash"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Edit Dentist Modals (Bilingual, Centered) -->
<?php foreach ($dentists as $doc): ?>
  <div class="modal fade" id="editDocModal<?php echo (int)$doc['dentist_id']; ?>" tabindex="-1" aria-labelledby="editDocModalLabel<?php echo (int)$doc['dentist_id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <form method="post" action="" enctype="multipart/form-data" class="modal-content" data-prevent-double-submit="true">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action_type" value="edit">
        <input type="hidden" name="dentist_id" value="<?php echo (int)$doc['dentist_id']; ?>">
        <div class="modal-header">
          <h5 class="modal-title text-emerald" id="editDocModalLabel<?php echo (int)$doc['dentist_id']; ?>">
            <i class="bi bi-pencil-square text-gold me-2"></i><?php _e('action_edit'); ?> — <?php echo e(dentist_name($doc)); ?>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php _e('modal_close'); ?>"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="luxury-form-label"><?php _e('dentist_name_en'); ?> <span class="text-danger">*</span></label>
              <input type="text" name="dentist_name_en" class="form-control luxury-input" dir="ltr" value="<?php echo e($doc['dentist_name']); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="luxury-form-label"><?php _e('dentist_name_ar'); ?></label>
              <input type="text" name="dentist_name_ar" class="form-control luxury-input" dir="rtl" value="<?php echo e($doc['dentist_name_ar'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
              <label class="luxury-form-label"><?php _e('dentist_spec_en'); ?></label>
              <input type="text" name="specialization_en" class="form-control luxury-input" dir="ltr" value="<?php echo e($doc['specialization'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
              <label class="luxury-form-label"><?php _e('dentist_spec_ar'); ?></label>
              <input type="text" name="specialization_ar" class="form-control luxury-input" dir="rtl" value="<?php echo e($doc['specialization_ar'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
              <label class="luxury-form-label"><?php _e('dentist_email_input'); ?> <span class="text-danger">*</span></label>
              <input type="email" name="dentist_email" class="form-control luxury-input" value="<?php echo e($doc['dentist_email']); ?>" required>
            </div>
            <div class="col-md-3">
              <label class="luxury-form-label"><?php _e('dentist_age_input'); ?> <span class="text-danger">*</span></label>
              <input type="number" name="dentist_age" class="form-control luxury-input" min="18" max="100" value="<?php echo (int)$doc['dentist_age']; ?>" required>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="is_active" id="activeDoc<?php echo (int)$doc['dentist_id']; ?>" <?php echo $doc['is_active'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="activeDoc<?php echo (int)$doc['dentist_id']; ?>"><?php _e('dentist_active_label'); ?></label>
              </div>
            </div>
            <div class="col-12">
              <label class="luxury-form-label"><?php _e('dentist_bio_en'); ?></label>
              <textarea name="dentist_brief_en" rows="3" class="form-control luxury-input" dir="ltr"><?php echo e($doc['dentist_brief']); ?></textarea>
            </div>
            <div class="col-12">
              <label class="luxury-form-label"><?php _e('dentist_bio_ar'); ?></label>
              <textarea name="dentist_brief_ar" rows="3" class="form-control luxury-input" dir="rtl"><?php echo e($doc['dentist_brief_ar'] ?? ''); ?></textarea>
            </div>
            <div class="col-12">
              <label class="luxury-form-label"><?php _e('dentist_replace_photo'); ?></label>
              <input type="file" name="image" class="form-control luxury-input" accept="image/jpeg,image/png">
            </div>
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

<!-- Add Dentist Modal (Bilingual, Centered) -->
<div class="modal fade" id="addDentistModal" tabindex="-1" aria-labelledby="addDentistModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form method="post" action="" enctype="multipart/form-data" class="modal-content" data-prevent-double-submit="true">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action_type" value="create">
      <div class="modal-header">
        <h5 class="modal-title text-emerald" id="addDentistModalLabel">
          <i class="bi bi-person-plus text-gold me-2"></i><?php _e('action_add_new'); ?> — <?php _e('admin_dentists'); ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php _e('modal_close'); ?>"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="luxury-form-label"><?php _e('dentist_name_en'); ?> <span class="text-danger">*</span></label>
            <input type="text" name="dentist_name_en" class="form-control luxury-input" dir="ltr" required>
          </div>
          <div class="col-md-6">
            <label class="luxury-form-label"><?php _e('dentist_name_ar'); ?></label>
            <input type="text" name="dentist_name_ar" class="form-control luxury-input" dir="rtl" placeholder="<?php _e('placeholder_dentist_ar'); ?>">
          </div>
          <div class="col-md-6">
            <label class="luxury-form-label"><?php _e('dentist_spec_en'); ?></label>
            <input type="text" name="specialization_en" class="form-control luxury-input" dir="ltr" placeholder="<?php _e('placeholder_spec_en'); ?>">
          </div>
          <div class="col-md-6">
            <label class="luxury-form-label"><?php _e('dentist_spec_ar'); ?></label>
            <input type="text" name="specialization_ar" class="form-control luxury-input" dir="rtl" placeholder="<?php _e('placeholder_spec_ar'); ?>">
          </div>
          <div class="col-md-8">
            <label class="luxury-form-label"><?php _e('dentist_email_input'); ?> <span class="text-danger">*</span></label>
            <input type="email" name="dentist_email" class="form-control luxury-input" required>
          </div>
          <div class="col-md-4">
            <label class="luxury-form-label"><?php _e('dentist_age_input'); ?> <span class="text-danger">*</span></label>
            <input type="number" name="dentist_age" class="form-control luxury-input" min="18" max="100" required>
          </div>
          <div class="col-12">
            <label class="luxury-form-label"><?php _e('dentist_bio_en'); ?></label>
            <textarea name="dentist_brief_en" rows="3" class="form-control luxury-input" dir="ltr" placeholder="<?php _e('placeholder_bio_en'); ?>"></textarea>
          </div>
          <div class="col-12">
            <label class="luxury-form-label"><?php _e('dentist_bio_ar'); ?></label>
            <textarea name="dentist_brief_ar" rows="3" class="form-control luxury-input" dir="rtl" placeholder="<?php _e('placeholder_bio_ar'); ?>"></textarea>
          </div>
          <div class="col-12">
            <label class="luxury-form-label"><?php _e('dentist_photo_label'); ?></label>
            <input type="file" name="image" class="form-control luxury-input" accept="image/jpeg,image/png">
          </div>
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
