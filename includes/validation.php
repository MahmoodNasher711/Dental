<?php
/**
 * DENTAL Management System — Input Validation Helpers
 */

/**
 * Validates an email address format.
 */
function validate_email(string $email): bool
{
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false && mb_strlen($email) <= 255;
}

/**
 * Validates a person's name (supports Arabic, English, spaces, apostrophes, hyphens).
 */
function validate_name(string $name): bool
{
    $trimmed = trim($name);
    if ($trimmed === '' || mb_strlen($trimmed) < 2 || mb_strlen($trimmed) > 100) {
        return false;
    }
    return preg_match('/^[\p{L}\s\'\-\.]+$/u', $trimmed) === 1;
}

/**
 * Validates a phone number string (preserves leading zeros and international + prefixes).
 */
function validate_phone(string $phone): bool
{
    $trimmed = trim($phone);
    // Allow optional +, digits, spaces, dashes, parentheses; minimum 7 digits
    if (!preg_match('/^\+?[0-9\s\-\(\)]{7,20}$/', $trimmed)) {
        return false;
    }
    $digitsOnly = preg_replace('/\D/', '', $trimmed);
    return strlen($digitsOnly) >= 7 && strlen($digitsOnly) <= 15;
}

/**
 * Validates an integer age within reasonable bounds (1 to 120).
 */
function validate_age(mixed $age): bool
{
    if (!is_numeric($age)) {
        return false;
    }
    $intAge = (int)$age;
    return (string)$intAge === trim((string)$age) && $intAge >= 1 && $intAge <= 120;
}

/**
 * Validates a date string in YYYY-MM-DD format.
 */
function validate_date(string $date): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', trim($date));
    return $dt !== false && $dt->format('Y-m-d') === trim($date);
}

/**
 * Validates a time string in HH:MM or HH:MM:SS format.
 */
function validate_time(string $time): bool
{
    $trimmed = trim($time);
    return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $trimmed) === 1;
}

/**
 * Normalizes a time string to HH:MM:SS.
 */
function normalize_time(string $time): string
{
    $trimmed = trim($time);
    if (strlen($trimmed) === 5) {
        return $trimmed . ':00';
    }
    return $trimmed;
}

/**
 * Validates password strength (minimum 8 characters).
 */
function validate_password(string $password): bool
{
    return mb_strlen($password) >= 8 && mb_strlen($password) <= 128;
}
