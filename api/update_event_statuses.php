<?php
/**
 * API Endpoint: Update Event Statuses
 * 
 * This endpoint can be called via HTTP to manually trigger event status updates.
 * Useful for webhooks, external monitoring, or manual triggers.
 * 
 * Usage:
 * GET /api/update_event_statuses.php
 * POST /api/update_event_statuses.php
 */

// Set JSON response header
header('Content-Type: application/json');

// Basic security check (optional - you can add authentication here)
// For now, we'll allow any request, but you might want to add API key authentication

try {
    // Include the event status updater
    require_once __DIR__ . "/../utils/event_status_updater.php";
    
    // Update all event statuses
    $results = updateAllEventStatuses();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'updated' => $results['updated'],
        'errors' => $results['errors'],
        'details' => $results['details']
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $e->getMessage()
    ]);
}
?>
