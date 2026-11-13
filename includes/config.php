<?php
/**
 * DENTAL Management System — Core Configuration
 * Centralized environment, database, and security configuration.
 */

if (defined('DENTAL_CONFIG_LOADED')) {
    return;
}
define('DENTAL_CONFIG_LOADED', true);

// Error Handling: Never expose internal errors or stack traces to browser
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Define application paths
define('DENTAL_ROOT', dirname(__DIR__));
define('DENTAL_INCLUDES', DENTAL_ROOT . DIRECTORY_SEPARATOR . 'includes');
define('DENTAL_LANGUAGES', DENTAL_ROOT . DIRECTORY_SEPARATOR . 'languages');
define('DENTAL_UPLOADS_DIR', DENTAL_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads');
define('DENTAL_DOCTORS_IMG_DIR', DENTAL_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'doctors');

// Base URL auto-detection
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseUrl = '/Dental';
if (preg_match('#^(/[^/]+)/#', $scriptName, $matches)) {
    // Keep /Dental when hosted in WAMP subfolder
    if (strtolower($matches[1]) === '/dental') {
        $baseUrl = $matches[1];
    }
}
define('DENTAL_BASE_URL', $baseUrl);

// Optional untracked local configuration override (recommended for real credentials)
$localConfig = __DIR__ . DIRECTORY_SEPARATOR . 'config.local.php';
if (is_file($localConfig)) {
    require_once $localConfig;
}

// Database Configuration (override via includes/config.local.php outside source control)
defined('DB_HOST')    || define('DB_HOST', 'localhost');
defined('DB_NAME')    || define('DB_NAME', 'appointment_system');
defined('DB_USER')    || define('DB_USER', 'root');
defined('DB_PASS')    || define('DB_PASS', '');
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');

// Security & Session Settings
define('SESSION_IDLE_TIMEOUT', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);
define('MAX_UPLOAD_BYTES', 2097152); // 2 MB

// Timezone configuration
date_default_timezone_set('Asia/Riyadh');
