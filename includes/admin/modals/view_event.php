<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../../utils/index.php";

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
$type_badge = ($event['event_type'] == 'Exclusive') ? 'badge-danger' : 'badge-primary';

// Fetch sections for exclusive events
$sections = [];
if ($event['event_type'] == 'Exclusive') {
    $sections_query = "SELECT s.section_id, s.grade, s.section 
                       FROM section s 
                       INNER JOIN event_section es ON s.section_id = es.section_id 
                       WHERE es.event_id = ? 
                       ORDER BY s.grade, s.section";
    $sections_stmt = $con->prepare($sections_query);
    if ($sections_stmt) {
        $sections_stmt->bind_param("i", $eventId);
        if ($sections_stmt->execute()) {
            $sections_result = $sections_stmt->get_result();
            while ($section = $sections_result->fetch_assoc()) {
                $sections[] = $section;
            }
        }
        $sections_stmt->close();
    }
}
?>

<div class="modal-body">
  <div class="row">
    <div class="col-md-8">
      <table class="table table-bordered">
        <tr>
          <th width="30%">Event ID</th>
          <td><?php echo htmlspecialchars($event['event_id']); ?></td>
        </tr>
        <tr>
          <th>Event Title</th>
          <td><strong><?php echo htmlspecialchars($event['title']); ?></strong></td>
        </tr>
        <tr>
          <th>Description</th>
          <td><?php echo nl2br(htmlspecialchars($event['event_description'] ?? 'No description provided')); ?></td>
        </tr>
        <tr>
          <th>Event Date</th>
          <td><?php echo $event_date; ?></td>
        </tr>
        <tr>
          <th>Time</th>
          <td><?php echo $start_time . ' - ' . $end_time; ?></td>
        </tr>
        <tr>
          <th>Location</th>
          <td><?php echo htmlspecialchars($event['location']); ?></td>
        </tr>
        <tr>
          <th>Event Type</th>
          <td>
            <span class="badge <?php echo $type_badge; ?>">
              <?php echo htmlspecialchars($event['event_type']); ?>
            </span>
          </td>
        </tr>
        <?php if ($event['event_type'] == 'Exclusive'): ?>
        <tr>
          <th>Included Sections</th>
          <td>
            <?php if (!empty($sections)): ?>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach ($sections as $section): ?>
                  <span class="badge badge-info">
                    <i class="bx bx-group"></i> <?php echo htmlspecialchars($section['grade'] . ' - ' . $section['section']); ?>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <span class="text-muted">
                <i class="bx bx-info-circle"></i> No sections assigned yet
              </span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <th>Status</th>
          <td>
            <span class="badge <?php echo $status_badge; ?>">
              <?php echo htmlspecialchars($event['event_status']); ?>
            </span>
          </td>
        </tr>
        <tr>
          <th>Absence Penalty</th>
          <td>
            <?php if ($event['abs_penalty'] > 0): ?>
              <span class="text-danger">₱<?php echo number_format($event['abs_penalty'], 2); ?></span>
            <?php else: ?>
              <span class="text-muted">No penalty</span>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <th>Created At</th>
          <td><?php echo $created_at; ?></td>
        </tr>
        <tr>
          <th>Last Updated</th>
          <td><?php echo $updated_at; ?></td>
        </tr>
      </table>
      
      <?php if (!empty($event['updated_at']) && $event['updated_at'] !== 'N/A'): ?>
        <div class="mt-3">
          <small class="text-muted">
            <i class="bx bx-time"></i> Last updated by <strong><?php 
              if (!empty($event['updated_by']) && $event['updated_by'] != 0) {
                // Get admin name from users table using updated_by
                $adminStmt = $con->prepare("SELECT firstname, lastname FROM users WHERE user_id = ?");
                if ($adminStmt) {
                  $adminStmt->bind_param("i", $event['updated_by']);
                  if ($adminStmt->execute()) {
                    $adminResult = $adminStmt->get_result();
                    if ($adminRow = $adminResult->fetch_assoc()) {
                      echo htmlspecialchars($adminRow['firstname'] . ' ' . $adminRow['lastname']);
                    } else {
                      echo htmlspecialchars('Admin ID: ' . $event['updated_by']);
                    }
                  } else {
                    echo htmlspecialchars('Admin ID: ' . $event['updated_by']);
                  }
                  $adminStmt->close();
                } else {
                  echo htmlspecialchars('Admin ID: ' . $event['updated_by']);
                }
              } elseif ($event['updated_by'] == 0) {
                echo 'System (Initial)';
              } else {
                echo 'System';
              }
            ?></strong>
          </small>
        </div>
      <?php endif; ?>
    </div>
    
    <div class="col-md-4 text-center">
      <div class="event-icon-container mb-3">
        <div class="event-icon rounded-circle border d-flex align-items-center justify-content-center mx-auto"
             style="width: 120px; height: 120px; background-color: #f8f9fa; color: #6c757d;">
          <i class="bx bx-calendar-event" style="font-size: 3rem;"></i>
        </div>
      </div>
      
      <div class="event-summary">
        <h6 class="mb-2"><?php echo htmlspecialchars($event['title']); ?></h6>
        <div class="mb-2">
          <span class="badge <?php echo $type_badge; ?>"><?php echo htmlspecialchars($event['event_type']); ?></span>
        </div>
        <div class="mb-2">
          <span class="badge <?php echo $status_badge; ?>"><?php echo htmlspecialchars($event['event_status']); ?></span>
        </div>
        
        <div class="mt-3">
          <small class="text-muted">
            <i class="bx bx-user"></i> Created by<br>
            <strong><?php echo htmlspecialchars($event['creator_name'] ?? 'Unknown'); ?></strong><br>
            <span class="badge badge-secondary"><?php echo htmlspecialchars($event['creator_role'] ?? 'Unknown'); ?></span>
          </small>
        </div>
      </div>
    </div>
  </div>
</div>
