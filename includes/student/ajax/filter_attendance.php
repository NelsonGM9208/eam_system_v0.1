<?php
/**
 * AJAX endpoint for filtering student attendance records
 */

// Disable error display to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to prevent any accidental output
ob_start();

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

require_once __DIR__ . "/../../../utils/index.php";
$con = getDatabaseConnection();

try {
    // Get current student info
    $student_id = $_SESSION['user_id'];

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

// Get updated attendance statistics
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

// Generate HTML for attendance records
$html = '';
if (empty($attendance_records)) {
    $html = '
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bx bx-clipboard text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-muted">No Attendance Records Found</h4>
            <p class="text-muted">
                ' . ($event_filter || $status_filter || $date_from || $date_to ? 
                    'No attendance records match your current filters. Try adjusting your search criteria.' : 
                    'You don\'t have any attendance records yet.') . '
            </p>
        </div>
    </div>';
} else {
    $html = '
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
                    <tbody>';
    
    foreach ($attendance_records as $record) {
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
        
        $html .= '
                        <tr class="border-bottom">
                            <td class="align-middle">
                                <div>
                                    <h6 class="mb-1 text-dark">' . htmlspecialchars($record['event_title']) . '</h6>
                                    <small class="text-muted">Event ID: ' . $record['event_id'] . '</small>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bx bx-calendar text-info mr-2"></i>
                                        <small class="text-dark">' . date('M j, Y', strtotime($record['event_date'])) . '</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-time text-info mr-2"></i>
                                        <small class="text-muted">' . date('g:i A', strtotime($record['start_time'])) . ' - ' . date('g:i A', strtotime($record['end_time'])) . '</small>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-log-in text-info mr-2"></i>
                                    <small class="text-muted">
                                        ' . ($record['check_in_time'] ? date('M j, Y g:i A', strtotime($record['check_in_time'])) : 'Not checked in') . '
                                    </small>
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="badge ' . $status_class . '">
                                    <i class="bx ' . $status_icon . ' mr-1"></i>
                                    ' . htmlspecialchars($record['remark']) . '
                                </span>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-map text-info mr-2"></i>
                                    <small class="text-muted">' . htmlspecialchars($record['location']) . '</small>
                                </div>
                            </td>
                            <td class="align-middle text-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewAttendanceDetails(' . $record['attendance_id'] . ')" title="View Details">
                                        <i class="bx bx-show"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printAttendanceRecord(' . $record['attendance_id'] . ')" title="Print Record">
                                        <i class="bx bx-printer"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>';
    }
    
    $html .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
}

    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'html' => $html,
        'count' => count($attendance_records),
        'stats' => $stats
    ], JSON_UNESCAPED_UNICODE);
    exit;
    
} catch (Exception $e) {
    error_log("Error filtering attendance: " . $e->getMessage());
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
