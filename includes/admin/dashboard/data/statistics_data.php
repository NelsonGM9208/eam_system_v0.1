<?php
/**
 * Admin Dashboard Statistics Data
 * Centralized data fetching for admin dashboard statistics
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../../../utils/index.php";

// Get database connection
$con = getDatabaseConnection();

/**
 * Get comprehensive statistics for admin dashboard
 */
function getAdminStatistics($con) {
    $stats = [
        // User Statistics
        'total_users' => 0,
        'active_users' => 0,
        'pending_users' => 0,
        'deactivated_users' => 0,
        
        // Event Statistics
        'total_events' => 0,
        'approved_events' => 0,
        'pending_events' => 0,
        'rejected_events' => 0,
        'upcoming_events' => 0,
        'ongoing_events' => 0,
        'finished_events' => 0,
        
        // System Statistics
        'total_classes' => 0,
        'total_attendance' => 0,
        'total_logs' => 0,
        'today_logs' => 0,
        
        // Recent Activity Counts
        'today_registrations' => 0,
        'today_events' => 0,
        'today_attendance' => 0
    ];
    
    try {
        // User Statistics
        $user_queries = [
            'total_users' => "SELECT COUNT(*) as total FROM users",
            'active_users' => "SELECT COUNT(*) as total FROM users WHERE account_status = 'active' OR account_status IS NULL",
            'pending_users' => "SELECT COUNT(*) as total FROM users WHERE account_status = 'pending'",
            'deactivated_users' => "SELECT COUNT(*) as total FROM users WHERE account_status = 'deactivated'"
        ];
        
        foreach ($user_queries as $key => $query) {
            $result = mysqli_query($con, $query);
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                $stats[$key] = $row['total'] ?? 0;
            }
        }
        
        // Event Statistics
        $event_queries = [
            'total_events' => "SELECT COUNT(*) as total FROM events",
            'approved_events' => "SELECT COUNT(*) as total FROM events WHERE approval_status = 'Approved'",
            'pending_events' => "SELECT COUNT(*) as total FROM events WHERE approval_status = 'Pending'",
            'rejected_events' => "SELECT COUNT(*) as total FROM events WHERE approval_status = 'Rejected'"
        ];
        
        foreach ($event_queries as $key => $query) {
            $result = mysqli_query($con, $query);
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                $stats[$key] = $row['total'] ?? 0;
            }
        }
        
        // Event Status Statistics
        $status_query = "SELECT event_status, COUNT(*) as total FROM events WHERE approval_status = 'Approved' GROUP BY event_status";
        $result = mysqli_query($con, $status_query);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                switch($row['event_status']) {
                    case 'Upcoming':
                        $stats['upcoming_events'] = $row['total'];
                        break;
                    case 'Ongoing':
                        $stats['ongoing_events'] = $row['total'];
                        break;
                    case 'Finished':
                        $stats['finished_events'] = $row['total'];
                        break;
                }
            }
        }
        
        // System Statistics
        $system_queries = [
            'total_classes' => "SELECT COUNT(*) as total FROM section",
            'total_attendance' => "SELECT COUNT(*) as total FROM attendance",
            'total_logs' => "SELECT COUNT(*) as total FROM logs"
        ];
        
        foreach ($system_queries as $key => $query) {
            $result = mysqli_query($con, $query);
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                $stats[$key] = $row['total'] ?? 0;
            }
        }
        
        // Today's Statistics
        $today = getCurrentDateTime('Y-m-d');
        $today_queries = [
            'today_logs' => "SELECT COUNT(*) as total FROM logs WHERE DATE(created_at) = '$today'",
            'today_registrations' => "SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = '$today'",
            'today_events' => "SELECT COUNT(*) as total FROM events WHERE DATE(created_at) = '$today'",
            'today_attendance' => "SELECT COUNT(*) as total FROM attendance WHERE DATE(created_at) = '$today'"
        ];
        
        foreach ($today_queries as $key => $query) {
            $result = mysqli_query($con, $query);
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                $stats[$key] = $row['total'] ?? 0;
            }
        }
        
    } catch (Exception $e) {
        error_log("Error fetching admin statistics: " . $e->getMessage());
    }
    
    return $stats;
}

