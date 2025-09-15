<?php
/**
 * AJAX endpoint to export attendance records for a student as PDF using mPDF
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
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . "/../../../utils/index.php";
$con = getDatabaseConnection();

try {
    $student_id = $_SESSION['user_id'];
    
    // Get filter parameters
    $event_filter = $_GET['event'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $format = $_GET['format'] ?? 'excel';
    
    // Build query
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
    
    // Get student info
    $student_query = "SELECT u.firstname, u.lastname, u.email 
                      FROM users u 
                      WHERE u.user_id = ?";
    $stmt = $con->prepare($student_query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    
    // Prepare filters for PDF generation
    $filters = [
        'event' => $event_filter,
        'status' => $status_filter,
        'date_from' => $date_from,
        'date_to' => $date_to
    ];
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Generate PDF using mPDF
    try {
        generateAttendanceReportPdf($attendance_records, $student, $filters, 'D');
        exit;
    } catch (Exception $e) {
        error_log("PDF Generation Error: " . $e->getMessage());
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Export attendance error: " . $e->getMessage());
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Return error response
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error generating PDF: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
