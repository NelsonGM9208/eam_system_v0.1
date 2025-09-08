<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

// Fetch all available sections for exclusive events
$sections_query = "SELECT section_id, grade, section FROM section ORDER BY grade, section";
$sections_result = mysqli_query($con, $sections_query);
$all_sections = [];
if ($sections_result) {
    while ($section = mysqli_fetch_assoc($sections_result)) {
        $all_sections[] = $section;
    }
}

// Fetch pending events created by current SSLG user
$current_user_id = $_SESSION['user_id'] ?? null;
$pending_events = [];
if ($current_user_id) {
    $pending_query = "SELECT e.*, 
                      CONCAT(u.firstname, ' ', u.lastname) as creator_name,
                      u.role as creator_role
                      FROM events e 
                      LEFT JOIN users u ON e.created_by = u.user_id 
                      WHERE e.created_by = ? AND e.event_status = 'Pending'
                      ORDER BY e.created_at DESC";
    $stmt = mysqli_prepare($con, $pending_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $current_user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($event = mysqli_fetch_assoc($result)) {
            $pending_events[] = $event;
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="container-fluid">
    <!-- Add Event Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="bx bx-calendar-plus me-2" style="font-size: 1.5rem;"></i>
                    <h4 class="mb-0">Add New Event</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5 class="alert-heading">SSLG Event Creation</h5>
                        <p>As an SSLG member, you can create and manage school events. Fill out the form below to add a
                            new event.</p>
                    </div>

                    <form id="addEventForm">
                        <input type="hidden" name="action" value="add">

                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label for="eventTitle">Event Title *</label>
                                    <input type="text" class="form-control" id="eventTitle" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label for="eventType">Event Type *</label>
                                    <select class="form-control" id="eventType" name="event_type" required>
                                        <option value="">Select Type</option>
                                        <option value="Open">Open</option>
                                        <option value="Exclusive">Exclusive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="eventDescription">Description</label>
                            <textarea class="form-control" id="eventDescription" name="event_description"
                                rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label for="eventDate">Event Date *</label>
                                    <input type="date" class="form-control" id="eventDate" name="event_date" required>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="form-group">
                                    <label for="startTime">Start Time *</label>
                                    <input type="time" class="form-control" id="startTime" name="start_time" required>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="form-group">
                                    <label for="endTime">End Time *</label>
                                    <input type="time" class="form-control" id="endTime" name="end_time" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="eventLocation">Location *</label>
                            <input type="text" class="form-control" id="eventLocation" name="location" required>
                        </div>

                        <div class="form-group">
                            <label for="absPenalty">Absence Penalty</label>
                            <input type="number" class="form-control" id="absPenalty" name="abs_penalty" min="0"
                                step="0.01" placeholder="Penalty amount in pesos (₱)">
                        </div>

                        <!-- Section Selection for Exclusive Events -->
                        <div class="form-group" id="sectionSelectionGroup" style="display: none; border: 2px solid #007bff; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                            <label class="font-weight-bold text-primary">
                                <i class="bx bx-group"></i> Select Sections for Exclusive Event
                            </label>
                            <small class="form-text text-muted mb-3">
                                Choose which sections can participate in this exclusive event.
                            </small>
                            <div class="row" id="sectionCheckboxes">
                                <?php if (!empty($all_sections)): ?>
                                    <?php foreach ($all_sections as $section): ?>
                                        <div class="col-md-6 col-lg-4 mb-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input section-checkbox"
                                                    id="section_<?php echo $section['section_id']; ?>" name="selected_classes[]"
                                                    value="<?php echo $section['section_id']; ?>">
                                                <label class="custom-control-label"
                                                    for="section_<?php echo $section['section_id']; ?>">
                                                    <i class="bx bx-group text-primary"></i>
                                                    <?php echo htmlspecialchars($section['grade'] . ' - ' . $section['section']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle"></i> No sections available. Please add sections
                                            first.
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-plus"></i> Create Event
                            </button>
                            <button type="reset" class="btn btn-secondary ml-2">
                                <i class="bx bx-refresh"></i> Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<!-- Pending Events Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning text-dark d-flex align-items-center">
                <i class="bx bx-time me-2" style="font-size: 1.5rem;"></i>
                <h4 class="mb-0">My Pending Events</h4>
                <span class="badge badge-light ml-auto"><?php echo count($pending_events); ?> events</span>
            </div>
            <div class="card-body">
                <?php if (!empty($pending_events)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Event Title</th>
                                    <th>Type</th>
                                    <th>Date & Time</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_events as $event): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                            <?php if ($event['event_description']): ?>
                                                <br><small
                                                    class="text-muted"><?php echo htmlspecialchars(substr($event['event_description'], 0, 50)) . (strlen($event['event_description']) > 50 ? '...' : ''); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-<?php echo $event['event_type'] == 'Exclusive' ? 'info' : 'primary'; ?>">
                                                <?php echo htmlspecialchars($event['event_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="bx bx-calendar text-primary"></i>
                                            <?php echo date('M j, Y', strtotime($event['event_date'])); ?><br>
                                            <i class="bx bx-time text-secondary"></i>
                                            <?php echo date('g:i A', strtotime($event['start_time'])); ?> -
                                            <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['location']); ?></td>
                                        <td>
                                            <span
                                                class="badge badge-warning"><?php echo htmlspecialchars($event['event_status']); ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y g:i A', strtotime($event['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-info btn-sm view-event-btn"
                                                    data-event-id="<?php echo $event['event_id']; ?>" title="View Details">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                                <button type="button" class="btn btn-warning btn-sm edit-event-btn"
                                                    data-event-id="<?php echo $event['event_id']; ?>" title="Edit Event">
                                                    <i class="bx bx-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bx bx-calendar-x" style="font-size: 3rem; color: #6c757d;"></i>
                        <h5 class="mt-3 text-muted">No Pending Events</h5>
                        <p class="text-muted">You haven't created any events yet. Use the form above to create your first
                            event.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    // Wait for jQuery to be available
    function initSSLGAddEvents() {
        if (typeof $ === 'undefined') {
            console.log('jQuery not available, retrying in 100ms...');
            setTimeout(initSSLGAddEvents, 100);
            return;
        }
        
        console.log('jQuery available, initializing SSLG add events...');
        
        $(document).ready(function () {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            $('#eventDate').attr('min', today);

        // Debug: Check if elements exist
        console.log('Event type select exists:', $('#eventType').length > 0);
        console.log('Section selection group exists:', $('#sectionSelectionGroup').length > 0);
        console.log('Section checkboxes count:', $('.section-checkbox').length);

        // Show/hide section selection based on event type
        $('#eventType').on('change', function () {
            const eventType = $(this).val();
            console.log('Event type changed to:', eventType);
            console.log('Section selection group element:', $('#sectionSelectionGroup'));
            
            if (eventType === 'Exclusive') {
                console.log('Showing section selection group');
                $('#sectionSelectionGroup').show();
                console.log('Section selection group visibility after show:', $('#sectionSelectionGroup').is(':visible'));
            } else {
                console.log('Hiding section selection group');
                $('#sectionSelectionGroup').hide();
                // Uncheck all section checkboxes when not exclusive
                $('.section-checkbox').prop('checked', false);
            }
        });

        // Form submission
        $('#addEventForm').on('submit', function (e) {
            e.preventDefault();

            // Validate date (cannot be in the past)
            const eventDate = $('#eventDate').val();
            const today = new Date().toISOString().split('T')[0];

            if (eventDate < today) {
                alert('Cannot create events for past dates. Please select today or a future date.');
                $('#eventDate').focus();
                return;
            }

            // Validate time
            const startTime = $('#startTime').val();
            const endTime = $('#endTime').val();

            if (startTime >= endTime) {
                alert('Start time must be earlier than end time.');
                $('#endTime').focus();
                return;
            }

            // Validate exclusive events have sections selected
            const eventType = $('#eventType').val();
            if (eventType === 'Exclusive') {
                const selectedSections = $('.section-checkbox:checked').length;
                if (selectedSections === 0) {
                    alert('Please select at least one section for exclusive events.');
                    return;
                }
            }

            // Confirmation is handled by the modal, no need for JavaScript confirm
            {
                const formData = $(this).serialize();

                // Add selected classes as JSON if exclusive
                if (eventType === 'Exclusive') {
                    const selectedClasses = [];
                    $('.section-checkbox:checked').each(function () {
                        selectedClasses.push($(this).val());
                    });
                    formData += '&selected_classes=' + encodeURIComponent(JSON.stringify(selectedClasses));
                }

                $.post('/eam_system_v0.1.1/config/events_crud.php', formData)
                    .done(function (response) {
                        if (response.includes('successfully')) {
                            alert(response);
                            location.reload();
                        } else {
                            alert('Error: ' + response);
                        }
                    })
                    .fail(function () {
                        alert('Failed to create event. Please try again.');
                    });
            }
        });
        });
    }
    
    // Initialize the function
    initSSLGAddEvents();
</script>