<?php
/**
 * Enhanced Admin Dashboard
 * Uses modular components for better maintainability and performance
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Include the new modular dashboard
include __DIR__ . "/dashboard/dashboard.php";
?>