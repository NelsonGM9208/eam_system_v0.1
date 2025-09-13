<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Start session for AJAX calls
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

if (!isset($_GET['id'])) {
    echo "<div class='modal-body'><p class='text-danger'>No event ID provided.</p></div>";
    exit;
}

$eventId = intval($_GET['id']);

// Verify that the event belongs to the current SSLG user
$current_user_id = $_SESSION['user_id'] ?? null;

// Debug session information
error_log("Edit Event Form - Session Debug:");
error_log("user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("email: " . ($_SESSION['email'] ?? 'NOT SET'));
error_log("role: " . ($_SESSION['role'] ?? 'NOT SET'));
error_log("session_id: " . session_id());

if (!$current_user_id) {
    echo "<div class='modal-body'><p class='text-danger'>User not authenticated. Session ID: " . session_id() . "</p></div>";
    exit;
}

// Fetch event details and verify ownership
$query = "SELECT * FROM events WHERE event_id = ? AND created_by = ?";
$stmt = $con->prepare($query);
if (!$stmt) {
    echo "<div class='modal-body'><p class='text-danger'>Database error: " . $con->error . "</p></div>";
    exit;
}

$stmt->bind_param("ii", $eventId, $current_user_id);
if (!$stmt->execute()) {
    echo "<div class='modal-body'><p class='text-danger'>Database error: " . $stmt->error . "</p></div>";
    exit;
}

$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    echo "<div class='modal-body'><p class='text-danger'>Event not found or you don't have permission to edit it.</p></div>";
    exit;
}

// Check if event is still pending (can only edit pending events)
if ($event['approval_status'] !== 'Pending') {
    echo "<div class='modal-body'><p class='text-danger'>This event has already been approved and cannot be edited.</p></div>";
    exit;
}

// Format dates for input fields
$event_date = formatInputDate($event['event_date']);
$start_time = formatInputTime($event['start_time']);
$end_time = formatInputTime($event['end_time']);

// Function to automatically determine event status based on date and time
function getEventStatus($event_date, $start_time, $end_time) {
    $now = new DateTime();
    $event_start = new DateTime($event_date . ' ' . $start_time);
    $event_end = new DateTime($event_date . ' ' . $end_time);
    
    if ($now < $event_start) {
        return 'Upcoming';
    } elseif ($now >= $event_start && $now <= $event_end) {
        return 'Ongoing';
    } else {
        return 'Finished';
    }
}

// Get the automatically calculated status
$auto_status = getEventStatus($event_date, $start_time, $end_time);

// Fetch all available sections
$sections_query = "SELECT section_id, grade, section FROM section ORDER BY grade, section";
$sections_result = mysqli_query($con, $sections_query);
$all_sections = [];
if ($sections_result) {
    while ($section = mysqli_fetch_assoc($sections_result)) {
        $all_sections[] = $section;
    }
}

// Fetch selected sections for this event
$selected_sections = [];
if ($event['event_type'] == 'Exclusive') {
    $selected_query = "SELECT section_id FROM event_section WHERE event_id = ?";
    $selected_stmt = $con->prepare($selected_query);
    if ($selected_stmt) {
        $selected_stmt->bind_param("i", $eventId);
        if ($selected_stmt->execute()) {
            $selected_result = $selected_stmt->get_result();
            while ($row = $selected_result->fetch_assoc()) {
                $selected_sections[] = $row['section_id'];
            }
        }
        $selected_stmt->close();
    }
}
?>

