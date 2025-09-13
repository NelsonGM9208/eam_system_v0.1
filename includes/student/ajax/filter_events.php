<?php
/**
 * AJAX endpoint for filtering student events
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
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

require_once __DIR__ . "/../../../utils/index.php";
$con = getDatabaseConnection();

// Get current student info
$student_id = $_SESSION['user_id'];

// Get student's section
$student_section_query = "SELECT s.section_id, s.grade, s.section 
                         FROM enrollment e 
                         JOIN section s ON e.section_id = s.section_id 
                         WHERE e.student_id = ? AND e.status = 'Active'";
$stmt = $con->prepare($student_section_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_section = $stmt->get_result()->fetch_assoc();

$student_section_id = $student_section['section_id'] ?? null;

// Get filter parameters
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? 'Upcoming';
$search = $_GET['search'] ?? '';

// Get events that student can access (pre-filtered)
$events_query = "SELECT DISTINCT e.*, 
                CASE 
                    WHEN a.attendance_id IS NOT NULL THEN 'Registered'
                    ELSE 'Not Registered'
                END as registration_status
                FROM events e
                LEFT JOIN event_section es ON e.event_id = es.event_id
                LEFT JOIN attendance a ON e.event_id = a.event_id AND a.student_id = ?
                WHERE e.approval_status = 'Approved'
                AND (e.event_type = 'Open' OR (e.event_type = 'Exclusive' AND es.section_id = ?))
                ORDER BY e.event_date ASC, e.start_time ASC";

$stmt = $con->prepare($events_query);
$stmt->bind_param("ii", $student_id, $student_section_id);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Filter events based on parameters
$filtered_events = $events;

if ($type_filter) {
    $filtered_events = array_filter($filtered_events, function($event) use ($type_filter) {
        return $event['event_type'] === $type_filter;
    });
}

if ($status_filter) {
    $filtered_events = array_filter($filtered_events, function($event) use ($status_filter) {
        return $event['event_status'] === $status_filter;
    });
}

if ($search) {
    $filtered_events = array_filter($filtered_events, function($event) use ($search) {
        return stripos($event['title'], $search) !== false || 
               stripos($event['event_description'], $search) !== false ||
               stripos($event['location'], $search) !== false;
    });
}

// Generate HTML for events
$html = '';
if (empty($filtered_events)) {
    $html = '
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bx bx-calendar-x text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-muted">No Events Found</h4>
            <p class="text-muted">
                ' . ($search || $type_filter || $status_filter !== 'Upcoming' ? 
                    'No events match your current filters. Try adjusting your search criteria.' : 
                    'There are no upcoming events available for you at the moment.') . '
            </p>
        </div>
    </div>';
} else {
    $html = '
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bx bx-list-ul mr-2"></i>
                Events List
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0">Event Details</th>
                            <th class="border-0">Date & Time</th>
                            <th class="border-0">Location</th>
                            <th class="border-0">Type</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Registration</th>
                            <th class="border-0 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($filtered_events as $event) {
        $badge_class = $event['event_type'] === 'Exclusive' ? 'badge-danger' : 'badge-primary';
        $status_badge_class = $event['event_status'] === 'Upcoming' ? 'badge-info' : 
                             ($event['event_status'] === 'Ongoing' ? 'badge-success' : 'badge-secondary');
        
        $html .= '
                        <tr class="border-bottom">
                            <td class="align-middle">
                                <div>
                                    <h6 class="mb-1 text-dark">' . htmlspecialchars($event['title']) . '</h6>
                                    <small class="text-muted">
                                        ' . htmlspecialchars(substr($event['event_description'], 0, 80)) . 
                                        (strlen($event['event_description']) > 80 ? '...' : '') . '
                                    </small>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bx bx-calendar text-info mr-2"></i>
                                        <small class="text-dark">' . date('M j, Y', strtotime($event['event_date'])) . '</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-time text-info mr-2"></i>
                                        <small class="text-muted">' . date('g:i A', strtotime($event['start_time'])) . ' - ' . date('g:i A', strtotime($event['end_time'])) . '</small>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-map text-info mr-2"></i>
                                    <small class="text-muted">' . htmlspecialchars($event['location']) . '</small>
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="badge ' . $badge_class . '">
                                    ' . htmlspecialchars($event['event_type']) . '
                                </span>
                            </td>
                            <td class="align-middle">
                                <span class="badge ' . $status_badge_class . '">
                                    ' . htmlspecialchars($event['event_status']) . '
                                </span>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-' . ($event['registration_status'] === 'Registered' ? 'success' : 'secondary') . '">
                                    <i class="bx bx-' . ($event['registration_status'] === 'Registered' ? 'check' : 'x') . ' mr-1"></i>
                                    ' . $event['registration_status'] . '
                                </span>
                            </td>
                             <td class="align-middle text-center">
                                 <div class="btn-group" role="group">
                                     <button type="button" class="btn btn-sm btn-outline-info" onclick="viewEventDetails(' . $event['event_id'] . ')" title="View Details">
                                         <i class="bx bx-show"></i>
                                     </button>';
        
        if ($event['event_status'] === 'Upcoming') {
            $html .= '
                                     <button type="button" class="btn btn-sm btn-info" onclick="registerForEvent(' . $event['event_id'] . ')" title="Register for Event">
                                         <i class="bx bx-user-plus"></i>
                                     </button>';
        } elseif ($event['registration_status'] === 'Registered' && $event['event_status'] === 'Ongoing') {
            $html .= '
                                     <button type="button" class="btn btn-sm btn-warning" onclick="checkInEvent(' . $event['event_id'] . ')" title="Check In to Event">
                                         <i class="bx bx-qr-scan"></i>
                                     </button>';
        } elseif ($event['event_status'] === 'Finished') {
            $html .= '
                                     <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Event Finished">
                                         <i class="bx bx-calendar-check"></i>
                                     </button>';
        } else {
            $html .= '
                                     <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Registration Not Available">
                                         <i class="bx bx-time"></i>
                                     </button>';
        }
        
        $html .= '
                                </div>
                            </td>
                        </tr>';
    }
    
    $html .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'html' => $html,
    'count' => count($filtered_events),
    'updateHeader' => true
]);
?>
