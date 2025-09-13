<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Debug session data
error_log("Header Component - Session Debug:");
error_log("firstname: " . ($_SESSION['firstname'] ?? 'NOT SET'));
error_log("lastname: " . ($_SESSION['lastname'] ?? 'NOT SET'));
error_log("role: " . ($_SESSION['role'] ?? 'NOT SET'));
error_log("user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));

// Get current admin user info
$admin_name = ($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? '');
$admin_name = trim($admin_name) ?: 'Admin';
$admin_role = $_SESSION['role'] ?? 'Administrator';
// Get last login time from session
$last_login_time = $_SESSION['login_time'] ?? 'Never';
if ($last_login_time !== 'Never') {
    $last_login_time = formatDisplayDate($last_login_time, 'F j, Y g:i A');
}
?>

<!-- Welcome Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white shadow-sm">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1">
                            <i class="bx bx-shield-check mr-2"></i>
                            Admin Dashboard
                        </h4>
                        <p class="mb-0 opacity-75 small">
                            Welcome back, <strong><?php echo htmlspecialchars($admin_name); ?></strong>! 
                            Manage your system and monitor activities here.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex flex-column">
                             <small class="opacity-75">Last Login</small>
                             <strong class="small"><?php echo $last_login_time; ?></strong>
                            <small class="opacity-75">Role: <?php echo htmlspecialchars($admin_role); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
