<?php
/**
 * Event Status Auto-Updater
 * Automatically updates event statuses based on current date and time
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/index.php";

/**
 * Automatically update all event statuses based on current date and time
 * @return array Results of the update operation
 */
function updateAllEventStatuses() {
    $con = getDatabaseConnection();
    $results = [
        'updated' => 0,
        'errors' => [],
        'details' => []
    ];
    
    try {
        // Get all events
        $query = "SELECT event_id, title, event_date, start_time, end_time, event_status FROM events";
        $result = mysqli_query($con, $query);
        
        if (!$result) {
            throw new Exception("Failed to fetch events: " . mysqli_error($con));
        }
        
        $now = new DateTime();
        
        while ($event = mysqli_fetch_assoc($result)) {
            $event_id = $event['event_id'];
            $current_status = $event['event_status'];
            
            // Calculate the correct status
            $event_start = new DateTime($event['event_date'] . ' ' . $event['start_time']);
            $event_end = new DateTime($event['event_date'] . ' ' . $event['end_time']);
            
            $new_status = '';
            if ($now < $event_start) {
                $new_status = 'Upcoming';
            } elseif ($now >= $event_start && $now <= $event_end) {
                $new_status = 'Ongoing';
            } else {
                $new_status = 'Finished';
            }
            
            // Only update if status has changed
            if ($current_status !== $new_status) {
                $update_query = "UPDATE events SET event_status = ?, updated_at = NOW() WHERE event_id = ?";
                $stmt = $con->prepare($update_query);
                
                if ($stmt) {
                    $stmt->bind_param("si", $new_status, $event_id);
                    if ($stmt->execute()) {
                        $results['updated']++;
                        $results['details'][] = [
                            'event_id' => $event_id,
                            'title' => $event['title'],
                            'old_status' => $current_status,
                            'new_status' => $new_status
                        ];
                    } else {
                        $results['errors'][] = "Failed to update event {$event_id}: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $results['errors'][] = "Failed to prepare statement for event {$event_id}: " . $con->error;
                }
            }
        }
        
    } catch (Exception $e) {
        $results['errors'][] = $e->getMessage();
    }
    
    return $results;
}

// Function getEventStatus() is already defined in utils/date_utils.php

// If this file is called directly or via AJAX, update all event statuses
if (basename($_SERVER['PHP_SELF']) === 'event_status_updater.php' || 
    ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST))) {
    
    $results = updateAllEventStatuses();
    
    // Ensure we return proper JSON
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode($results, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
