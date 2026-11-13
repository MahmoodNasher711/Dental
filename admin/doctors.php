<?php
require_once __DIR__ . '/../includes/bootstrap.php';
auth_require_admin();
header('Location: ' . DENTAL_BASE_URL . '/admin/dentists.php', true, 301);
exit;
