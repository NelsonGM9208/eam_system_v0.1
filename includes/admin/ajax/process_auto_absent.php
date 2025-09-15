<?php
/**
 * AJAX handler for auto-absent processing
 */

// Start output buffering to prevent unwanted output
ob_start();

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
    $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : null;
    $force = isset($_POST['force']) ? (bool)$_POST['force'] : false;
    
    // Debug logging
    error_log("Auto-absent AJAX called with action: $action, eventId: $eventId, force: " . ($force ? 'true' : 'false'));
    
    // Check if AutoAbsentProcessor class exists
    if (!class_exists('AutoAbsentProcessor')) {
        throw new Exception('AutoAbsentProcessor class not found');
    }
    
    // Check if database schema is updated
    $con = getDatabaseConnection();
    $result = mysqli_query($con, "SHOW COLUMNS FROM events LIKE 'grace_period_hours'");
    if (mysqli_num_rows($result) == 0) {
        throw new Exception('Database schema not updated. Please run the SQL script to add grace_period_hours and auto_absent_processed columns.');
    }
    
    $processor = new AutoAbsentProcessor();
    
    switch ($action) {
        case 'process_all':
            $results = $processor->processAllEvents($force);
            ob_clean(); // Clear any previous output
            echo json_encode([
                'success' => true,
                'message' => 'Auto-absent processing completed',
                'data' => $results
            ]);
            break;
            
        case 'process_event':
            if (!$eventId) {
                throw new Exception('Event ID is required');
            }
            
            $result = $processor->processEvent($eventId, $force);
            
            if (isset($result['error'])) {
                ob_clean(); // Clear any previous output
                echo json_encode([
                    'success' => false,
                    'message' => $result['error']
                ]);
            } else {
                ob_clean(); // Clear any previous output
                echo json_encode([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result
                ]);
            }
            break;
            
        case 'get_ready_events':
            $events = $processor->getEventsReadyForProcessing();
            ob_clean(); // Clear any previous output
            echo json_encode([
                'success' => true,
                'data' => $events
            ]);
            break;
            
        case 'check_grace_period':
            if (!$eventId) {
                throw new Exception('Event ID is required');
            }
            
            $event = $processor->getEventDetails($eventId);
            if (!$event) {
                throw new Exception('Event not found');
            }
            
            $gracePeriodInfo = $processor->getGracePeriodInfo($event);
            ob_clean(); // Clear any previous output
            echo json_encode([
                'success' => true,
                'grace_period_info' => $gracePeriodInfo
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    error_log('Auto-absent processing error: ' . $e->getMessage());
    http_response_code(500);
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>
