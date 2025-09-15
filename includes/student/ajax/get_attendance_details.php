<?php
/**
 * AJAX endpoint to get detailed attendance information for a student
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
    $attendance_id = intval($_GET['attendance_id'] ?? 0);
    $student_id = $_SESSION['user_id'];
    
    if ($attendance_id <= 0) {
        throw new Exception('Invalid attendance ID');
    }
    
    // Get detailed attendance information
    $attendance_query = "SELECT a.*, e.title as event_title, e.event_description, e.event_date, 
                                e.start_time, e.end_time, e.location, e.event_type, e.event_status,
                                u.firstname, u.lastname, u.email
                         FROM attendance a
                         JOIN events e ON a.event_id = e.event_id
                         JOIN users u ON a.student_id = u.user_id
                         WHERE a.attendance_id = ? AND a.student_id = ?";
    
    $stmt = $con->prepare($attendance_query);
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $con->error);
    }
    
    $stmt->bind_param("ii", $attendance_id, $student_id);
    if (!$stmt->execute()) {
        throw new Exception('Database execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Database result failed: ' . $stmt->error);
    }
    
    $attendance = $result->fetch_assoc();
    
    if (!$attendance) {
        throw new Exception('Attendance record not found or access denied');
    }
    
    // Format the response
    $response = [
        'success' => true,
        'attendance' => [
            'attendance_id' => $attendance['attendance_id'],
            'event_id' => $attendance['event_id'],
            'event_title' => $attendance['event_title'],
            'event_description' => $attendance['event_description'],
            'event_date' => $attendance['event_date'],
            'start_time' => $attendance['start_time'],
            'end_time' => $attendance['end_time'],
            'location' => $attendance['location'],
            'event_type' => $attendance['event_type'],
            'event_status' => $attendance['event_status'],
            'check_in_time' => $attendance['check_in_time'],
            'check_out_time' => $attendance['check_out_time'],
            'remark' => $attendance['remark'],
            'notes' => $attendance['notes'],
            'student_name' => $attendance['firstname'] . ' ' . $attendance['lastname'],
            'student_email' => $attendance['email']
        ]
    ];
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
    
} catch (Exception $e) {
    error_log("Error getting attendance details: " . $e->getMessage());
    
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
