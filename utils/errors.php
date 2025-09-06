<?php
/**
 * Error Handling and Logging Utilities
 * Handles error logging, display, and debugging
 */

// Prevent direct access
if (!defined('IN_APP')) {
    header('HTTP/1.0 403 Forbidden');
    exit();
}

/**
 * Log error message
 * @param string $message Error message
 * @param string $level Error level (error, warning, info)
 * @return void
 */
function logError($message, $level = 'error') {
    $logFile = __DIR__ . "/../logs/admin_errors.log";
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    // Ensure logs directory exists
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Display error message
 * @param string $message Error message
 * @param string $type Alert type (danger, warning, info, success)
 * @return string HTML for error message
 */
function displayError($message, $type = 'danger') {
    return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>";
}

/**
 * Display success message
 * @param string $message Success message
 * @return string HTML for success message
 */
function displaySuccess($message) {
    return displayError($message, 'success');
}

/**
 * Display warning message
 * @param string $message Warning message
 * @return string HTML for warning message
 */
function displayWarning($message) {
    return displayError($message, 'warning');
}

/**
 * Display info message
 * @param string $message Info message
 * @return string HTML for info message
 */
function displayInfo($message) {
    return displayError($message, 'info');
}

/**
 * Handle and log database errors
 * @param mysqli $connection Database connection
 * @param string $operation Operation being performed
 * @return string Error message for display
 */
function handleDatabaseError($connection, $operation = 'Database operation') {
    $error = mysqli_error($connection);
    logError("{$operation} failed: {$error}");
    return displayError("Database error occurred. Please try again.");
}

/**
 * Handle and log query errors
 * @param string $query SQL query
 * @param string $error Error message
 * @return string Error message for display
 */
function handleQueryError($query, $error) {
    logError("Query failed: {$error}. Query: {$query}");
    return displayError("Database query failed. Please try again.");
}

/**
 * Log user action for audit trail
 * @param int $userId User ID
 * @param string $action Action performed
 * @param string $details Additional details
 * @return void
 */
function logUserAction($userId, $action, $details = '') {
    $logFile = __DIR__ . "/../logs/user_actions.log";
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] User {$userId}: {$action}";
    if ($details) {
        $logMessage .= " - {$details}";
    }
    $logMessage .= PHP_EOL;
    
    // Ensure logs directory exists
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}
?>
