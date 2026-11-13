<?php
/**
 * DENTAL Management System — Bilingual Localization Engine (EN / AR)
 * Default language: English (en, LTR). Full Arabic (ar, RTL) support.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

/**
 * Resolves and persists the active application language ('en' or 'ar').
 */
function init_localization(): string
{
    $allowed = ['en', 'ar'];

    if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed, true)) {
        $lang = $_GET['lang'];
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['lang'] = $lang;
        }
        if (PHP_SAPI !== 'cli' && !headers_sent()) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            setcookie('dental_lang', $lang, [
                'expires'  => time() + (86400 * 30),
                'path'     => '/',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    } elseif (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['lang']) && in_array($_SESSION['lang'], $allowed, true)) {
        $lang = $_SESSION['lang'];
    } elseif (isset($_COOKIE['dental_lang']) && in_array($_COOKIE['dental_lang'], $allowed, true)) {
        $lang = $_COOKIE['dental_lang'];
    } else {
        $lang = 'en'; // Approved default language: English
    }

    return $lang;
}

$CURRENT_LANG = init_localization();
$CURRENT_DIR  = ($CURRENT_LANG === 'ar') ? 'rtl' : 'ltr';

$langFile = DENTAL_LANGUAGES . DIRECTORY_SEPARATOR . $CURRENT_LANG . '.php';
$DENTAL_TRANSLATIONS = is_file($langFile) ? require $langFile : [];

/**
 * Returns a raw translated string (not HTML-escaped). Use __() for safe HTML output.
 */
function lang_raw(string $key, array $replacements = []): string
{
    global $DENTAL_TRANSLATIONS;
    $text = $DENTAL_TRANSLATIONS[$key] ?? $key;
    foreach ($replacements as $placeholder => $value) {
        $text = str_replace(':' . $placeholder, (string)$value, $text);
    }
    return $text;
}

/**
 * Returns an HTML-escaped translated string.
 */
function __(string $key, array $replacements = []): string
{
    return e(lang_raw($key, $replacements));
}

/**
 * Echoes an HTML-escaped translated string.
 */
function _e(string $key, array $replacements = []): void
{
    echo __($key, $replacements);
}

/**
 * Returns the HTML URL to switch to the alternate language while staying on the current page.
 */
function lang_switch_url(string $targetLang): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? (DENTAL_BASE_URL . '/index.php');
    $parts = parse_url($uri);
    $path = $parts['path'] ?? (DENTAL_BASE_URL . '/index.php');
    $query = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
    }
    $query['lang'] = $targetLang;
    return $path . '?' . http_build_query($query);
}

/**
 * Translates internal DB appointment status cleanly while keeping DB keys untouched.
 */
function translate_status(?string $status): string
{
    $normalized = strtolower(trim((string)($status ?? 'Pending')));
    if ($normalized === '') {
        $normalized = 'pending';
    }
    return __('status_' . $normalized);
}

/**
 * Maps internal DB status to CSS badge class.
 */
function status_badge_class(?string $status): string
{
    return match (strtolower(trim((string)($status ?? 'Pending')))) {
        'confirmed' => 'status-confirmed',
        'completed' => 'status-completed',
        'cancelled', 'rejected' => 'status-cancelled',
        default     => 'status-pending',
    };
}

/**
 * Translates an internal user role ('admin' / 'user') cleanly for UI display.
 */
function translate_role(?string $role): string
{
    return strtolower(trim((string)$role)) === 'admin' ? __('role_admin') : __('role_user');
}

/**
 * Formats a date string cleanly for display in the active locale.
 */
function format_localized_date(?string $dateStr): string
{
    global $CURRENT_LANG;
    if (empty($dateStr)) {
        return '-';
    }
    $ts = strtotime($dateStr);
    if ($ts === false) {
        return e($dateStr);
    }
    if ($CURRENT_LANG === 'ar') {
        $monthsAr = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];
        $day = date('j', $ts);
        $month = $monthsAr[(int)date('n', $ts)];
        $year = date('Y', $ts);
        return "$day $month $year";
    }
    return date('M j, Y', $ts);
}

