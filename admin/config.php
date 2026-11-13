<?php
/**
 * Admin config bridge -> forwards to centralized PDO & Auth modules.
 */
require_once __DIR__ . '/../includes/auth.php';
$adminUser = auth_require_admin();
