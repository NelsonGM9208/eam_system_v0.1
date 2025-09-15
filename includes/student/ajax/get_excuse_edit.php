<?php
/**
 * AJAX endpoint to get excuse letter data for editing
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
    $excuse_id = validateInput($_GET['excuse_id'] ?? '', 'int', 0);
    
    if (!$excuse_id) {
        throw new Exception('Excuse ID is required');
    }
    
    // Get excuse letter details
    $excuse_query = "SELECT * FROM excuse_letters WHERE excuse_id = ? AND student_id = ? AND status = 'Pending'";
    $excuse_stmt = $con->prepare($excuse_query);
    $excuse_stmt->bind_param("ii", $excuse_id, $student_id);
    $excuse_stmt->execute();
    $excuse = $excuse_stmt->get_result()->fetch_assoc();
    
    if (!$excuse) {
        throw new Exception('Excuse letter not found, already processed, or access denied');
    }
    
    // Get available events
    $events_query = "SELECT event_id, title, event_date, start_time, end_time, location 
                    FROM events 
                    WHERE event_status IN ('Upcoming', 'Ongoing', 'Finished')
                    AND event_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    ORDER BY event_date DESC, start_time DESC";
    
    $events_result = mysqli_query($con, $events_query);
    $available_events = [];
    if ($events_result) {
        while ($event = mysqli_fetch_assoc($events_result)) {
            $available_events[] = $event;
        }
    }
    
    // Get existing documents
    $documents_query = "SELECT * FROM excuse_documents WHERE excuse_id = ? ORDER BY uploaded_at ASC";
    $documents_stmt = $con->prepare($documents_query);
    $documents_stmt->bind_param("i", $excuse_id);
    $documents_stmt->execute();
    $documents = $documents_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Generate HTML form
    $html = '<div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="editExcuseEvent">Event <span class="text-danger">*</span></label>
                <select class="form-control" id="editExcuseEvent" name="event_id" required>
                    <option value="">Select an event</option>';
    
    foreach ($available_events as $event) {
        $selected = ($event['event_id'] == $excuse['event_id']) ? 'selected' : '';
        $html .= '<option value="' . $event['event_id'] . '" ' . $selected . '>
                    ' . htmlspecialchars($event['title']) . ' - 
                    ' . date('M j, Y', strtotime($event['event_date'])) . '
                  </option>';
    }
    
    $html .= '</select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="editExcuseType">Excuse Type <span class="text-danger">*</span></label>
                <select class="form-control" id="editExcuseType" name="excuse_type" required>
                    <option value="">Select excuse type</option>
                    <option value="Medical"' . ($excuse['excuse_type'] === 'Medical' ? ' selected' : '') . '>Medical</option>
                    <option value="Family Emergency"' . ($excuse['excuse_type'] === 'Family Emergency' ? ' selected' : '') . '>Family Emergency</option>
                    <option value="Academic"' . ($excuse['excuse_type'] === 'Academic' ? ' selected' : '') . '>Academic</option>
                    <option value="Personal"' . ($excuse['excuse_type'] === 'Personal' ? ' selected' : '') . '>Personal</option>
                    <option value="Other"' . ($excuse['excuse_type'] === 'Other' ? ' selected' : '') . '>Other</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="editExcuseReason">Reason for Absence <span class="text-danger">*</span></label>
        <textarea class="form-control" id="editExcuseReason" name="reason" rows="4" required>' . htmlspecialchars($excuse['reason']) . '</textarea>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="editExcuseStartDate">Start Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="editExcuseStartDate" name="start_date" value="' . $excuse['start_date'] . '" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="editExcuseEndDate">End Date</label>
                <input type="date" class="form-control" id="editExcuseEndDate" name="end_date" value="' . $excuse['end_date'] . '">
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="editExcuseDocuments">Additional Documents</label>
        <div class="custom-file">
            <input type="file" class="custom-file-input" id="editExcuseDocuments" name="documents[]" multiple 
                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.mp4,.avi,.mov">
            <label class="custom-file-label" for="editExcuseDocuments">Choose additional files...</label>
        </div>
        <small class="form-text text-muted">
            You can add more supporting documents. Existing documents will be preserved.
        </small>
    </div>';
    
    // Show existing documents
    if (!empty($documents)) {
        $html .= '<div class="form-group">
            <label>Current Documents</label>
            <div class="row">';
        
        foreach ($documents as $doc) {
            $file_icon = '';
            $file_type = strtolower($doc['file_type']);
            
            if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                $file_icon = '<i class="bx bx-image text-success"></i>';
            } elseif (in_array($file_type, ['mp4', 'avi', 'mov'])) {
                $file_icon = '<i class="bx bx-video text-info"></i>';
            } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                $file_icon = '<i class="bx bx-file-blank text-primary"></i>';
            } else {
                $file_icon = '<i class="bx bx-file text-secondary"></i>';
            }
            
            $file_size = formatFileSize($doc['file_size']);
            $file_path = '/eam_system_v0.1.1/includes/uploads/excuse_letters/' . $doc['stored_name'];
            
            $html .= '<div class="col-md-6 mb-2">
                <div class="d-flex align-items-center p-2 border rounded">
                    <div class="mr-3" style="font-size: 1.5rem;">' . $file_icon . '</div>
                    <div class="flex-grow-1">
                        <div class="font-weight-bold">' . htmlspecialchars($doc['original_name']) . '</div>
                        <div class="text-muted small">' . $file_size . '</div>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" onclick="previewDocument(\'' . $file_path . '\', \'' . htmlspecialchars($doc['original_name']) . '\')">
                            <i class="bx bx-show"></i>
                        </button>
                        <a href="' . $file_path . '" class="btn btn-outline-success" download>
                            <i class="bx bx-download"></i>
                        </a>
                    </div>
                </div>
            </div>';
        }
        
        $html .= '</div>
        </div>';
    }
    
    $html .= '<div class="form-group">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="editExcuseUrgent" name="is_urgent"' . ($excuse['is_urgent'] ? ' checked' : '') . '>
            <label class="form-check-label" for="editExcuseUrgent">
                Mark as urgent
            </label>
        </div>
    </div>';
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'html' => $html
    ], JSON_UNESCAPED_UNICODE);
    exit;
    
} catch (Exception $e) {
    error_log("Error getting excuse edit data: " . $e->getMessage());
    
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

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>
