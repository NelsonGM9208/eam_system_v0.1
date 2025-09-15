<?php
/**
 * AJAX handler for excuse letter approval/rejection
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../../utils/index.php";

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? '';
    $excuseId = isset($_POST['excuse_id']) ? (int)$_POST['excuse_id'] : null;
    $status = $_POST['status'] ?? ''; // 'Approved' or 'Rejected'
    $adminResponse = $_POST['admin_response'] ?? '';
    
    if (!$excuseId || !in_array($status, ['Approved', 'Rejected'])) {
        throw new Exception('Invalid parameters');
    }
    
    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Database connection failed');
    }
    
    // Get excuse letter details
    $query = "SELECT * FROM excuse_letters WHERE excuse_id = ?";
    $stmt = $con->prepare($query);
    if (!$stmt) {
        throw new Exception('Database error: ' . $con->error);
    }
    
    $stmt->bind_param("i", $excuseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $excuse = $result->fetch_assoc();
    
    if (!$excuse) {
        throw new Exception('Excuse letter not found');
    }
    
    // Update excuse letter status
    $updateQuery = "UPDATE excuse_letters 
                    SET status = ?, 
                        admin_response = ?, 
                        reviewed_by = ?, 
                        reviewed_at = NOW() 
                    WHERE excuse_id = ?";
    
    $updateStmt = $con->prepare($updateQuery);
    if (!$updateStmt) {
        throw new Exception('Database error: ' . $con->error);
    }
    
    $adminId = $_SESSION['user_id'];
    $updateStmt->bind_param("ssii", $status, $adminResponse, $adminId, $excuseId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update excuse letter');
    }
    
    // If approved, update attendance from Absent to Excused
    if ($status === 'Approved' && $excuse['event_id']) {
        $processor = new AutoAbsentProcessor();
        $updated = $processor->updateAbsentToExcused($excuse['student_id'], $excuse['event_id']);
        
        if ($updated) {
            // Log the action
            $logQuery = "INSERT INTO logs (user_id, user_role, log_action) VALUES (?, ?, ?)";
            $logStmt = $con->prepare($logQuery);
            $logAction = "Approved excuse letter ID: {$excuseId} for student ID: {$excuse['student_id']}, event ID: {$excuse['event_id']} - Updated attendance from Absent to Excused";
            $logStmt->bind_param("iss", $adminId, $_SESSION['role'], $logAction);
            $logStmt->execute();
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Excuse letter {$status} successfully",
        'data' => [
            'excuse_id' => $excuseId,
            'status' => $status,
            'attendance_updated' => ($status === 'Approved' && $excuse['event_id']) ? true : false
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Excuse letter approval error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>
