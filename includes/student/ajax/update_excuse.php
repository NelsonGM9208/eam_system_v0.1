<?php
/**
 * AJAX endpoint to update excuse letter
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

// Include utilities
require_once __DIR__ . "/../../../utils/index.php";

// Initialize database if needed
if (!initializeDatabase()) {
    if (ob_get_level()) {
        ob_clean();
    }
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Get database connection
$con = getDatabaseConnection();
if (!$con) {
    if (ob_get_level()) {
        ob_clean();
    }
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Check if user is authenticated
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
        throw new Exception('Unauthorized access');
    }
    
    $student_id = $_SESSION['user_id'];
    $excuse_id = validateInput($_POST['excuse_id'] ?? '', 'int', 0);
    
    if (!$excuse_id) {
        throw new Exception('Excuse ID is required');
    }
    
    // Check if excuse exists and is editable
    $check_query = "SELECT excuse_id, status FROM excuse_letters WHERE excuse_id = ? AND student_id = ? AND status = 'Pending'";
    $check_stmt = $con->prepare($check_query);
    $check_stmt->bind_param("ii", $excuse_id, $student_id);
    $check_stmt->execute();
    $existing_excuse = $check_stmt->get_result()->fetch_assoc();
    
    if (!$existing_excuse) {
        throw new Exception('Excuse letter not found, already processed, or access denied');
    }
    
    // Validate required fields
    $required_fields = ['event_id', 'excuse_type', 'reason', 'start_date'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }
    
    // Sanitize input data
    $event_id = validateInput($_POST['event_id'], 'int', 0);
    $excuse_type = validateInput($_POST['excuse_type'], 'string', '');
    $reason = validateInput($_POST['reason'], 'string', '');
    $start_date = validateInput($_POST['start_date'], 'string', '');
    $end_date = validateInput($_POST['end_date'] ?? '', 'string', '');
    $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;
    
    // Validate event exists
    $event_query = "SELECT event_id, title FROM events WHERE event_id = ?";
    $event_stmt = $con->prepare($event_query);
    $event_stmt->bind_param("i", $event_id);
    $event_stmt->execute();
    $event = $event_stmt->get_result()->fetch_assoc();
    
    if (!$event) {
        throw new Exception('Selected event does not exist');
    }
    
    // Validate date range
    if ($end_date && $start_date > $end_date) {
        throw new Exception('End date must be after or equal to start date');
    }
    
    // Check for duplicate excuse for the same event (excluding current one)
    $duplicate_query = "SELECT excuse_id FROM excuse_letters WHERE student_id = ? AND event_id = ? AND status = 'Pending' AND excuse_id != ?";
    $duplicate_stmt = $con->prepare($duplicate_query);
    $duplicate_stmt->bind_param("iii", $student_id, $event_id, $excuse_id);
    $duplicate_stmt->execute();
    
    if ($duplicate_stmt->get_result()->num_rows > 0) {
        throw new Exception('You already have a pending excuse letter for this event');
    }
    
    // Handle new file uploads
    $uploaded_files = [];
    if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {
        $upload_dir = __DIR__ . "/../../uploads/excuse_letters/";
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_types = [
            'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov'
        ];
        $max_file_size = 10 * 1024 * 1024; // 10MB
        
        for ($i = 0; $i < count($_FILES['documents']['name']); $i++) {
            if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['documents']['name'][$i];
                $file_size = $_FILES['documents']['size'][$i];
                $file_tmp = $_FILES['documents']['tmp_name'][$i];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Validate file type
                if (!in_array($file_ext, $allowed_types)) {
                    throw new Exception("File type '{$file_ext}' is not allowed");
                }
                
                // Validate file size
                if ($file_size > $max_file_size) {
                    throw new Exception("File '{$file_name}' is too large. Maximum size is 10MB");
                }
                
                // Generate unique filename
                $unique_filename = uniqid() . '_' . time() . '.' . $file_ext;
                $upload_path = $upload_dir . $unique_filename;
                
                // Move uploaded file
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $uploaded_files[] = [
                        'original_name' => $file_name,
                        'stored_name' => $unique_filename,
                        'file_path' => $upload_path,
                        'file_size' => $file_size,
                        'file_type' => $file_ext
                    ];
                } else {
                    throw new Exception("Failed to upload file '{$file_name}'");
                }
            }
        }
    }
    
    // Update excuse letter in database
    $update_query = "UPDATE excuse_letters SET 
        event_id = ?, excuse_type = ?, reason = ?, start_date = ?, end_date = ?, 
        is_urgent = ?, updated_at = NOW()
        WHERE excuse_id = ? AND student_id = ? AND status = 'Pending'";
    
    $update_stmt = $con->prepare($update_query);
    $update_stmt->bind_param("issssiii", $event_id, $excuse_type, $reason, $start_date, $end_date, $is_urgent, $excuse_id, $student_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update excuse letter: ' . $update_stmt->error);
    }
    
    // Save new uploaded files information
    if (!empty($uploaded_files)) {
        $file_insert_query = "INSERT INTO excuse_documents (excuse_id, original_name, stored_name, file_path, file_size, file_type, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $file_stmt = $con->prepare($file_insert_query);
        
        foreach ($uploaded_files as $file) {
            $file_stmt->bind_param("isssis", $excuse_id, $file['original_name'], $file['stored_name'], $file['file_path'], $file['file_size'], $file['file_type']);
            if (!$file_stmt->execute()) {
                // Log error but don't fail the entire operation
                error_log("Failed to save file info: " . $file_stmt->error);
            }
        }
    }
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Excuse letter updated successfully'
    ], JSON_UNESCAPED_UNICODE);
    exit;
    
} catch (Exception $e) {
    error_log("Error updating excuse letter: " . $e->getMessage());
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Return error response
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>