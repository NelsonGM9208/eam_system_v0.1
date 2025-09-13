<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

if (!isset($_GET['id'])) {
    echo "<div class='modal-body'><p class='text-danger'>No event ID provided.</p></div>";
    exit;
}

$eventId = intval($_GET['id']);

// Fetch event details with creator information
$query = "SELECT e.*, 
          CONCAT(u.firstname, ' ', u.lastname) as creator_name,
          u.role as creator_role
          FROM events e 
          LEFT JOIN users u ON e.created_by = u.user_id 
          WHERE e.event_id = ?";
$stmt = $con->prepare($query);
if (!$stmt) {
    echo "<div class='modal-body'><p class='text-danger'>Database error: " . $con->error . "</p></div>";
    exit;
}

$stmt->bind_param("i", $eventId);
if (!$stmt->execute()) {
    echo "<div class='modal-body'><p class='text-danger'>Database error: " . $stmt->error . "</p></div>";
    exit;
}

$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    echo "<div class='modal-body'><p class='text-danger'>Event not found.</p></div>";
    exit;
}

// Format dates and times
$event_date = formatEventDateWithDay($event['event_date']);
$start_time = formatDisplayTime($event['start_time']);
$end_time = formatDisplayTime($event['end_time']);
$created_at = formatDisplayDate($event['created_at'], 'F d, Y h:i A');
$updated_at = !empty($event['updated_at']) ? formatDisplayDate($event['updated_at'], 'F d, Y h:i A') : 'N/A';

// Status badge colors
$status_badge = '';
switch($event['event_status']) {
    case 'Upcoming':
        $status_badge = 'badge-warning';
        break;
    case 'Ongoing':
        $status_badge = 'badge-info';
        break;
    case 'Finished':
        $status_badge = 'badge-success';
        break;
    default:
        $status_badge = 'badge-secondary';
}

// Type badge colors
$type_badge = '';
switch($event['event_type']) {
    case 'General':
        $type_badge = 'badge-primary';
        break;
    case 'Exclusive':
        $type_badge = 'badge-info';
        break;
    default:
        $type_badge = 'badge-secondary';
}

// Approval badge colors
$approval_badge = '';
switch($event['approval_status']) {
    case 'Approved':
        $approval_badge = 'badge-success';
        break;
    case 'Pending':
        $approval_badge = 'badge-warning';
        break;
    case 'Rejected':
        $approval_badge = 'badge-danger';
        break;
    default:
        $approval_badge = 'badge-secondary';
}
?>

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title">
        <i class="bx bx-calendar-event"></i> Event Details
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-md-8">
            <h4 class="text-primary mb-3"><?php echo htmlspecialchars($event['title']); ?></h4>
            
            <div class="mb-3">
                <h6><i class="bx bx-info-circle text-primary"></i> Description</h6>
                <p class="text-muted"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="bx bx-calendar text-primary"></i> Event Date</h6>
                    <p class="text-muted"><?php echo $event_date; ?></p>
                </div>
                <div class="col-md-6">
                    <h6><i class="bx bx-time text-primary"></i> Time</h6>
                    <p class="text-muted"><?php echo $start_time; ?> - <?php echo $end_time; ?></p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="bx bx-map text-primary"></i> Location</h6>
                    <p class="text-muted"><?php echo htmlspecialchars($event['location']); ?></p>
                </div>
                <div class="col-md-6">
                    <h6><i class="bx bx-user text-primary"></i> Created By</h6>
                    <p class="text-muted"><?php echo htmlspecialchars($event['creator_name']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">Event Information</h6>
                    
                    <div class="mb-2">
                        <strong>Status:</strong>
                        <span class="badge <?php echo $status_badge; ?> ml-1">
                            <?php echo htmlspecialchars($event['event_status']); ?>
                        </span>
                    </div>
                    
                    <div class="mb-2">
                        <strong>Type:</strong>
                        <span class="badge <?php echo $type_badge; ?> ml-1">
                            <?php echo htmlspecialchars($event['event_type']); ?>
                        </span>
                    </div>
                    
                    <div class="mb-2">
                        <strong>Approval:</strong>
                        <span class="badge <?php echo $approval_badge; ?> ml-1">
                            <?php echo htmlspecialchars($event['approval_status']); ?>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-2">
                        <small class="text-muted">
                            <strong>Created:</strong><br>
                            <?php echo $created_at; ?>
                        </small>
                    </div>
                    
                    <?php if ($updated_at !== 'N/A'): ?>
                    <div class="mb-2">
                        <small class="text-muted">
                            <strong>Updated:</strong><br>
                            <?php echo $updated_at; ?>
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    <?php if ($event['event_status'] === 'Upcoming'): ?>
    <button type="button" class="btn btn-primary" onclick="addToCalendar('<?php echo $event['title']; ?>', '<?php echo $event['event_date']; ?>', '<?php echo $event['start_time']; ?>', '<?php echo $event['end_time']; ?>', '<?php echo $event['location']; ?>')">
        <i class="bx bx-calendar-plus"></i> Add to Calendar
    </button>
    <?php endif; ?>
</div>

<script>
function addToCalendar(title, date, startTime, endTime, location) {
    // Create calendar event URL
    const startDateTime = new Date(date + ' ' + startTime);
    const endDateTime = new Date(date + ' ' + endTime);
    
    const startISO = startDateTime.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
    const endISO = endDateTime.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
    
    const calendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(title)}&dates=${startISO}/${endISO}&location=${encodeURIComponent(location)}&details=${encodeURIComponent('Event from EAM System')}`;
    
    window.open(calendarUrl, '_blank');
}
</script>
