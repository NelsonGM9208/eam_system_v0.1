<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Student Statistics Cards -->
<div class="row mb-4">
    <!-- Events Attended -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Events Attended
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $student_stats['total_events_attended']; ?>
                        </div>
                        <div class="text-xs text-muted">
                            <span class="text-success"><?php echo $student_stats['present_count']; ?> present</span> | 
                            <span class="text-warning"><?php echo $student_stats['late_count']; ?> late</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bx bx-calendar-check text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Rate -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Attendance Rate
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $student_stats['attendance_rate']; ?>%
                        </div>
                        <div class="text-xs text-muted">
                            Overall performance
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bx bx-trending-up text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Events -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Upcoming Events
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $student_stats['upcoming_events']; ?>
                        </div>
                        <div class="text-xs text-muted">
                            In your section
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bx bx-calendar-event text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Excuses -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending Excuses
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $student_stats['pending_excuses']; ?>
                        </div>
                        <div class="text-xs text-muted">
                            Awaiting review
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bx bx-file text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Statistics Row -->
<div class="row mb-4">
    <!-- Attendance Breakdown -->
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-primary text-white">
                <h6 class="mb-0">
                    <i class="bx bx-pie-chart mr-2"></i>
                    Attendance Breakdown
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="row text-center g-3">
                    <div class="col-6">
                        <div class="p-3 rounded" style="background-color: rgba(40, 167, 69, 0.1);">
                            <h3 class="text-success mb-1 fw-bold"><?php echo $student_stats['present_count']; ?></h3>
                            <small class="text-muted fw-medium">Present</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background-color: rgba(255, 193, 7, 0.1);">
                            <h3 class="text-warning mb-1 fw-bold"><?php echo $student_stats['late_count']; ?></h3>
                            <small class="text-muted fw-medium">Late</small>
                        </div>
                    </div>
                </div>
                <div class="row text-center g-3 mt-2">
                    <div class="col-6">
                        <div class="p-3 rounded" style="background-color: rgba(220, 53, 69, 0.1);">
                            <h3 class="text-danger mb-1 fw-bold"><?php echo $student_stats['absent_count']; ?></h3>
                            <small class="text-muted fw-medium">Absent</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background-color: rgba(13, 202, 240, 0.1);">
                            <h3 class="text-info mb-1 fw-bold"><?php echo $student_stats['excused_count']; ?></h3>
                            <small class="text-muted fw-medium">Excused</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-success text-white">
                <h6 class="mb-0">
                    <i class="bx bx-calendar-check mr-2"></i>
                    Recent Activity
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="row text-center g-3">
                    <div class="col-6">
                        <div class="p-3 rounded" style="background-color: rgba(13, 110, 253, 0.1);">
                            <h3 class="text-primary mb-1 fw-bold"><?php echo $student_stats['recent_attendance']; ?></h3>
                            <small class="text-muted fw-medium">Last 7 Days</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background-color: rgba(25, 135, 84, 0.1);">
                            <h3 class="text-success mb-1 fw-bold"><?php echo $student_stats['upcoming_events']; ?></h3>
                            <small class="text-muted fw-medium">Upcoming</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Academic Info -->
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-info text-white">
                <h6 class="mb-0">
                    <i class="bx bx-book mr-2"></i>
                    Academic Info
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="text-center">
                    <div class="p-3 rounded" style="background-color: rgba(13, 202, 240, 0.1);">
                        <h3 class="text-info mb-1 fw-bold"><?php echo htmlspecialchars($current_section); ?></h3>
                        <small class="text-muted fw-medium">Current Section</small>
                    </div>
                    <?php if ($student_stats['teacher_name']): ?>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bx bx-user me-1"></i>
                            <?php echo htmlspecialchars($student_stats['teacher_name']); ?>
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
