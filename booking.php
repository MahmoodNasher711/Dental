<?php
require_once __DIR__ . '/modules/appointments/booking_service.php';

// Mandatory authentication: Guests are redirected to login with intended destination preserved
$authUser = auth_require_booking_login();

$pageTitle = __('booking_page_title');
$activeNav = 'booking';

$prefillPatient = [
    'patient_name'   => $authUser['username'] ?? '',
    'patient_email'  => $authUser['email'] ?? '',
    'phone_number'   => '',
    'patient_age'    => '',
    'patient_gender' => '1',
];

$stmtPat = db()->prepare("SELECT * FROM patients WHERE user_id = :uid OR (patient_email = :email AND user_id IS NULL) LIMIT 1");
$stmtPat->execute([':uid' => (int)$authUser['id'], ':email' => $authUser['email']]);
$patRow = $stmtPat->fetch();
if ($patRow) {
    $prefillPatient = $patRow;
}

$services = db()->query("SELECT * FROM services WHERE is_active = 1 ORDER BY service_id ASC")->fetchAll();
$dentists = db()->query("SELECT * FROM dentists WHERE is_active = 1 ORDER BY dentist_id ASC")->fetchAll();

$selectedService = filter_input(INPUT_GET, 'service_id', FILTER_VALIDATE_INT) ?: ($services[0]['service_id'] ?? 1);
$selectedDentist = filter_input(INPUT_GET, 'dentist_id', FILTER_VALIDATE_INT) ?: ($dentists[0]['dentist_id'] ?? 1);
$selectedDate    = trim((string)($_POST['appointment_date'] ?? date('Y-m-d', strtotime('+1 day'))));

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $selectedService = (int)($_POST['service_id'] ?? $selectedService);
    $selectedDentist = (int)($_POST['dentist_id'] ?? $selectedDentist);

    $submittedNonce = (string)($_POST['booking_nonce'] ?? '');
    $sessionNonce   = (string)($_SESSION['booking_nonce'] ?? '');
    unset($_SESSION['booking_nonce']);

    if ($sessionNonce === '' || $submittedNonce === '' || !hash_equals($sessionNonce, $submittedNonce)) {
        $errorMsg = __('booking_nonce_error');
    } else {
        $bookingInput = $_POST;
        unset($bookingInput['patient_id'], $bookingInput['user_id']);
        $bookingInput['patient_email'] = $authUser['email'];

        $result = booking_create_appointment($bookingInput, $authUser);
        if ($result['success']) {
            $_SESSION['last_booking_ref'] = $result['appointment_id'];
            header('Location: ' . DENTAL_BASE_URL . '/booking_successfully.php?ref=' . (int)$result['appointment_id']);
            exit;
        }
        $errorMsg = __($result['error_key']);
    }
}

$_SESSION['booking_nonce'] = bin2hex(random_bytes(16));
$initialSlots = booking_get_available_slots((int)$selectedDentist, $selectedDate, (int)$selectedService);

require_once __DIR__ . '/templates/public/header.php';
?>

<section class="py-5 bg-emerald-deep">
  <div class="container py-3">
    <span class="eyebrow-label" style="color: var(--dental-gold-soft);"><?php _e('booking_eyebrow'); ?></span>
    <h1 class="display-5 text-white mb-1"><?php _e('booking_title'); ?></h1>
    <p class="mb-0 small" style="color: var(--dental-gold-soft);"><?php _e('booking_subtitle'); ?></p>
  </div>
</section>