/**
 * Returns localized service name based on active application language.
 */
function service_name(mixed $service): string
{
    global $CURRENT_LANG;
    if (!$service) {
        return '';
    }
    $row = (array)$service;
    if ($CURRENT_LANG === 'ar') {
        if (!empty($row['service_name_ar'])) {
            return (string)$row['service_name_ar'];
        }
        return (string)($row['service_name_en'] ?? $row['service_name'] ?? '');
    }
    return (string)($row['service_name_en'] ?? $row['service_name'] ?? $row['service_name_ar'] ?? '');
}

/**
 * Returns localized service description based on active application language.
 */
function service_description(mixed $service): string
{
    global $CURRENT_LANG;
    if (!$service) {
        return '';
    }
    $row = (array)$service;
    if ($CURRENT_LANG === 'ar') {
        if (!empty($row['service_description_ar'])) {
            return (string)$row['service_description_ar'];
        }
        return (string)($row['service_description_en'] ?? $row['description'] ?? '');
    }
    return (string)($row['service_description_en'] ?? $row['description'] ?? $row['service_description_ar'] ?? '');
}

/**
 * Returns localized dentist name.
 */
function dentist_name(mixed $dentist): string
{
    global $CURRENT_LANG;
    if (!$dentist) {
        return '';
    }
    $row = (array)$dentist;
    if ($CURRENT_LANG === 'ar' && !empty($row['dentist_name_ar'])) {
        return (string)$row['dentist_name_ar'];
    }
    return (string)($row['dentist_name'] ?? '');
}

/**
 * Returns localized dentist specialization.
 */
function dentist_specialization(mixed $dentist): string
{
    global $CURRENT_LANG;
    if (!$dentist) {
        return '';
    }
    $row = (array)$dentist;
    if ($CURRENT_LANG === 'ar' && !empty($row['specialization_ar'])) {
        return (string)$row['specialization_ar'];
    }
    return (string)($row['specialization'] ?? '');
}

/**
 * Returns localized dentist biography.
 */
function dentist_bio(mixed $dentist): string
{
    global $CURRENT_LANG;
    if (!$dentist) {
        return '';
    }
    $row = (array)$dentist;
    if ($CURRENT_LANG === 'ar' && !empty($row['dentist_brief_ar'])) {
        return (string)$row['dentist_brief_ar'];
    }
    return (string)($row['dentist_brief'] ?? '');
}

/**
 * Returns localized About Us text.
 */
function about_us_content(mixed $about): string
{
    global $CURRENT_LANG;
    if (!$about) {
        return '';
    }
    $row = (array)$about;
    if ($CURRENT_LANG === 'ar' && !empty($row['about_text_ar'])) {
        return (string)$row['about_text_ar'];
    }
    return (string)($row['about_text'] ?? '');
}

/**
 * Returns localized clinic address from settings array.
 */
function clinic_address(array $settings): string
{
    global $CURRENT_LANG;
    $key = ($CURRENT_LANG === 'ar') ? 'clinic_address_ar' : 'clinic_address_en';
    if (!empty($settings[$key])) {
        return (string)$settings[$key];
    }
    $fallbackKey = ($CURRENT_LANG === 'ar') ? 'clinic_address_en' : 'clinic_address_ar';
    return (string)($settings[$fallbackKey] ?? '');
}

/**
 * Returns localized operating hours from settings array.
 */
function clinic_hours(array $settings): string
{
    global $CURRENT_LANG;
    $key = ($CURRENT_LANG === 'ar') ? 'operating_hours_ar' : 'operating_hours_en';
    if (!empty($settings[$key])) {
        return (string)$settings[$key];
    }
    $fallbackKey = ($CURRENT_LANG === 'ar') ? 'operating_hours_en' : 'operating_hours_ar';
    return (string)($settings[$fallbackKey] ?? '');
}