<form id="editEventForm">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?php echo $event['event_id']; ?>">
    
    <div class="row">
      <div class="col-md-6 col-sm-12 mb-3">
        <div class="form-group">
          <label for="editEventTitle">Event Title *</label>
          <input type="text" class="form-control" id="editEventTitle" name="title" 
                 value="<?php echo htmlspecialchars($event['title']); ?>" required>
        </div>
      </div>
      <div class="col-md-6 col-sm-12 mb-3">
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
      <div class="col-md-6 col-sm-12 mb-3">
        <div class="form-group">
          <label for="editEventDate">Event Date *</label>
          <input type="date" class="form-control" id="editEventDate" name="event_date" 
                 value="<?php echo $event_date; ?>" required>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 mb-3">
        <div class="form-group">
          <label for="editStartTime">Start Time *</label>
          <input type="time" class="form-control" id="editStartTime" name="start_time" 
                 value="<?php echo $start_time; ?>" required>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 mb-3">
        <div class="form-group">
          <label for="editEndTime">End Time *</label>
          <input type="time" class="form-control" id="editEndTime" name="end_time" 
                 value="<?php echo $end_time; ?>" required>
        </div>
      </div>
    </div>
    
    <div class="form-group">
      <label for="editEventLocation">Location *</label>
      <input type="text" class="form-control" id="editEventLocation" name="location" 
             value="<?php echo htmlspecialchars($event['location']); ?>" required>
    </div>
    
    <div class="form-group">
      <label for="editAbsPenalty">Absence Penalty</label>
      <input type="number" class="form-control" id="editAbsPenalty" name="abs_penalty" 
             min="0" step="0.01" value="<?php echo $event['abs_penalty'] ?? 0; ?>" 
             placeholder="Penalty amount in pesos (₱)">
    </div>
    
    <!-- Section Selection for Exclusive Events -->
    <div class="form-group" id="editSectionSelectionGroup" style="display: none; border: 2px solid #007bff; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
      <label class="font-weight-bold text-primary">
        <i class="bx bx-group"></i> Select Sections for Exclusive Event
      </label>
      <small class="form-text text-muted mb-3">
        Choose which sections can participate in this exclusive event.
      </small>
      <div class="row" id="editSectionCheckboxes">
        <?php if (!empty($all_sections)): ?>
          <?php foreach ($all_sections as $section): ?>
            <div class="col-md-6 col-lg-4 mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" 
                       class="custom-control-input section-checkbox" 
                       id="edit_section_<?php echo $section['section_id']; ?>" 
                       name="selected_classes[]" 
                       value="<?php echo $section['section_id']; ?>"
                       <?php echo in_array($section['section_id'], $selected_sections) ? 'checked' : ''; ?>>
                <label class="custom-control-label" for="edit_section_<?php echo $section['section_id']; ?>">
                  <i class="bx bx-group text-primary"></i>
                  <?php echo htmlspecialchars($section['grade'] . ' - ' . $section['section']); ?>
                </label>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-info">
              <i class="bx bx-info-circle"></i> No sections available. Please add sections first.
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Status Display (Read-only for SSLG) -->
    <div class="form-group">
      <label>Current Status</label>
      <div class="alert alert-info">
        <i class="bx bx-info-circle"></i>
        <strong>Event Status:</strong> <span class="badge badge-<?php echo $auto_status == 'Upcoming' ? 'warning' : ($auto_status == 'Ongoing' ? 'info' : 'success'); ?>"><?php echo $auto_status; ?></span><br>
        <strong>Approval Status:</strong> <span class="badge badge-warning">Pending</span><br>
        <small class="text-muted">Status is automatically calculated based on event date and time. Approval status will be updated by administrators.</small>
      </div>
    </div>
    
    <!-- Modal Footer with Save/Cancel Buttons -->
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-dismiss="modal">
        <i class="bx bx-x me-1"></i>Cancel
      </button>
      <button type="submit" class="btn btn-warning">
        <i class="bx bx-save me-1"></i>Save Changes
      </button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Show/hide section selection based on event type
    function toggleSectionSelection() {
        const eventType = $('#editEventType').val();
        if (eventType === 'Exclusive') {
            $('#editSectionSelectionGroup').show();
        } else {
            $('#editSectionSelectionGroup').hide();
            // Uncheck all section checkboxes when not exclusive
            $('.section-checkbox').prop('checked', false);
        }
    }
    
    // Initial check
    toggleSectionSelection();
    
    // Toggle on event type change
    $('#editEventType').on('change', toggleSectionSelection);
    
    // Form submission
    $('#editEventForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate date (cannot be in the past)
        const eventDate = $('#editEventDate').val();
        const today = new Date().toISOString().split('T')[0];

        if (eventDate < today) {
            alert('Cannot create events for past dates. Please select today or a future date.');
            $('#editEventDate').focus();
            return;
        }
        
        // Validate time
        const startTime = $('#editStartTime').val();
        const endTime = $('#editEndTime').val();
        
        if (startTime >= endTime) {
            alert('End time must be after start time');
            $('#editEndTime').focus();
            return;
        }
        
        // Validate exclusive event has at least one section selected
        const eventType = $('#editEventType').val();
        if (eventType === 'Exclusive') {
            const selectedSections = $('.section-checkbox:checked').length;
            if (selectedSections === 0) {
                alert('Please select at least one section for exclusive events.');
                return;
            }
        }
        
        // Show confirmation
        const eventTitle = $('#editEventTitle').val();
        if (!confirm(`Are you sure you want to update "${eventTitle}"?`)) {
            return;
        }
        
        // Disable submit button to prevent double submission
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Saving...');
        
        // Prepare form data
        let formData = $(this).serialize();
        
        // Add selected classes as JSON if exclusive
        if (eventType === 'Exclusive') {
            const selectedClasses = [];
            $('.section-checkbox:checked').each(function () {
                selectedClasses.push($(this).val());
            });
            formData += '&selected_classes=' + encodeURIComponent(JSON.stringify(selectedClasses));
        }
        
        // Debug: Log form data being sent
        console.log('Form data being sent:', formData);
        
        $.post('/eam_system_v0.1.1/config/events_crud.php', formData)
            .done(function(response) {
                if (response.includes('successfully')) {
                    alert('Event updated successfully!');
                    $('#editEventModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response);
                    submitBtn.prop('disabled', false).html(originalText);
                }
            })
            .fail(function() {
                alert('Failed to update event. Please try again.');
                submitBtn.prop('disabled', false).html(originalText);
            });
    });
});
</script>