<section class="section-pad bg-ivory">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <?php if ($errorMsg !== ''): ?>
          <div class="alert alert-danger border-0 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo e($errorMsg); ?>
          </div>
        <?php endif; ?>

        <form method="post" action="" class="luxury-card p-4 p-md-5" data-prevent-double-submit="true">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="booking_nonce" value="<?php echo e($_SESSION['booking_nonce']); ?>">

          <!-- Step 1 & 2 -->
          <div class="row g-4 mb-4 pb-4 border-bottom" style="border-color: var(--dental-border-subtle) !important;">
            <div class="col-md-6">
              <label for="bookingService" class="luxury-form-label"><?php _e('step_1_service'); ?></label>
              <select id="bookingService" name="service_id" class="form-select luxury-select" required>
                <?php foreach ($services as $srv): ?>
                  <option value="<?php echo (int)$srv['service_id']; ?>" <?php echo ((int)$selectedService === (int)$srv['service_id']) ? 'selected' : ''; ?>>
                    <?php echo e(service_name($srv) . ' — ' . $srv['service_price'] . ' (' . $srv['duration_minutes'] . ' ' . lang_raw('minutes') . ')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="bookingDentist" class="luxury-form-label"><?php _e('step_2_dentist'); ?></label>
              <select id="bookingDentist" name="dentist_id" class="form-select luxury-select" required>
                <?php foreach ($dentists as $doc): ?>
                  <option value="<?php echo (int)$doc['dentist_id']; ?>" <?php echo ((int)$selectedDentist === (int)$doc['dentist_id']) ? 'selected' : ''; ?>>
                    <?php echo e(dentist_name($doc) . (!empty(dentist_specialization($doc)) ? ' (' . dentist_specialization($doc) . ')' : '')); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="mb-4 pb-4 border-bottom" style="border-color: var(--dental-border-subtle) !important;">
            <div class="row g-4">
              <div class="col-md-4">
                <label for="bookingDate" class="luxury-form-label"><?php _e('select_date'); ?></label>
                <input type="date" id="bookingDate" name="appointment_date" class="form-control luxury-input" min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($selectedDate); ?>" required>
              </div>
              <div class="col-md-8">
                <label class="luxury-form-label d-block"><?php _e('available_slots'); ?></label>
                <div id="slotGridContainer"
                     data-loading-text="<?php _e('booking_slots_loading'); ?>"
                     data-empty-text="<?php _e('no_slots_available'); ?>"
                     data-error-text="<?php _e('booking_slots_error'); ?>">
                  <?php if (empty($initialSlots)): ?>
                    <div class="alert alert-warning py-2 px-3 small mb-0"><?php _e('no_slots_available'); ?></div>
                  <?php else: ?>
                    <div class="slot-grid">
                      <?php foreach ($initialSlots as $idx => $slot): ?>
                        <div>
                          <input type="radio" class="slot-radio" name="appointment_time" id="slot_init_<?php echo $idx; ?>" value="<?php echo e($slot['time']); ?>" <?php echo $slot['available'] ? '' : 'disabled'; ?> <?php echo (($_POST['appointment_time'] ?? '') === $slot['time']) ? 'checked' : ''; ?> required>
                          <label class="slot-chip <?php echo $slot['available'] ? '' : 'slot-disabled'; ?>" for="slot_init_<?php echo $idx; ?>">
                            <?php echo e($slot['label']); ?>
                          </label>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 4 -->
          <h3 class="h5 text-emerald mb-3"><?php _e('step_4_patient'); ?></h3>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label for="patName" class="luxury-form-label"><?php _e('patient_full_name'); ?></label>
              <input type="text" id="patName" name="patient_name" class="form-control luxury-input" value="<?php echo e($_POST['patient_name'] ?? $prefillPatient['patient_name']); ?>" required>
            </div>
            <div class="col-md-6">
              <label for="patEmail" class="luxury-form-label"><?php _e('patient_email'); ?></label>
              <input type="email" id="patEmail" class="form-control luxury-input bg-light" value="<?php echo e($authUser['email']); ?>" readonly disabled>
            </div>
            <div class="col-md-4">
              <label for="patPhone" class="luxury-form-label"><?php _e('patient_phone'); ?></label>
              <input type="text" id="patPhone" name="patient_phone" class="form-control luxury-input" placeholder="+..." value="<?php echo e($_POST['patient_phone'] ?? $prefillPatient['phone_number']); ?>" required>
            </div>
            <div class="col-md-4">
              <label for="patAge" class="luxury-form-label"><?php _e('patient_age'); ?></label>
              <input type="number" id="patAge" name="patient_age" class="form-control luxury-input" min="1" max="120" value="<?php echo e($_POST['patient_age'] ?? $prefillPatient['patient_age']); ?>" required>
            </div>
            <div class="col-md-4">
              <label for="patGender" class="luxury-form-label"><?php _e('patient_gender'); ?></label>
              <select id="patGender" name="patient_gender" class="form-select luxury-select" required>
                <?php $gVal = $_POST['patient_gender'] ?? $prefillPatient['patient_gender']; ?>
                <option value="1" <?php echo ($gVal === '1' || $gVal === 'Male') ? 'selected' : ''; ?>><?php _e('gender_male'); ?></option>
                <option value="2" <?php echo ($gVal === '2' || $gVal === 'Female') ? 'selected' : ''; ?>><?php _e('gender_female'); ?></option>
              </select>
            </div>
            <div class="col-12">
              <label for="bookingNotes" class="luxury-form-label"><?php _e('booking_notes'); ?></label>
              <textarea id="bookingNotes" name="notes" rows="2" class="form-control luxury-input" maxlength="500"><?php echo e($_POST['notes'] ?? ''); ?></textarea>
            </div>
          </div>

          <button type="submit" class="btn btn-luxury-primary w-100 py-3">
            <i class="bi bi-calendar2-check me-2"></i> <?php _e('confirm_booking'); ?>
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/templates/public/footer.php'; ?>
