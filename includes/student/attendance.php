<?php
/**
 * Student Attendance Management
 * View personal attendance records and statistics
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: /eam_system_v0.1.1/index.php');
    exit;
}

require_once __DIR__ . "/../../utils/index.php";
$con = getDatabaseConnection();

// Get current student info
$student_id = $_SESSION['user_id'];

// Get student's section
$student_section_query = "SELECT s.section_id, s.grade, s.section 
                         FROM enrollment e 
                         JOIN section s ON e.section_id = s.section_id 
                         WHERE e.student_id = ? AND e.status = 'Active'";
$stmt = $con->prepare($student_section_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_section = $stmt->get_result()->fetch_assoc();

$student_section_id = $student_section['section_id'] ?? null;

// Get filter parameters
$event_filter = $_GET['event'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Get attendance records
$attendance_query = "SELECT a.*, e.title as event_title, e.event_date, e.start_time, e.end_time, e.location
                     FROM attendance a
                     JOIN events e ON a.event_id = e.event_id
                     WHERE a.student_id = ?";

$params = [$student_id];
$param_types = "i";

if ($event_filter) {
    $attendance_query .= " AND a.event_id = ?";
    $params[] = $event_filter;
    $param_types .= "i";
}

if ($status_filter) {
    $attendance_query .= " AND a.remark = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

if ($date_from) {
    $attendance_query .= " AND e.event_date >= ?";
    $params[] = $date_from;
    $param_types .= "s";
}

if ($date_to) {
    $attendance_query .= " AND e.event_date <= ?";
    $params[] = $date_to;
    $param_types .= "s";
}

$attendance_query .= " ORDER BY e.event_date DESC, a.check_in_time DESC";

$stmt = $con->prepare($attendance_query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$attendance_records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get attendance statistics
$stats_query = "SELECT 
                COUNT(*) as total_attendance,
                SUM(CASE WHEN remark = 'Present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN remark = 'Late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN remark = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN remark = 'Excused' THEN 1 ELSE 0 END) as excused_count
                FROM attendance 
                WHERE student_id = ?";
$stmt = $con->prepare($stats_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Calculate attendance rate
$attendance_rate = $stats['total_attendance'] > 0 ? 
    round((($stats['present_count'] + $stats['late_count']) / $stats['total_attendance']) * 100, 1) : 0;

// Get available events for filter
$events_query = "SELECT DISTINCT e.event_id, e.title 
                 FROM events e
                 JOIN attendance a ON e.event_id = a.event_id
                 WHERE a.student_id = ?
                 ORDER BY e.title";
$stmt = $con->prepare($events_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$available_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-white rounded-circle p-3 mr-3 shadow-sm">
                                    <i class="bx bx-clipboard text-info" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h3 class="text-white mb-1 font-weight-bold">Attendance Management</h3>
                                    <p class="text-white-50 mb-0">Track your personal attendance records and statistics</p>
                                </div>
                            </div>
                            <?php if ($student_section): ?>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-group text-white mr-2"></i>
                                    <span class="text-white">
                                        <strong>Your Section:</strong> Grade <?php echo htmlspecialchars($student_section['grade']); ?> - <?php echo htmlspecialchars($student_section['section']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-md-right mt-3 mt-md-0">
                            <div class="bg-white rounded-lg p-3 shadow-sm d-inline-block">
                                <div class="text-center">
                                    <div class="text-info mb-1">
                                        <i class="bx bx-trending-up" style="font-size: 1.2rem;"></i>
                                    </div>
                                    <div class="text-muted small mb-1">Attendance Rate</div>
                                    <div class="h4 text-info mb-0 font-weight-bold"><?php echo $attendance_rate; ?>%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="bg-primary rounded-circle p-2 mr-2">
                            <i class="bx bx-calendar-check text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-primary"><?php echo $stats['total_attendance']; ?></h5>
                            <small class="text-muted">Total Events</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="bg-success rounded-circle p-2 mr-2">
                            <i class="bx bx-check text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-success"><?php echo $stats['present_count']; ?></h5>
                            <small class="text-muted">Present</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="bg-warning rounded-circle p-2 mr-2">
                            <i class="bx bx-time text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-warning"><?php echo $stats['late_count']; ?></h5>
                            <small class="text-muted">Late</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="bg-danger rounded-circle p-2 mr-2">
                            <i class="bx bx-x text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-danger"><?php echo $stats['absent_count']; ?></h5>
                            <small class="text-muted">Absent</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <div class="row align-items-end" id="attendanceFilterControls">
                        <div class="col-md-3">
                            <label for="event_filter" class="form-label small mb-1">Filter by Event</label>
                            <select class="form-control form-control-sm" id="event_filter" name="event">
                                <option value="">All Events</option>
                                <?php foreach ($available_events as $event): ?>
                                    <option value="<?php echo $event['event_id']; ?>" <?php echo $event_filter == $event['event_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($event['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status_filter" class="form-label small mb-1">Status</label>
                            <select class="form-control form-control-sm" id="status_filter" name="status">
                                <option value="">All Status</option>
                                <option value="Present" <?php echo $status_filter === 'Present' ? 'selected' : ''; ?>>Present</option>
                                <option value="Late" <?php echo $status_filter === 'Late' ? 'selected' : ''; ?>>Late</option>
                                <option value="Absent" <?php echo $status_filter === 'Absent' ? 'selected' : ''; ?>>Absent</option>
                                <option value="Excused" <?php echo $status_filter === 'Excused' ? 'selected' : ''; ?>>Excused</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label small mb-1">From Date</label>
                            <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" 
                                   value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label small mb-1">To Date</label>
                            <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" 
                                   value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex">
                                <button type="button" class="btn btn-outline-secondary btn-sm mr-1" id="clearAttendanceFilters" style="flex: 1;">
                                    <i class="bx bx-x"></i> Clear
                                </button>
                                <button type="button" class="btn btn-info btn-sm" id="exportAttendance" style="flex: 1;">
                                    <i class="bx bx-download"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Records Table -->
    <div id="attendanceContainer">
        <?php if (empty($attendance_records)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bx bx-clipboard text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-muted">No Attendance Records Found</h4>
                    <p class="text-muted">
                        <?php if ($event_filter || $status_filter || $date_from || $date_to): ?>
                            No attendance records match your current filters. Try adjusting your search criteria.
                        <?php else: ?>
                            You don't have any attendance records yet.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bx bx-list-ul mr-2"></i>
                        Attendance History
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">Event Details</th>
                                    <th class="border-0">Date & Time</th>
                                    <th class="border-0">Check-in Time</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Location</th>
                                    <th class="border-0 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_records as $record): ?>
                                    <tr class="border-bottom">
                                        <td class="align-middle">
                                            <div>
                                                <h6 class="mb-1 text-dark"><?php echo htmlspecialchars($record['event_title']); ?></h6>
                                                <small class="text-muted">Event ID: <?php echo $record['event_id']; ?></small>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div>
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="bx bx-calendar text-info mr-2"></i>
                                                    <small class="text-dark"><?php echo date('M j, Y', strtotime($record['event_date'])); ?></small>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i class="bx bx-time text-info mr-2"></i>
                                                    <small class="text-muted"><?php echo date('g:i A', strtotime($record['start_time'])); ?> - <?php echo date('g:i A', strtotime($record['end_time'])); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-log-in text-info mr-2"></i>
                                                <small class="text-muted">
                                                    <?php echo $record['check_in_time'] ? date('M j, Y g:i A', strtotime($record['check_in_time'])) : 'Not checked in'; ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <?php
                                            $status_class = '';
                                            $status_icon = '';
                                            switch ($record['remark']) {
                                                case 'Present':
                                                    $status_class = 'badge-success';
                                                    $status_icon = 'bx-check';
                                                    break;
                                                case 'Late':
                                                    $status_class = 'badge-warning';
                                                    $status_icon = 'bx-time';
                                                    break;
                                                case 'Absent':
                                                    $status_class = 'badge-danger';
                                                    $status_icon = 'bx-x';
                                                    break;
                                                case 'Excused':
                                                    $status_class = 'badge-info';
                                                    $status_icon = 'bx-info-circle';
                                                    break;
                                                default:
                                                    $status_class = 'badge-secondary';
                                                    $status_icon = 'bx-help-circle';
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>">
                                                <i class="bx <?php echo $status_icon; ?> mr-1"></i>
                                                <?php echo htmlspecialchars($record['remark']); ?>
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-map text-info mr-2"></i>
                                                <small class="text-muted"><?php echo htmlspecialchars($record['location']); ?></small>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewAttendanceDetails(<?php echo $record['attendance_id']; ?>)" title="View Details">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printAttendanceRecord(<?php echo $record['attendance_id']; ?>)" title="Print Record">
                                                    <i class="bx bx-printer"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Attendance Styles -->
<link rel="stylesheet" href="/eam_system_v0.1.1/assets/css/student_attendance.css">

<!-- Initialize attendance page when loaded -->
<script>
// Initialize the attendance page when this content is loaded
if (typeof initializeAttendancePage === 'function') {
    initializeAttendancePage();
} else {
    console.log('initializeAttendancePage function not available yet');
}
</script>
