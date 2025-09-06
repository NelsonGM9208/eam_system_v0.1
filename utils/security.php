<?php
/**
 * Security Utilities
 * Handles input validation, sanitization, and security functions
 */

// Prevent direct access
if (!defined('IN_APP')) {
    header('HTTP/1.0 403 Forbidden');
    exit();
}

/**
 * Sanitize output
 * @param string $value Value to sanitize
 * @return string Sanitized value
 */
function sanitizeOutput($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Validate and sanitize input
 * @param mixed $input Input value
 * @param string $type Input type (string, int, email, url)
 * @param mixed $default Default value if validation fails
 * @return mixed Sanitized value or default
 */
function validateInput($input, $type = 'string', $default = '') {
    if ($input === null || $input === '') return $default;
    
    switch ($type) {
        case 'int':
            return filter_var($input, FILTER_VALIDATE_INT) !== false ? (int)$input : $default;
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL) !== false ? $input : $default;
        case 'url':
            return filter_var($input, FILTER_VALIDATE_URL) !== false ? $input : $default;
        case 'string':
        default:
            return is_string($input) ? trim($input) : $default;
    }
}

/**
 * Check if user has permission
 * @param string $permission Permission to check
 * @param array $userRoles User's roles
 * @return bool True if user has permission
 */
function hasPermission($permission, $userRoles = []) {
    $permissions = [
        'view_users' => ['admin', 'teacher'],
        'edit_users' => ['admin'],
        'delete_users' => ['admin'],
        'approve_users' => ['admin'],
        'view_events' => ['admin', 'teacher', 'student'],
        'edit_events' => ['admin', 'teacher'],
        'delete_events' => ['admin'],
        'create_events' => ['admin', 'teacher']
    ];
    
    if (!isset($permissions[$permission])) return false;
    
    return !empty(array_intersect($userRoles, $permissions[$permission]));
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if token is valid
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize filename for safe storage
 * @param string $filename Original filename
 * @return string Sanitized filename
 */
function sanitizeFilename($filename) {
    // Remove any character that isn't alphanumeric, dot, dash, or underscore
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    // Remove multiple dots
    $filename = preg_replace('/\.{2,}/', '.', $filename);
    // Ensure filename isn't empty
    if (empty($filename)) {
        $filename = 'file_' . time();
    }
    return $filename;
}

/**
 * Validate file upload
 * @param array $file $_FILES array element
 * @param array $allowedTypes Allowed MIME types
 * @param int $maxSize Maximum file size in bytes
 * @return array Validation result with success status and message
 */
function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) { // 5MB default
    $result = ['success' => false, 'message' => ''];
    
    if (!isset($file['error']) || is_array($file['error'])) {
        $result['message'] = 'Invalid file upload.';
        return $result;
    }
    
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            $result['message'] = 'No file uploaded.';
            return $result;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $result['message'] = 'File too large.';
            return $result;
        default:
            $result['message'] = 'Unknown upload error.';
            return $result;
    }
    
    if ($file['size'] > $maxSize) {
        $result['message'] = 'File too large.';
        return $result;
    }
    
    if (!empty($allowedTypes) && !in_array($file['type'], $allowedTypes)) {
        $result['message'] = 'Invalid file type.';
        return $result;
    }
    
    $result['success'] = true;
    return $result;
}
?>
