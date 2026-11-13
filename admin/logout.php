<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Location: ' . DENTAL_BASE_URL . '/admin/index.php');
    exit;
}

csrf_verify();
auth_logout();
header('Location: ' . DENTAL_BASE_URL . '/login.php');
exit;
