<?php
/**
 * Student Events Management
 * View available events and register for them
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
    header('Location: /eam_system_v0.1.1/index.php');
    exit;
}

require_once __DIR__ . "/../../utils/index.php";
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
                ORDER BY e.event_date DESC, e.start_time DESC";

$stmt = $con->prepare($events_query);
$stmt->bind_param("ii", $student_id, $student_section_id);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get filter parameters
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? 'Upcoming'; // Default to Upcoming
$search = $_GET['search'] ?? '';

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
?>

<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-white rounded-circle p-3 mr-3 shadow-sm">
                                    <i class="bx bx-calendar-event text-info" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h3 class="text-white mb-1 font-weight-bold">Available Events</h3>
                                    <p class="text-white-50 mb-0">View and register for events you can attend</p>
                                </div>
                            </div>
                            <?php if ($student_section): ?>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-group text-white mr-2"></i>
                                    <span class="text-white">
                                        <strong>Your Section:</strong> Grade <?php echo htmlspecialchars($student_section['grade']); ?> - <?php echo htmlspecialchars($student_section['section']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-md-right mt-3 mt-md-0">
                            <div class="bg-white rounded-lg p-3 shadow-sm d-inline-block">
                                <div class="text-center">
                                    <div class="text-info mb-1">
                                        <i class="bx bx-list-ul" style="font-size: 1.2rem;"></i>
                                    </div>
                                    <div class="text-muted small mb-1">Total Events</div>
                                    <div class="h4 text-info mb-0 font-weight-bold"><?php echo count($filtered_events); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <div class="row align-items-end" id="filterControls">
                        <div class="col-md-4">
                            <label for="search" class="form-label small mb-1">Search Events</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by title, description, or location...">
                        </div>
                        <div class="col-md-3">
                            <label for="type" class="form-label small mb-1">Event Type</label>
                            <select class="form-control form-control-sm" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="Open" <?php echo $type_filter === 'Open' ? 'selected' : ''; ?>>Open Events</option>
                                <option value="Exclusive" <?php echo $type_filter === 'Exclusive' ? 'selected' : ''; ?>>Exclusive Events</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label small mb-1">Event Status</label>
                            <select class="form-control form-control-sm" id="status" name="status">
                                <option value="Upcoming" <?php echo $status_filter === 'Upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="Ongoing" <?php echo $status_filter === 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="Finished" <?php echo $status_filter === 'Finished' ? 'selected' : ''; ?>>Finished</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="clearFilters" style="flex: 1;">
                                    <i class="bx bx-x"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Events Table -->
    <div id="eventsContainer">
        <?php if (empty($filtered_events)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bx bx-calendar-x text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-muted">No Events Found</h4>
                    <p class="text-muted">
                        <?php if ($search || $type_filter || $status_filter !== 'Upcoming'): ?>
                            No events match your current filters. Try adjusting your search criteria.
                        <?php else: ?>
                            There are no upcoming events available for you at the moment.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
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
                            <tbody>
                                <?php foreach ($filtered_events as $event): ?>
                                    <tr class="border-bottom">
                                        <td class="align-middle">
                                            <div>
                                                <h6 class="mb-1 text-dark"><?php echo htmlspecialchars($event['title']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars(substr($event['event_description'], 0, 80)); ?>
                                                    <?php if (strlen($event['event_description']) > 80): ?>...<?php endif; ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div>
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="bx bx-calendar text-info mr-2"></i>
                                                    <small class="text-dark"><?php echo date('M j, Y', strtotime($event['event_date'])); ?></small>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i class="bx bx-time text-info mr-2"></i>
                                                    <small class="text-muted"><?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-map text-info mr-2"></i>
                                                <small class="text-muted"><?php echo htmlspecialchars($event['location']); ?></small>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge <?php echo $event['event_type'] === 'Exclusive' ? 'badge-danger' : 'badge-primary'; ?>">
                                                <?php echo htmlspecialchars($event['event_type']); ?>
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge <?php echo $event['event_status'] === 'Upcoming' ? 'badge-info' : ($event['event_status'] === 'Ongoing' ? 'badge-success' : 'badge-secondary'); ?>">
                                                <?php echo htmlspecialchars($event['event_status']); ?>
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-<?php echo $event['registration_status'] === 'Registered' ? 'success' : 'secondary'; ?>">
                                                <i class="bx bx-<?php echo $event['registration_status'] === 'Registered' ? 'check' : 'x'; ?> mr-1"></i>
                                                <?php echo $event['registration_status']; ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewEventDetails(<?php echo $event['event_id']; ?>)" title="View Details">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                                <?php if ($event['event_status'] === 'Upcoming'): ?>
                                                    <button type="button" class="btn btn-sm btn-info" onclick="registerForEvent(<?php echo $event['event_id']; ?>)" title="Register for Event">
                                                        <i class="bx bx-user-plus"></i>
                                                    </button>
                                                <?php elseif ($event['registration_status'] === 'Registered' && $event['event_status'] === 'Ongoing'): ?>
                                                    <button type="button" class="btn btn-sm btn-warning" onclick="checkInEvent(<?php echo $event['event_id']; ?>)" title="Check In to Event">
                                                        <i class="bx bx-qr-scan"></i>
                                                    </button>
                                                <?php elseif ($event['event_status'] === 'Finished'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Event Finished">
                                                        <i class="bx bx-calendar-check"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Registration Not Available">
                                                        <i class="bx bx-time"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Event Modals -->
<?php include __DIR__ . "/modals/event_details.php"; ?>
<?php include __DIR__ . "/modals/event_registration.php"; ?>

<!-- Include Event Styles -->
<link rel="stylesheet" href="/eam_system_v0.1.1/assets/css/student_events.css">

<!-- Initialize events page when loaded -->
<script>
// Initialize the events page when this content is loaded
if (typeof initializeEventsPage === 'function') {
    initializeEventsPage();
} else {
    console.log('initializeEventsPage function not available yet');
}
</script>