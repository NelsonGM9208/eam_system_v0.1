<?php
require "../../../config/database.php";

if (!isset($_GET['id'])) {
    echo "<div class='modal-body'><p class='text-danger'>No event ID provided.</p></div>";
    exit;
}

$eventId = intval($_GET['id']);

// Fetch event details
$query = "SELECT * FROM events WHERE event_id = ?";
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

// Format dates for input fields
$event_date = date('Y-m-d', strtotime($event['event_date']));
$start_time = date('H:i', strtotime($event['start_time']));
$end_time = date('H:i', strtotime($event['end_time']));
?>

<form id="editEventForm">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?php echo $event['event_id']; ?>">
    
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label for="editEventTitle">Event Title *</label>
          <input type="text" class="form-control" id="editEventTitle" name="title" 
                 value="<?php echo htmlspecialchars($event['title']); ?>" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label for="editEventType">Event Type *</label>
          <select class="form-control" id="editEventType" name="event_type" required>
            <option value="">Select Type</option>
            <option value="Open" <?php echo ($event['event_type'] == 'Open') ? 'selected' : ''; ?>>Open</option>
            <option value="Exclusive" <?php echo ($event['event_type'] == 'Exclusive') ? 'selected' : ''; ?>>Exclusive</option>
          </select>
        </div>
      </div>
    </div>
    
    <div class="form-group">
      <label for="editEventDescription">Description</label>
      <textarea class="form-control" id="editEventDescription" name="event_description" rows="3"><?php echo htmlspecialchars($event['event_description'] ?? ''); ?></textarea>
    </div>
    
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label for="editEventDate">Event Date *</label>
          <input type="date" class="form-control" id="editEventDate" name="event_date" 
                 value="<?php echo $event_date; ?>" required>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label for="editStartTime">Start Time *</label>
          <input type="time" class="form-control" id="editStartTime" name="start_time" 
                 value="<?php echo $start_time; ?>" required>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label for="editEndTime">End Time *</label>
          <input type="time" class="form-control" id="editEndTime" name="end_time" 
                 value="<?php echo $end_time; ?>" required>
        </div>
      </div>
    </div>
    
    <div class="row">
      <div class="col-md-8">
        <div class="form-group">
          <label for="editEventLocation">Location *</label>
          <input type="text" class="form-control" id="editEventLocation" name="location" 
                 value="<?php echo htmlspecialchars($event['location']); ?>" required>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label for="editEventStatus">Status *</label>
          <select class="form-control" id="editEventStatus" name="event_status" required>
            <option value="">Select Status</option>
            <option value="Upcoming" <?php echo ($event['event_status'] == 'Upcoming') ? 'selected' : ''; ?>>Upcoming</option>
            <option value="Ongoing" <?php echo ($event['event_status'] == 'Ongoing') ? 'selected' : ''; ?>>Ongoing</option>
            <option value="Finished" <?php echo ($event['event_status'] == 'Finished') ? 'selected' : ''; ?>>Finished</option>
          </select>
        </div>
      </div>
    </div>
    
    <div class="form-group">
      <label for="editAbsPenalty">Absence Penalty</label>
      <input type="number" class="form-control" id="editAbsPenalty" name="abs_penalty" 
             min="0" step="0.01" value="<?php echo $event['abs_penalty'] ?? 0; ?>" 
             placeholder="Penalty amount in pesos (₱)">
    </div>
    
    <!-- Class Selection for Exclusive Events -->
    <div class="form-group" id="editClassSelectionGroup" style="display: none;">
      <label>Select Classes for Exclusive Event</label>
      <div class="row" id="editClassCheckboxes">
        <!-- Classes will be loaded dynamically -->
      </div>
    </div>
</form>

<script>
$(document).ready(function() {
    // Form submission
    $('#editEventForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate time
        const startTime = $('#editStartTime').val();
        const endTime = $('#editEndTime').val();
        
        if (startTime >= endTime) {
            alert('End time must be after start time');
            $('#editEndTime').focus();
            return;
        }
        
        $.post('../includes/admin/events_crud.php', $(this).serialize(), function(response) {
            alert(response);
            if(response.includes('successfully')) {
                $('#editEventModal').modal('hide');
                location.reload();
            }
        });
    });
});
</script>
