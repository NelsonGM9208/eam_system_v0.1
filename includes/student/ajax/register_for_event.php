<?php
/**
 * AJAX endpoint to register student for an event
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
    $event_id = intval($_POST['event_id'] ?? 0);
    $registration_method = $_POST['registration_method'] ?? 'qr_scan';
    
    if ($event_id <= 0) {
        throw new Exception('Invalid event ID');
    }
    
    $student_id = $_SESSION['user_id'];
    
    // Check if student is already registered
    $check_query = "SELECT attendance_id FROM attendance WHERE student_id = ? AND event_id = ?";
    $stmt = $con->prepare($check_query);
    $stmt->bind_param("ii", $student_id, $event_id);
    $stmt->execute();
    $existing_registration = $stmt->get_result()->fetch_assoc();
    
    if ($existing_registration) {
        throw new Exception('You are already registered for this event');
    }
    
    // Get student's section
    $student_section_query = "SELECT s.section_id 
                             FROM enrollment e 
                             JOIN section s ON e.section_id = s.section_id 
                             WHERE e.student_id = ? AND e.status = 'Active'";
    $stmt = $con->prepare($student_section_query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student_section = $stmt->get_result()->fetch_assoc();
    $student_section_id = $student_section['section_id'] ?? null;
    
    // Check if student can access this event and validate timing
    $access_query = "SELECT e.event_id, e.event_type, e.event_date, e.start_time,
                            CASE 
                                WHEN e.event_type = 'Open' THEN 1
                                WHEN e.event_type = 'Exclusive' AND es.section_id = ? THEN 1
                                ELSE 0
                            END as can_access
                     FROM events e
                     LEFT JOIN event_section es ON e.event_id = es.event_id
                     WHERE e.event_id = ? AND e.approval_status = 'Approved'";
    
    $stmt = $con->prepare($access_query);
    $stmt->bind_param("ii", $student_section_id, $event_id);
    $stmt->execute();
    $access_check = $stmt->get_result()->fetch_assoc();
    
    if (!$access_check || !$access_check['can_access']) {
        throw new Exception('You do not have access to this event');
    }
    
    // Validate registration timing - QR codes are only valid 1 hour before event start
    $event_datetime = $access_check['event_date'] . ' ' . $access_check['start_time'];
    $event_timestamp = strtotime($event_datetime);
    $current_timestamp = time();
    $time_diff_hours = ($event_timestamp - $current_timestamp) / 3600;
    
    if ($time_diff_hours > 1) {
        throw new Exception('Registration is not yet available. QR codes become valid 1 hour before the event starts.');
    } elseif ($time_diff_hours < 0) {
        throw new Exception('Registration is no longer available. The event has already started or passed.');
    }
    
    // Register student for the event (create attendance record)
    $current_time = date('Y-m-d H:i:s');
    $insert_query = "INSERT INTO attendance (student_id, event_id, check_in_time, remark) 
                     VALUES (?, ?, ?, 'Present')";
    
    $stmt = $con->prepare($insert_query);
    $stmt->bind_param("iis", $student_id, $event_id, $current_time);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to register for event: ' . $stmt->error);
    }
    
    // Log the registration
    $log_query = "INSERT INTO logs (user_id, user_role, log_action) VALUES (?, 'student', ?)";
    $log_action = "Registered for event ID: $event_id via $registration_method";
    $stmt = $con->prepare($log_query);
    $stmt->bind_param("is", $student_id, $log_action);
    $stmt->execute();
    
    // Prepare response
    $response = [
        'success' => true,
        'message' => 'Successfully registered for the event',
        'registration_id' => $con->insert_id,
        'registration_time' => $current_time
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error registering for event: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
