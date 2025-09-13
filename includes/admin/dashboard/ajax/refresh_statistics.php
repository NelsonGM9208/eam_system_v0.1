<?php
/**
 * AJAX endpoint for refreshing admin dashboard statistics
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . "/../../../../utils/index.php";

// Get database connection
$con = getDatabaseConnection();

// Include statistics data functions
require_once __DIR__ . "/../data/statistics_data.php";

try {
    // Get fresh statistics
    $admin_stats = getAdminStatistics($con);
    $recent_activities = getRecentActivities($con, 8);
    $pending_items = getPendingItems($con);
    
    // Prepare response
    $response = [
        'success' => true,
        'statistics' => $admin_stats,
        'recent_activities' => $recent_activities,
        'pending_items' => $pending_items,
        'timestamp' => time()
    ];
    
    // Set JSON header
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error refreshing statistics: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to refresh statistics',
        'message' => $e->getMessage()
    ]);
}
?>

