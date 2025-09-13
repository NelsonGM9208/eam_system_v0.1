<?php
/**
 * AJAX endpoint to get event details for students
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
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . "/../../../utils/index.php";
$con = getDatabaseConnection();

try {
    $event_id = intval($_GET['event_id'] ?? 0);
    
    if ($event_id <= 0) {
        throw new Exception('Invalid event ID');
    }
    
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
    
    // Get event details with registration status
    $event_query = "SELECT e.*, 
                    CASE 
                        WHEN a.attendance_id IS NOT NULL THEN 'Registered'
                        ELSE 'Not Registered'
                    END as registration_status
                    FROM events e
                    LEFT JOIN event_section es ON e.event_id = es.event_id
                    LEFT JOIN attendance a ON e.event_id = a.event_id AND a.student_id = ?
                    WHERE e.event_id = ? AND e.approval_status = 'Approved'
                    AND (e.event_type = 'Open' OR (e.event_type = 'Exclusive' AND es.section_id = ?))";
    
    $stmt = $con->prepare($event_query);
    $stmt->bind_param("iii", $student_id, $event_id, $student_section_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    
    if (!$event) {
        throw new Exception('Event not found or not accessible');
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'event' => $event
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error getting event details: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
