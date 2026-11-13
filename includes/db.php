<?php
/**
 * DENTAL Management System — Centralized Database Access (PDO)
 */

require_once __DIR__ . '/config.php';

/**
 * Returns the singleton PDO connection instance.
 *
 * @return PDO
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dbName = (PHP_SAPI === 'cli' && getenv('DENTAL_TEST_DB'))
            ? getenv('DENTAL_TEST_DB')
            : DB_NAME;

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            $dbName,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci"
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('[DENTAL DB CONNECTION ERROR] ' . $e->getMessage());
            http_response_code(503);
            exit('Service temporarily unavailable. Please try again later.');
        }
    }

    return $pdo;
}

// Initialize global handles for clean modular procedural usage
$conn = db();
$pdo = $conn;
