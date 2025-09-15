<?php
/**
 * AJAX endpoint to get excuse letter details
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
    $excuse_query = "SELECT el.*, 
                    CONCAT(u.firstname, ' ', u.lastname) as student_name,
                    u.email as student_email,
                    e.title as event_title,
                    e.event_date,
                    e.start_time,
                    e.end_time,
                    e.location
                    FROM excuse_letters el
                    LEFT JOIN users u ON el.student_id = u.user_id
                    LEFT JOIN events e ON el.event_id = e.event_id
                    WHERE el.excuse_id = ? AND el.student_id = ?";
    
    $excuse_stmt = $con->prepare($excuse_query);
    $excuse_stmt->bind_param("ii", $excuse_id, $student_id);
    $excuse_stmt->execute();
    $excuse = $excuse_stmt->get_result()->fetch_assoc();
    
    if (!$excuse) {
        throw new Exception('Excuse letter not found or access denied');
    }
    
    // Get uploaded documents
    $documents_query = "SELECT * FROM excuse_documents WHERE excuse_id = ? ORDER BY uploaded_at ASC";
    $documents_stmt = $con->prepare($documents_query);
    $documents_stmt->bind_param("i", $excuse_id);
    $documents_stmt->execute();
    $documents = $documents_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Generate HTML content
    $status_badge = '';
    $status_icon = '';
    switch ($excuse['status']) {
        case 'Pending':
            $status_badge = 'badge-warning';
            $status_icon = 'bx-time';
            break;
        case 'Approved':
            $status_badge = 'badge-success';
            $status_icon = 'bx-check-circle';
            break;
        case 'Rejected':
            $status_badge = 'badge-danger';
            $status_icon = 'bx-x-circle';
            break;
        default:
            $status_badge = 'badge-secondary';
            $status_icon = 'bx-question-mark';
    }
    
    $html = '<div class="row">
        <div class="col-md-6">
            <h6 class="text-muted mb-3">Excuse Information</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Event:</strong></td>
                    <td>' . htmlspecialchars($excuse['event_title']) . '</td>
                </tr>
                <tr>
                    <td><strong>Date:</strong></td>
                    <td>' . ($excuse['event_date'] ? date('M j, Y', strtotime($excuse['event_date'])) : 'N/A') . '</td>
                </tr>
                <tr>
                    <td><strong>Time:</strong></td>
                    <td>';
    
    if ($excuse['start_time'] && $excuse['end_time']) {
        $html .= date('g:i A', strtotime($excuse['start_time'])) . ' - ' . date('g:i A', strtotime($excuse['end_time']));
    } else {
        $html .= 'N/A';
    }
    
    $html .= '</td>
                </tr>
                <tr>
                    <td><strong>Location:</strong></td>
                    <td>' . htmlspecialchars($excuse['location'] ?? 'N/A') . '</td>
                </tr>
                <tr>
                    <td><strong>Excuse Type:</strong></td>
                    <td>' . htmlspecialchars($excuse['excuse_type']) . '</td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td><span class="badge ' . $status_badge . '"><i class="bx ' . $status_icon . '"></i> ' . $excuse['status'] . '</span></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="text-muted mb-3">Absence Details</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Start Date:</strong></td>
                    <td>' . date('M j, Y', strtotime($excuse['start_date'])) . '</td>
                </tr>
                <tr>
                    <td><strong>End Date:</strong></td>
                    <td>' . ($excuse['end_date'] ? date('M j, Y', strtotime($excuse['end_date'])) : 'Single day') . '</td>
                </tr>
                <tr>
                    <td><strong>Urgent:</strong></td>
                    <td>' . ($excuse['is_urgent'] ? '<span class="badge badge-danger">Yes</span>' : '<span class="badge badge-secondary">No</span>') . '</td>
                </tr>
                <tr>
                    <td><strong>Submitted:</strong></td>
                    <td>' . date('M j, Y g:i A', strtotime($excuse['created_at'])) . '</td>
                </tr>';
    
    if ($excuse['updated_at'] && $excuse['updated_at'] !== $excuse['created_at']) {
        $html .= '<tr>
                    <td><strong>Last Updated:</strong></td>
                    <td>' . date('M j, Y g:i A', strtotime($excuse['updated_at'])) . '</td>
                </tr>';
    }
    
    $html .= '</table>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <h6 class="text-muted mb-3">Reason for Absence</h6>
            <div class="alert alert-light">
                ' . nl2br(htmlspecialchars($excuse['reason'])) . '
            </div>
        </div>
    </div>';
    
    // Add documents section if there are any
    if (!empty($documents)) {
        $html .= '<div class="row mt-4">
            <div class="col-12">
                <h6 class="text-muted mb-3">Supporting Documents</h6>
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
            
            $html .= '<div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-2" style="font-size: 2rem;">' . $file_icon . '</div>
                        <h6 class="card-title">' . htmlspecialchars($doc['original_name']) . '</h6>
                        <p class="card-text text-muted small">' . $file_size . '</p>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" onclick="previewDocument(\'' . $file_path . '\', \'' . htmlspecialchars($doc['original_name']) . '\')">
                                <i class="bx bx-show"></i> Preview
                            </button>
                            <a href="' . $file_path . '" class="btn btn-outline-success" download>
                                <i class="bx bx-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>';
        }
        
        $html .= '</div>
            </div>
        </div>';
    }
    
    // Add admin response if available
    if ($excuse['admin_response']) {
        $html .= '<div class="row mt-4">
            <div class="col-12">
                <h6 class="text-muted mb-3">Administrator Response</h6>
                <div class="alert alert-info">
                    ' . nl2br(htmlspecialchars($excuse['admin_response'])) . '
                </div>
            </div>
        </div>';
    }
    
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
    error_log("Error getting excuse details: " . $e->getMessage());
    
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
