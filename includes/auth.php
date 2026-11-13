<?php
/**
 * DENTAL Management System — Authentication & RBAC Guards
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

/**
 * Initializes a hardened PHP session and enforces idle timeout.
 */
function init_secure_session(): void
{
    if (PHP_SAPI === 'cli') {
        return;
    }

    send_security_headers();

    if (session_status() === PHP_SESSION_NONE) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                   || (($_SERVER['SERVER_PORT'] ?? 80) == 443);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
    }

    // Idle timeout enforcement
    if (isset($_SESSION['user_id'], $_SESSION['last_activity'])) {
        if ((time() - (int)$_SESSION['last_activity']) > SESSION_IDLE_TIMEOUT) {
            auth_logout();
            return;
        }
    }

    if (isset($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Establishes a verified user session and prevents session fixation.
 */
function auth_login(array $user): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    $_SESSION['user_id']       = (int)$user['id'];
    $_SESSION['username']      = $user['username'];
    $_SESSION['email']         = $user['email'];
    $_SESSION['role']          = $user['role'];
    $_SESSION['last_activity'] = time();
    unset($_SESSION['csrf_token']); // Force fresh CSRF token post-auth

    audit_log('LOGIN_SUCCESS', 'Authenticated user: ' . $user['email'], (int)$user['id']);
}

/**
 * Destroys the active user session and expires its cookie securely.
 */
function auth_logout(): void
{
    if (isset($_SESSION['user_id'])) {
        audit_log('LOGOUT', 'User session ended', (int)$_SESSION['user_id']);
    }

    $_SESSION = [];

    if (PHP_SAPI !== 'cli' && ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax'
            ]
        );
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/**
 * Retrieves the currently authenticated active user from the database.
 * Returns null if not authenticated or account deactivated.
 */
function auth_user(): ?array
{
    static $cachedUser = false;

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    if ($cachedUser !== false) {
        return $cachedUser;
    }

    try {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT id, username, email, role, image, add_date, CAST(active AS UNSIGNED) AS is_active
             FROM users
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => (int)$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user || (int)$user['is_active'] !== 1) {
            auth_logout();
            $cachedUser = null;
            return null;
        }

        // Keep session role synced with authoritative DB state
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];

        $cachedUser = $user;
        return $user;
    } catch (PDOException $e) {
        error_log('[DENTAL AUTH USER FETCH] ' . $e->getMessage());
        return null;
    }
}

/**
 * Checks whether a user is logged in.
 */
function auth_is_logged_in(): bool
{
    return auth_user() !== null;
}

/**
 * Checks whether the current user has the administrator role.
 */
function auth_is_admin(): bool
{
    $user = auth_user();
    return $user !== null && ($user['role'] ?? '') === 'admin';
}

/**
 * Sanitizes an internal redirect URL strictly to paths inside DENTAL_BASE_URL.
 * Prevents open redirects, protocol-relative URLs, and auth-loop targets.
 */
function auth_sanitize_internal_redirect(?string $url): ?string
{
    if ($url === null) {
        return null;
    }
    $trimmed = trim($url);
    if ($trimmed === '' || preg_match('/[\r\n\0\\\\]/', $trimmed) || str_starts_with($trimmed, '//')) {
        return null;
    }

    $parts = @parse_url($trimmed);
    if (!is_array($parts) || isset($parts['scheme']) || isset($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
        return null;
    }

    $path = $parts['path'] ?? '';
    if ($path === '') {
        return null;
    }
    if (!str_starts_with($path, '/')) {
        $path = rtrim(DENTAL_BASE_URL, '/') . '/' . ltrim($path, '/');
    }

    $basePrefix = rtrim(DENTAL_BASE_URL, '/') . '/';
    if (!str_starts_with($path, $basePrefix)) {
        return null;
    }

    $basename = strtolower(basename($path));
    if (in_array($basename, ['login.php', 'register.php', 'logout.php'], true)) {
        return null;
    }

    return $path . (isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '');
}

/**
 * Stores a validated post-login destination URL in the session.
 */
function auth_set_intended_url(?string $url): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    $safe = auth_sanitize_internal_redirect($url);
    if ($safe !== null) {
        $_SESSION['intended_url'] = $safe;
    }
}

/**
 * Returns the validated intended URL from the session without clearing it.
 */
function auth_get_intended_url(): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['intended_url'])) {
        return null;
    }
    return auth_sanitize_internal_redirect((string)$_SESSION['intended_url']);
}

/**
 * Retrieves and clears the validated intended URL from the session.
 */
