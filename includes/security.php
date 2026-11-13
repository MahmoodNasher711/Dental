<?php
/**
 * DENTAL Management System — Security Utilities
 * Output escaping, CSRF defense, security headers, rate limiting, and audit logging.
 */

require_once __DIR__ . '/config.php';

/**
 * Context-safe HTML entity escaping.
 */
function e(mixed $value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Generates or retrieves the current session's anti-CSRF token.
 */
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Outputs a hidden CSRF token input field for forms.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Validates the submitted CSRF token on state-changing requests.
 */
function csrf_verify(): void
{
    $submitted = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if ($sessionToken === '' || $submitted === '' || !hash_equals($sessionToken, (string)$submitted)) {
        http_response_code(403);
        error_log('[DENTAL CSRF FAILURE] IP: ' . get_client_ip() . ' URI: ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
        $msg = function_exists('lang_raw')
            ? lang_raw('err_csrf_failed')
            : 'Security verification failed (Invalid or missing CSRF token). Please refresh the page and try again.';
        exit($msg);
    }
}

/**
 * Sends defensive HTTP security headers.
 */
function send_security_headers(): void
{
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }

    header_remove('X-Powered-By');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; img-src 'self' data: https:; connect-src 'self'; frame-ancestors 'self'; form-action 'self';");
}

/**
 * Retrieves the client IP address safely.
 */
function get_client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Checks login rate limiting against the database.
 * Returns ['allowed' => bool, 'remaining_minutes' => int].
 */
function rate_limit_check(string $email): array
{
    $ip = get_client_ip();
    try {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS attempts, MAX(attempted_at) AS last_attempt
             FROM login_attempts
             WHERE (ip_address = :ip OR email = :email)
               AND attempted_at >= (NOW() - INTERVAL :minutes MINUTE)"
        );
        $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindValue(':email', strtolower(trim($email)), PDO::PARAM_STR);
        $stmt->bindValue(':minutes', LOGIN_LOCKOUT_MINUTES, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row && (int)$row['attempts'] >= MAX_LOGIN_ATTEMPTS) {
            return ['allowed' => false, 'remaining_minutes' => LOGIN_LOCKOUT_MINUTES];
        }
    } catch (PDOException $e) {
        // Gracefully degrade if table not yet migrated in early phase
        error_log('[DENTAL RATE LIMIT CHECK] ' . $e->getMessage());
    }

    return ['allowed' => true, 'remaining_minutes' => 0];
}

/**
 * Records a failed login attempt for rate limiting.
 */
function rate_limit_record_failure(string $email): void
{
    try {
        $pdo = db();
        $stmt = $pdo->prepare(
            "INSERT INTO login_attempts (ip_address, email, attempted_at) VALUES (:ip, :email, NOW())"
        );
        $stmt->execute([
            ':ip'    => get_client_ip(),
            ':email' => strtolower(trim($email))
        ]);
    } catch (PDOException $e) {
        error_log('[DENTAL RATE LIMIT RECORD] ' . $e->getMessage());
    }
}

/**
 * Clears failed login attempts after a successful login.
 */
function rate_limit_clear(string $email): void
{
    try {
        $pdo = db();
        $stmt = $pdo->prepare(
            "DELETE FROM login_attempts WHERE ip_address = :ip OR email = :email"
        );
        $stmt->execute([
            ':ip'    => get_client_ip(),
            ':email' => strtolower(trim($email))
        ]);
    } catch (PDOException $e) {
        error_log('[DENTAL RATE LIMIT CLEAR] ' . $e->getMessage());
    }
}

/**
 * Records a sensitive security or administrative audit event.
 */
function audit_log(string $action, string $details = '', ?int $userId = null): void
{
    if ($userId === null && isset($_SESSION['user_id'])) {
        $userId = (int)$_SESSION['user_id'];
    }
    try {
        $pdo = db();
        $stmt = $pdo->prepare(
            "INSERT INTO audit_logs (user_id, action, details, ip_address, created_at)
             VALUES (:uid, :action, :details, :ip, NOW())"
        );
        $stmt->execute([
            ':uid'     => $userId,
            ':action'  => substr($action, 0, 100),
            ':details' => substr($details, 0, 1000),
            ':ip'      => get_client_ip()
        ]);
    } catch (PDOException $e) {
        error_log("[DENTAL AUDIT LOG] $action | $details | " . $e->getMessage());
    }
}

/**
 * Stores a flash notification in the session.
 */
function flash_set(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['flash_messages'][] = [
            'type'    => $type, // 'success', 'danger', 'warning', 'info'
            'message' => $message
        ];
    }
}

/**
 * Retrieves and clears flash notifications from the session.
 */
function flash_get(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['flash_messages'])) {
        return [];
    }
    $messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
    return $messages;
}
