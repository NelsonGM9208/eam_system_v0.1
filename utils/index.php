<?php
/**
 * Utils Index File
 * Central include file for all utility functions
 */

// Prevent direct access
if (!defined('IN_APP')) {
    header('HTTP/1.0 403 Forbidden');
    exit();
}

// Set global timezone for the entire application
date_default_timezone_set('Asia/Manila');

// Include all utility files in correct order
require_once __DIR__ . '/errors.php';      // Error handling first
require_once __DIR__ . '/security.php';    // Security utilities
require_once __DIR__ . '/database.php';    // Database operations (needs logError)
require_once __DIR__ . '/pagination.php';  // Pagination utilities
require_once __DIR__ . '/ui.php';          // UI utilities
require_once __DIR__ . '/date_utils.php';  // Date and time utilities
require_once __DIR__ . '/email.php';       // Email utilities
?>
