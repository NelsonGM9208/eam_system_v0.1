<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: ../login.php');
exit();
?>
