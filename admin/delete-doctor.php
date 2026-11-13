<?php
require_once __DIR__ . '/../includes/auth.php';
$admin = auth_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Location: doctors.php');
    exit;
}

csrf_verify();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id && $id > 0) {
    $stmt = $conn->prepare("DELETE FROM dentists WHERE dentist_id = :id");
    $stmt->execute([':id' => $id]);
    audit_log('DELETE_DENTIST', "Deleted dentist ID $id", (int)$admin['id']);
}

header('Location: doctors.php');
exit;
