<?php
/**
 * Enhanced Admin Dashboard
 * Modular dashboard with separate components for better maintainability
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

<div class="container-fluid admin-dashboard">
    <!-- Dashboard Header -->
    <?php include __DIR__ . "/components/header.php"; ?>

    <!-- Statistics Cards -->
    <?php include __DIR__ . "/components/statistics.php"; ?>

    <!-- Quick Actions -->
    <?php include __DIR__ . "/components/quick_actions.php"; ?>

    <!-- Pending Items Summary -->
    <?php include __DIR__ . "/components/pending_items.php"; ?>

    <!-- Recent Activities -->
    <?php include __DIR__ . "/components/recent_activities.php"; ?>

    <!-- System Overview
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bx bx-trending-up me-2"></i>
                        System Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-success">99.9%</h4>
                                <small class="text-muted">Uptime</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-info">2.1s</h4>
                                <small class="text-muted">Avg Response</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="text-warning">45MB</h4>
                            <small class="text-muted">Memory Usage</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bx bx-shield me-2"></i>
                        Security Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-success">Secure</h4>
                                <small class="text-muted">SSL Status</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-info">0</h4>
                                <small class="text-muted">Failed Logins</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="text-warning">Updated</h4>
                            <small class="text-muted">Last Backup</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>-->

<!-- Include Admin Dashboard Styles -->
<link rel="stylesheet" href="/eam_system_v0.1.1/assets/css/admin_dashboard.css">
<link rel="stylesheet" href="/eam_system_v0.1.1/assets/css/admin_dashboard_enhanced.css">
