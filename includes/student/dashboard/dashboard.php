<?php
/**
 * Student Dashboard
 * Tailored dashboard for student functionalities
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include data fetching
require_once __DIR__ . "/data/statistics_data.php";
?>

<div class="container-fluid">
    <!-- Dashboard Header -->
    <?php include __DIR__ . "/components/header.php"; ?>

    <!-- Student Statistics Cards -->
    <?php include __DIR__ . "/components/statistics.php"; ?>

    <!-- Student Quick Actions -->
    <?php include __DIR__ . "/components/quick_actions.php"; ?>

    <!-- Upcoming Events -->
    <?php include __DIR__ . "/components/upcoming_events.php"; ?>

    <!-- Attendance History -->
    <?php include __DIR__ . "/components/attendance_history.php"; ?>

    <!-- Personal Information -->
    <?php include __DIR__ . "/components/personal_info.php"; ?>
</div>

<!-- Include Student Dashboard Styles -->
<link rel="stylesheet" href="/eam_system_v0.1.1/assets/css/admin_dashboard.css">
<link rel="stylesheet" href="/eam_system_v0.1.1/assets/css/student_dashboard.css">

<!-- Include Student Dashboard JavaScript -->
<script src="/eam_system_v0.1.1/includes/student/dashboard/assets/student_dashboard.js"></script>
