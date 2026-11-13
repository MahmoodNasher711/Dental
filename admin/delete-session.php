<?php
require_once __DIR__ . '/../includes/auth.php';
auth_require_admin();
header('Location: index.php');
exit;
