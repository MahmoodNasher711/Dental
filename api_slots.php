<?php
/**
 * DENTAL — Real-Time Appointment Slot Availability Endpoint (JSON)
 */
require_once __DIR__ . '/modules/appointments/booking_service.php';

header('Content-Type: application/json; charset=UTF-8');

$authUser = auth_user();
if ($authUser === null) {
    http_response_code(401);
    echo json_encode([
        'error'   => 'auth_required',
        'slots'   => [],
        'message' => lang_raw('booking_auth_required')
    ]);
    exit;
}

$dentistId = filter_input(INPUT_GET, 'dentist_id', FILTER_VALIDATE_INT) ?: 0;
$serviceId = filter_input(INPUT_GET, 'service_id', FILTER_VALIDATE_INT) ?: 0;
$date      = trim((string)($_GET['date'] ?? ''));

if ($dentistId <= 0 || !validate_date($date)) {
    echo json_encode(['slots' => [], 'message' => lang_raw('select_slot_prompt')]);
    exit;
}

if ($date < date('Y-m-d')) {
    echo json_encode(['slots' => [], 'message' => lang_raw('err_past_date')]);
    exit;
}

$slots = booking_get_available_slots($dentistId, $date, $serviceId);
$anyAvailable = false;
foreach ($slots as $s) {
    if ($s['available']) {
        $anyAvailable = true;
        break;
    }
}

echo json_encode([
    'slots'   => $slots,
    'message' => $anyAvailable ? '' : lang_raw('no_slots_available')
]);