function auth_consume_intended_url(): ?string
{
    $url = auth_get_intended_url();
    if (session_status() === PHP_SESSION_ACTIVE) {
        unset($_SESSION['intended_url']);
    }
    return $url;
}

/**
 * Requires an authenticated session; otherwise redirects and terminates execution.
 */
function auth_require_login(?string $loginPath = null): array
{
    $user = auth_user();
    if ($user === null) {
        $target = $loginPath ?? (DENTAL_BASE_URL . '/login.php');
        header('Location: ' . $target);
        exit;
    }
    return $user;
}

/**
 * Requires an authenticated account specifically for appointment booking routes.
 * Guests are blocked (on both GET and POST) and redirected to login with intended destination preserved.
 */
function auth_require_booking_login(?string $intendedUrl = null): array
{
    $user = auth_user();
    if ($user === null) {
        $fallback = DENTAL_BASE_URL . '/booking.php';
        $rawIntended = $intendedUrl ?? ($_SERVER['REQUEST_URI'] ?? $fallback);
        $safeIntended = auth_sanitize_internal_redirect($rawIntended) ?? $fallback;

        auth_set_intended_url($safeIntended);

        $msg = function_exists('lang_raw')
            ? lang_raw('booking_auth_required')
            : 'Please sign in or create a patient account to book an appointment.';
        flash_set($_SERVER['REQUEST_METHOD'] === 'POST' ? 'warning' : 'info', $msg);

        header('Location: ' . DENTAL_BASE_URL . '/login.php?redirect=' . rawurlencode($safeIntended));
        exit;
    }
    return $user;
}

/**
 * Requires an authenticated non-admin patient session for patient portal pages.
 * Redirects guests to login and redirects admins to the exclusive Admin Platform sidebar.
 */
function auth_require_patient(?string $loginPath = null): array
{
    $user = auth_require_login($loginPath);
    if (($user['role'] ?? '') === 'admin') {
        header('Location: ' . DENTAL_BASE_URL . '/admin/index.php');
        exit;
    }
    return $user;
}

/**
 * Requires an authenticated administrator session; prevents patient/guest access.
 */
function auth_require_admin(?string $loginPath = null): array
{
    $user = auth_user();
    if ($user === null) {
        $target = $loginPath ?? (DENTAL_BASE_URL . '/login.php');
        header('Location: ' . $target);
        exit;
    }

    if (($user['role'] ?? '') !== 'admin') {
        audit_log('UNAUTHORIZED_ADMIN_ACCESS', 'Non-admin user attempted admin access: ' . ($_SERVER['REQUEST_URI'] ?? ''), (int)$user['id']);
        http_response_code(403);
        header('Location: ' . DENTAL_BASE_URL . '/patient/index.php?error=forbidden');
        exit;
    }

    return $user;
}

/**
 * Resolves the safe web path for a user avatar with graceful fallback.
 */
function get_user_avatar_url(?string $imageName): string
{
    if (!empty($imageName)) {
        $cleanName = basename($imageName);
        if (is_file(DENTAL_UPLOADS_DIR . DIRECTORY_SEPARATOR . 'avatars' . DIRECTORY_SEPARATOR . $cleanName)) {
            return DENTAL_BASE_URL . '/assets/uploads/avatars/' . rawurlencode($cleanName);
        }
        if (is_file(DENTAL_ROOT . DIRECTORY_SEPARATOR . 'login' . DIRECTORY_SEPARATOR . 'uploaded_img' . DIRECTORY_SEPARATOR . $cleanName)) {
            return DENTAL_BASE_URL . '/login/uploaded_img/' . rawurlencode($cleanName);
        }
    }
    return DENTAL_BASE_URL . '/login/images/default-avatar.png';
}

/**
 * Resolves the safe web path for a dentist photograph with fallback.
 */
function get_dentist_photo_url(?string $imageName): string
{
    if (!empty($imageName)) {
        $cleanName = basename($imageName);
        if (is_file(DENTAL_DOCTORS_IMG_DIR . DIRECTORY_SEPARATOR . $cleanName)) {
            return DENTAL_BASE_URL . '/assets/images/doctors/' . rawurlencode($cleanName);
        }
        if (is_file(DENTAL_ROOT . DIRECTORY_SEPARATOR . 'imgs' . DIRECTORY_SEPARATOR . $cleanName)) {
            return DENTAL_BASE_URL . '/imgs/' . rawurlencode($cleanName);
        }
    }
    return DENTAL_BASE_URL . '/login/images/default-avatar.png';
}

init_secure_session();
