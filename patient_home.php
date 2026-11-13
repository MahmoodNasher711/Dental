<?php
require_once __DIR__ . '/includes/bootstrap.php';
auth_require_patient();
header('Location: ' . DENTAL_BASE_URL . '/patient/index.php', true, 301);
exit;
