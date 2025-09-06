<?php
require "../../../config/database.php";

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
$event_date = date('F d, Y (l)', strtotime($event['event_date']));
$start_time = date('h:i A', strtotime($event['start_time']));
$end_time = date('h:i A', strtotime($event['end_time']));
$created_at = date('F d, Y h:i A', strtotime($event['created_at']));
$updated_at = !empty($event['updated_at']) ? date('F d, Y h:i A', strtotime($event['updated_at'])) : 'N/A';

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
