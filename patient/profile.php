<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/upload.php';

$portalUser = auth_require_patient();
$pageTitle  = __('portal_profile');
$activeNav  = 'profile';
$uid        = (int)$portalUser['id'];
$pdo        = db();

$stmtPat = $pdo->prepare("SELECT * FROM patients WHERE user_id = :uid LIMIT 1");
$stmtPat->execute([':uid' => $uid]);
$patient = $stmtPat->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone_number'] ?? '');
    $age      = $_POST['patient_age'] ?? '';
    $gender   = trim($_POST['patient_gender'] ?? '1');

    if (!validate_name($username)) {
        $errors[] = __('err_invalid_name');
    }
    if (!validate_email($email)) {
        $errors[] = __('err_invalid_email');
    }
    if ($phone !== '' && !validate_phone($phone)) {
        $errors[] = __('err_invalid_phone');
    }
    if ($age !== '' && !validate_age($age)) {
        $errors[] = __('err_invalid_age');
    }

    if (empty($errors)) {
        $stmtDup = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :uid LIMIT 1");
        $stmtDup->execute([':email' => $email, ':uid' => $uid]);
        if ($stmtDup->fetch()) {
            $errors[] = __('err_email_exists');
        }
    }

    $newImage = $portalUser['image'];
    if (empty($errors) && !empty($_FILES['avatar']['name'])) {
        $up = handle_secure_image_upload($_FILES['avatar'], DENTAL_UPLOADS_DIR . DIRECTORY_SEPARATOR . 'avatars');
        if ($up['success']) {
            $newImage = $up['filename'];
        } else {
            $errors[] = __('err_upload_failed');
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $stmtUpdUser = $pdo->prepare("UPDATE users SET username = :uname, email = :email, image = :img WHERE id = :uid");
            $stmtUpdUser->execute([
                ':uname' => $username,
                ':email' => $email,
                ':img'   => $newImage,
                ':uid'   => $uid
            ]);

            if ($patient) {
                $stmtUpdPat = $pdo->prepare(
                    "UPDATE patients SET patient_name = :name, phone_number = :phone, patient_age = :age, patient_gender = :gender
                     WHERE patient_id = :pid"
                );
                $stmtUpdPat->execute([
                    ':name'   => $username,
                    ':phone'  => $phone !== '' ? $phone : $patient['phone_number'],
                    ':age'    => $age !== '' ? (int)$age : (int)$patient['patient_age'],
                    ':gender' => in_array($gender, ['1', '2'], true) ? $gender : $patient['patient_gender'],
                    ':pid'    => (int)$patient['patient_id']
                ]);
            } elseif ($phone !== '' && $age !== '') {
                $stmtCheckEmail = $pdo->prepare("SELECT patient_id FROM patients WHERE patient_email = :email LIMIT 1");
                $stmtCheckEmail->execute([':email' => $email]);
                if (!$stmtCheckEmail->fetch()) {
                    $stmtInsPat = $pdo->prepare(
                        "INSERT INTO patients (user_id, patient_name, patient_age, patient_email, ppassword, phone_number, patient_gender)
                         VALUES (:uid, :name, :age, :email, NULL, :phone, :gender)"
                    );
                    $stmtInsPat->execute([
                        ':uid'    => $uid,
                        ':name'   => $username,
                        ':age'    => (int)$age,
                        ':email'  => $email,
                        ':phone'  => $phone,
                        ':gender' => in_array($gender, ['1', '2'], true) ? $gender : '1'
                    ]);
                }
            }

            $pdo->commit();
            flash_set('success', lang_raw('msg_profile_updated'));
            header('Location: ' . DENTAL_BASE_URL . '/patient/profile.php');
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[DENTAL PROFILE UPDATE ERROR] ' . $e->getMessage());
            $errors[] = __('err_required_fields');
        }
    }
}

require_once __DIR__ . '/../templates/patient/layout_start.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger border-0 shadow-sm mb-4">
        <?php foreach ($errors as $err): ?>
          <div><?php echo e($err); ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data" class="luxury-card p-4 p-md-5" data-prevent-double-submit="true">
      <?php echo csrf_field(); ?>
      <div class="d-flex align-items-center gap-4 mb-4 pb-4 border-bottom">
        <img src="<?php echo e(get_user_avatar_url($portalUser['image'])); ?>" alt="Avatar" class="avatar-circle" width="84" height="84">
        <div>
          <label for="avatarFile" class="luxury-form-label d-block"><?php _e('field_avatar'); ?></label>
          <input type="file" id="avatarFile" name="avatar" class="form-control form-control-sm luxury-input" accept="image/jpeg,image/png">
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label for="profName" class="luxury-form-label"><?php _e('field_username'); ?></label>
          <input type="text" id="profName" name="username" class="form-control luxury-input" value="<?php echo e($_POST['username'] ?? $portalUser['username']); ?>" required>
        </div>
        <div class="col-md-6">
          <label for="profEmail" class="luxury-form-label"><?php _e('field_email'); ?></label>
          <input type="email" id="profEmail" name="email" class="form-control luxury-input" value="<?php echo e($_POST['email'] ?? $portalUser['email']); ?>" required>
        </div>
        <div class="col-md-4">
          <label for="profPhone" class="luxury-form-label"><?php _e('patient_phone'); ?></label>
          <input type="text" id="profPhone" name="phone_number" class="form-control luxury-input" value="<?php echo e($_POST['phone_number'] ?? ($patient['phone_number'] ?? '')); ?>">
        </div>
        <div class="col-md-4">
          <label for="profAge" class="luxury-form-label"><?php _e('patient_age'); ?></label>
          <input type="number" id="profAge" name="patient_age" class="form-control luxury-input" min="1" max="120" value="<?php echo e($_POST['patient_age'] ?? ($patient['patient_age'] ?? '')); ?>">
        </div>
        <div class="col-md-4">
          <label for="profGender" class="luxury-form-label"><?php _e('patient_gender'); ?></label>
          <select id="profGender" name="patient_gender" class="form-select luxury-select">
            <?php $g = $_POST['patient_gender'] ?? ($patient['patient_gender'] ?? '1'); ?>
            <option value="1" <?php echo ($g === '1') ? 'selected' : ''; ?>><?php _e('gender_male'); ?></option>
            <option value="2" <?php echo ($g === '2') ? 'selected' : ''; ?>><?php _e('gender_female'); ?></option>
          </select>
        </div>
      </div>

      <button type="submit" class="btn btn-luxury-primary">
        <i class="bi bi-check2 me-1"></i> <?php _e('btn_save_changes'); ?>
      </button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../templates/patient/layout_end.php'; ?>
