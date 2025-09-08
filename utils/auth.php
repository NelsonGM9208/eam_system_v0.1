<?php
// Define IN_APP constant for utility access
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Safe session start with secure cookie flags
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Session timeout (30 minutes)
$timeoutSeconds = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeoutSeconds) {
    session_unset();
    session_destroy();
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = dirname(dirname($_SERVER['SCRIPT_NAME'])); // Go up two levels to get base path
    $loginUrl = $protocol . '://' . $host . $basePath . '/login.php?error=session_expired';
    header('Location: ' . $loginUrl);
    exit();
}
$_SESSION['last_activity'] = time();

// Require logged-in user
if (empty($_SESSION['email'])) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = dirname(dirname($_SERVER['SCRIPT_NAME'])); // Go up two levels to get base path
    $loginUrl = $protocol . '://' . $host . $basePath . '/login.php?error=unauthorized';
    header('Location: ' . $loginUrl);
    exit();
}

// Check if user account is deactivated
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/database.php';
    $con = getDatabaseConnection();
    
    $stmt = $con->prepare("SELECT account_status FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['account_status'] === 'deactivated') {
            // Clear session and redirect to login
            session_unset();
            session_destroy();
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $basePath = dirname(dirname($_SERVER['SCRIPT_NAME']));
            $loginUrl = $protocol . '://' . $host . $basePath . '/login.php?error=account_deactivated';
            header('Location: ' . $loginUrl);
            exit();
        }
    }
    $stmt->close();
}

// Optional: regenerate id once after login (do it at the moment of successful login)
// session_regenerate_id(true);

/**
 * Check if user has required role
 * @param string|array $requiredRoles - Single role or array of roles
 * @return bool
 */
function hasRole($requiredRoles) {
    if (empty($_SESSION['role'])) {
        return false;
    }
    
    $userRole = $_SESSION['role'];
    
    if (is_array($requiredRoles)) {
        return in_array($userRole, $requiredRoles);
    }
    
    return $userRole === $requiredRoles;
}

/**
 * Require specific role(s) - redirect if not authorized
 * @param string|array $requiredRoles - Single role or array of roles
 * @param string $redirectUrl - URL to redirect to if unauthorized
 */
function requireRole($requiredRoles, $redirectUrl = null) {
    if (!hasRole($requiredRoles)) {
        if ($redirectUrl === null) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $basePath = dirname(dirname($_SERVER['SCRIPT_NAME'])); // Go up two levels to get base path
            $redirectUrl = $protocol . '://' . $host . $basePath . '/login.php?error=insufficient_permissions';
        }
        header("Location: $redirectUrl");
        exit();
    }
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Check if user is SSLG
 * @return bool
 */
function isSSLG() {
    return hasRole('sslg');
}

/**
 * Check if user is teacher
 * @return bool
 */
function isTeacher() {
    return hasRole('teacher');
}

/**
 * Check if user is student
 * @return bool
 */
function isStudent() {
    return hasRole('student');
}

/**
 * Get current user's role
 * @return string|null
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Get current user's email
 * @return string|null
 */
function getCurrentUserEmail() {
    return $_SESSION['email'] ?? null;
}

/**
 * Get current user's ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>
