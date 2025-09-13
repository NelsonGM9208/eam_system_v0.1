<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Debug session data
error_log("Student Header Component - Session Debug:");
error_log("firstname: " . ($_SESSION['firstname'] ?? 'NOT SET'));
error_log("lastname: " . ($_SESSION['lastname'] ?? 'NOT SET'));
error_log("role: " . ($_SESSION['role'] ?? 'NOT SET'));
error_log("user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));

// Get current student user info
$student_name = ($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? '');
$student_name = trim($student_name) ?: 'Student';
$student_role = $_SESSION['role'] ?? 'Student';

// Get last login time from session
$last_login_time = $_SESSION['login_time'] ?? 'Never';
if ($last_login_time !== 'Never') {
    $last_login_time = formatDisplayDate($last_login_time, 'F j, Y g:i A');
}

// Get current section info
$current_section = 'Not Enrolled';
if (isset($student_stats) && $student_stats['section_grade'] && $student_stats['section_name']) {
    $current_section = $student_stats['section_grade'] . '-' . $student_stats['section_name'];
}
?>

<!-- Welcome Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-success text-white shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1">
                            <i class="bx bx-user mr-2"></i>
                            Student Dashboard
                        </h2>
                        <p class="mb-0 opacity-75">
                            Welcome back, <strong><?php echo htmlspecialchars($student_name); ?></strong>! 
                            Track your events, attendance, and academic progress here.
                        </p>
                        <small class="opacity-75">
                            <i class="bx bx-book me-1"></i>
                            Section: <?php echo htmlspecialchars($current_section); ?>
                            <?php if ($student_stats['teacher_name']): ?>
                                | Teacher: <?php echo htmlspecialchars($student_stats['teacher_name']); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex flex-column">
                            <small class="opacity-75">Last Login</small>
                            <strong><?php echo $last_login_time; ?></strong>
                            <small class="opacity-75 mt-1">Role: <?php echo htmlspecialchars($student_role); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
