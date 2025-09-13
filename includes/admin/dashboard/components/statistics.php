<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <!-- Users Statistics -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Users
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $admin_stats['total_users'] ?? 0; ?>
                        </div>
                        <div class="text-xs text-muted">
                            <span class="text-success"><?php echo $admin_stats['active_users'] ?? 0; ?> active</span> | 
                            <span class="text-warning"><?php echo $admin_stats['pending_users'] ?? 0; ?> pending</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bx bx-user text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Events Statistics -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Events
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $admin_stats['total_events'] ?? 0; ?>
                        </div>
                        <div class="text-xs text-muted">
                            All events created
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bx bx-calendar-event text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Classes Statistics -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Classes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $admin_stats['total_classes'] ?? 0; ?>
                        </div>
                        <div class="text-xs text-muted">
                            Active sections
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bx bx-book text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Statistics -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Attendance
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $admin_stats['total_attendance'] ?? 0; ?>
                        </div>
                        <div class="text-xs text-muted">
                            <span class="text-info"><?php echo $admin_stats['today_attendance'] ?? 0; ?> today</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bx bx-check-square text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Statistics Row -->
<div class="row mb-4">
    <!-- Event Status Overview -->
    <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-warning text-white">
                <h6 class="mb-0">
                    <i class="bx bx-time mr-2"></i>
                    Event Status
                </h6>
            </div>
            <div class="card-body p-2">
                <div class="row text-center g-2">
                    <div class="col-4">
                        <div class="p-1 rounded" style="background-color: rgba(255, 193, 7, 0.1);">
                            <h4 class="text-warning mb-1 fw-bold"><?php echo $admin_stats['upcoming_events'] ?? 0; ?></h4>
                            <small class="text-muted fw-medium">Upcoming</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-1 rounded" style="background-color: rgba(13, 202, 240, 0.1);">
                            <h4 class="text-info mb-1 fw-bold"><?php echo $admin_stats['ongoing_events'] ?? 0; ?></h4>
                            <small class="text-muted fw-medium">Ongoing</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-1 rounded" style="background-color: rgba(40, 167, 69, 0.1);">
                            <h4 class="text-success mb-1 fw-bold"><?php echo $admin_stats['finished_events'] ?? 0; ?></h4>
                            <small class="text-muted fw-medium">Finished</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Approval Status -->
    <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-success text-white">
                <h6 class="mb-0">
                    <i class="bx bx-check-circle mr-2"></i>
                    Event Approval
                </h6>
            </div>
            <div class="card-body p-2">
                <div class="row text-center g-2">
                    <div class="col-6">
                        <div class="p-1 rounded" style="background-color: rgba(40, 167, 69, 0.1);">
                            <h4 class="text-success mb-1 fw-bold"><?php echo $admin_stats['approved_events'] ?? 0; ?></h4>
                            <small class="text-muted fw-medium">Approved</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-1 rounded" style="background-color: rgba(255, 193, 7, 0.1);">
                            <h4 class="text-warning mb-1 fw-bold"><?php echo $admin_stats['pending_events'] ?? 0; ?></h4>
                            <small class="text-muted fw-medium">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Activity -->
    <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-primary text-white">
                <h6 class="mb-0">
                    <i class="bx bx-calendar-check mr-2"></i>
                    Today's Activity
                </h6>
            </div>
            <div class="card-body p-2">
                <div class="row text-center g-2">
                    <div class="col-6">
                        <div class="p-1 rounded" style="background-color: rgba(13, 110, 253, 0.1);">
                            <h4 class="text-primary mb-1 fw-bold"><?php echo $admin_stats['today_registrations'] ?? 0; ?></h4>
                            <small class="text-muted fw-medium">New Users</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-1 rounded" style="background-color: rgba(40, 167, 69, 0.1);">
                            <h4 class="text-success mb-1 fw-bold"><?php echo $admin_stats['today_events'] ?? 0; ?></h4>
                            <small class="text-muted fw-medium">New Events</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health -->
    <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-info text-white">
                <h6 class="mb-0">
                    <i class="bx bx-shield mr-2"></i>
                    System Health
                </h6>
            </div>
            <div class="card-body p-2">
                <div class="row text-center g-2">
                    <div class="col-6">
                        <div class="p-1 rounded" style="background-color: rgba(40, 167, 69, 0.1);">
                            <h4 class="text-success mb-1 fw-bold"><?php echo $admin_stats['total_logs'] ?? 0; ?></h4>
                            <small class="text-muted fw-medium">Total Logs</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-1 rounded" style="background-color: rgba(13, 202, 240, 0.1);">
                            <h4 class="text-info mb-1 fw-bold"><?php echo $admin_stats['today_logs'] ?? 0; ?></h4>
                            <small class="text-muted fw-medium">Today's Logs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
