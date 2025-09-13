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
$event_date = formatInputDate($event['event_date']);
$start_time = formatInputTime($event['start_time']);
$end_time = formatInputTime($event['end_time']);

// Function getEventStatus() is already defined in utils/date_utils.php

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
    
    <div class="row">
      <div class="col-md-8 col-sm-12 mb-3">
        <div class="form-group">
          <label for="editEventLocation">Location *</label>
          <input type="text" class="form-control" id="editEventLocation" name="location" 
                 value="<?php echo htmlspecialchars($event['location']); ?>" required>
        </div>
      </div>
      <div class="col-md-4 col-sm-12 mb-3">
        <div class="form-group">
          <label for="editEventStatus">Status *</label>
          <select class="form-control" id="editEventStatus" name="event_status" required disabled>
            <option value="Upcoming" <?php echo ($auto_status == 'Upcoming') ? 'selected' : ''; ?>>Upcoming</option>
            <option value="Ongoing" <?php echo ($auto_status == 'Ongoing') ? 'selected' : ''; ?>>Ongoing</option>
            <option value="Finished" <?php echo ($auto_status == 'Finished') ? 'selected' : ''; ?>>Finished</option>
          </select>
          <small class="form-text text-info">
            <i class="bx bx-info-circle"></i> Status is automatically calculated based on event date and time
          </small>
        </div>
      </div>
    </div>
    
    <div class="form-group">
      <label for="editAbsPenalty">Absence Penalty</label>
      <input type="number" class="form-control" id="editAbsPenalty" name="abs_penalty" 
             min="0" step="0.01" value="<?php echo $event['abs_penalty'] ?? 0; ?>" 
             placeholder="Penalty amount in pesos (₱)">
    </div>
    
    <!-- Section Selection for Exclusive Events -->
    <div class="form-group" id="editSectionSelectionGroup" style="display: none;">
      <label class="font-weight-bold">
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
    
    <!-- Modal Footer with Save/Cancel Buttons -->
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      <button type="submit" class="btn btn-primary" form="editEventForm">Save Changes</button>
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
    
    // Function to automatically update event status based on date and time
    function updateEventStatus() {
        const eventDate = $('#editEventDate').val();
        const startTime = $('#editStartTime').val();
        const endTime = $('#editEndTime').val();
        
        if (eventDate && startTime && endTime) {
            const now = new Date();
            const eventStart = new Date(eventDate + ' ' + startTime);
            const eventEnd = new Date(eventDate + ' ' + endTime);
            
            let status;
            if (now < eventStart) {
                status = 'Upcoming';
            } else if (now >= eventStart && now <= eventEnd) {
                status = 'Ongoing';
            } else {
                status = 'Finished';
            }
            
            // Update the status select (even though it's disabled, we need to set the value for form submission)
            $('#editEventStatus').val(status);
            
            // Update the visual indicator
            const statusText = $('#editEventStatus option:selected').text();
            console.log('Event status automatically updated to:', status);
        }
    }
    
    // Update status when date or time fields change
    $('#editEventDate, #editStartTime, #editEndTime').on('change', updateEventStatus);
    
    // Initial status update
    updateEventStatus();
    
    // Form submission
    $('#editEventForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get event title for confirmation
        const eventTitle = $('#editEventTitle').val();
        
        // Show confirmation dialog
        // Confirmation is handled by the modal, no need for JavaScript confirm
        
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
        
        // Ensure the status is included in the form data (disabled fields don't submit by default)
        const formData = $(this).serializeArray();
        formData.push({
            name: 'event_status',
            value: $('#editEventStatus').val()
        });
        
        // Convert to URL-encoded string
        const serializedData = $.param(formData);
        
        $.post('/eam_system_v0.1.1/config/events_crud.php', serializedData, function(response) {
            alert(response);
            if(response.includes('successfully')) {
                $('#editEventModal').modal('hide');
                location.reload();
            }
        });
    });
});
</script>