/**
 * Get recent activities for admin dashboard
 */
function getRecentActivities($con, $limit = 10) {
    $activities = [];
    
    try {
        // Recent user registrations
        $users_query = "SELECT user_id, firstname, lastname, email, role, created_at, account_status 
                        FROM users 
                        ORDER BY created_at DESC 
                        LIMIT $limit";
        $result = mysqli_query($con, $users_query);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $activities[] = [
                    'type' => 'user_registration',
                    'title' => 'New User Registration',
                    'description' => $row['firstname'] . ' ' . $row['lastname'] . ' (' . $row['role'] . ')',
                    'status' => $row['account_status'] ?? 'active',
                    'timestamp' => $row['created_at'],
                    'icon' => 'bx-user-plus',
                    'color' => $row['account_status'] == 'pending' ? 'warning' : 'success'
                ];
            }
        }
        
        // Recent event submissions
        $events_query = "SELECT e.event_id, e.title, e.approval_status, e.created_at, 
                                CONCAT(u.firstname, ' ', u.lastname) as creator_name
                         FROM events e 
                         LEFT JOIN users u ON e.created_by = u.user_id 
                         ORDER BY e.created_at DESC 
                         LIMIT $limit";
        $result = mysqli_query($con, $events_query);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $activities[] = [
                    'type' => 'event_submission',
                    'title' => 'Event Submission',
                    'description' => $row['title'] . ' by ' . $row['creator_name'],
                    'status' => $row['approval_status'],
                    'timestamp' => $row['created_at'],
                    'icon' => 'bx-calendar-plus',
                    'color' => $row['approval_status'] == 'Pending' ? 'warning' : 
                              ($row['approval_status'] == 'Approved' ? 'success' : 'danger')
                ];
            }
        }
        
        // Sort activities by timestamp
        usort($activities, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        // Return only the most recent activities
        return array_slice($activities, 0, $limit);
        
    } catch (Exception $e) {
        error_log("Error fetching recent activities: " . $e->getMessage());
        return [];
    }
}

/**
 * Get pending items for admin dashboard
 */
function getPendingItems($con) {
    $pending = [
        'pending_users' => [],
        'pending_events' => []
    ];
    
    try {
        // Pending user approvals
        $users_query = "SELECT user_id, firstname, lastname, email, role, created_at 
                        FROM users 
                        WHERE account_status = 'pending' 
                        ORDER BY created_at ASC 
                        LIMIT 5";
        $result = mysqli_query($con, $users_query);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $pending['pending_users'][] = $row;
            }
        }
        
        // Pending event approvals
        $events_query = "SELECT e.event_id, e.title, e.event_date, e.created_at,
                                CONCAT(u.firstname, ' ', u.lastname) as creator_name
                         FROM events e 
                         LEFT JOIN users u ON e.created_by = u.user_id 
                         WHERE e.approval_status = 'Pending' 
                         ORDER BY e.created_at ASC 
                         LIMIT 5";
        $result = mysqli_query($con, $events_query);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $pending['pending_events'][] = $row;
            }
        }
        
    } catch (Exception $e) {
        error_log("Error fetching pending items: " . $e->getMessage());
    }
    
    return $pending;
}

// Get all data for dashboard
$admin_stats = getAdminStatistics($con);
$recent_activities = getRecentActivities($con, 8);
$pending_items = getPendingItems($con);

// Debug statistics
error_log("Admin Statistics Debug:");
error_log("Total Users: " . $admin_stats['total_users']);
error_log("Pending Users: " . $admin_stats['pending_users']);
error_log("Total Events: " . $admin_stats['total_events']);
error_log("Pending Events: " . $admin_stats['pending_events']);
error_log("Pending Items - Users: " . count($pending_items['pending_users']));
error_log("Pending Items - Events: " . count($pending_items['pending_events']));
?>
