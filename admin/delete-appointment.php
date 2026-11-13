<?php
require_once __DIR__ . '/../includes/auth.php';
$admin = auth_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Location: appointments.php');
    exit;
}

csrf_verify();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id && $id > 0) {
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = :id");
    $stmt->execute([':id' => $id]);
    audit_log('DELETE_APPOINTMENT', "Deleted appointment ID $id", (int)$admin['id']);
}

header('Location: appointment.php');
exit;
