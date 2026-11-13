<?php
/**
 * DENTAL Management System — Core Bootstrap Loader
 * Ensures deterministic load order across all public, patient, and admin endpoints.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/localization.php';

/**
 * Fetches all clinic settings as a key-value associative array.
 */
function get_clinic_settings(): array
{
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }

    $settings = [];
    try {
        $rows = db()->query("SELECT setting_key, setting_value FROM clinic_settings")->fetchAll();
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (PDOException $e) {
        error_log('[DENTAL SETTINGS] ' . $e->getMessage());
    }
    return $settings;
}
