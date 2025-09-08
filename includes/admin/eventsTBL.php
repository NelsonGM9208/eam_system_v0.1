<?php
require_once __DIR__ . "/../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

// Check database connection
if (!isset($con) || !$con) {
    echo "<div class='alert alert-danger'>Database connection failed.</div>";
    exit;
}

// Fetch all available sections for the add event modal
$sections_query = "SELECT section_id, grade, section FROM section ORDER BY grade, section";
$sections_result = mysqli_query($con, $sections_query);
$all_sections = [];
if ($sections_result) {
    while ($section = mysqli_fetch_assoc($sections_result)) {
        $all_sections[] = $section;
    }
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// Get total number of events
$total_query = "SELECT COUNT(*) as total FROM events";
$total_result = mysqli_query($con, $total_query);
if (!$total_result) {
    echo "<div class='alert alert-danger'>Database error: " . $con->error . "</div>";
    exit;
}
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Ensure page is within valid range
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $records_per_page;
}

// Only run the query if there are events
if ($total_records > 0) {
    // Get events with pagination
    $query = "SELECT e.*, 
              CONCAT(u.firstname, ' ', u.lastname) as creator_name,
              u.role as creator_role
              FROM events e 
              LEFT JOIN users u ON e.created_by = u.user_id 
              ORDER BY e.created_at DESC 
              LIMIT ?, ?";

    $stmt = $con->prepare($query);
    if (!$stmt) {
        echo "<div class='alert alert-danger'>Database error: " . $con->error . "</div>";
        exit;
    }

    $stmt->bind_param("ii", $offset, $records_per_page);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = null;
}
?>

<div class="card mt-4 shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 d-flex align-items-center">
            <i class="bx bx-calendar-event mr-2"></i>
            Events Management
        </h5>
        <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#addEventModal">
            <i class="bx bx-plus"></i> Add New Event
        </button>
    </div>
    <div class="card-body">
        <!-- Search and Filter -->
        <div class="row mb-3">
            <div class="col-md-6 col-sm-12 mb-2">
                <div class="input-group">
                    <input type="text" class="form-control" id="eventSearch" placeholder="Search events...">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bx bx-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <select class="form-control" id="eventStatusFilter">
                    <option value="">All Status</option>
                    <option value="Upcoming">Upcoming</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Finished">Finished</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <select class="form-control" id="eventTypeFilter">
                    <option value="">All Types</option>
                    <option value="Open">Open</option>
                    <option value="Exclusive">Exclusive</option>
                </select>
            </div>
            <div class="col-md-1 col-sm-12 mb-2">
                <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="clearEventFilters">
                    <i class="bx bx-x"></i> Clear
                </button>
            </div>
        </div>

        <!-- Events Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="eventsTable">
                <thead class="thead-light">
                    <tr>
                        <th>Event Title</th>
                        <th>Description</th>
                        <th>Date & Time</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Approval</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($total_records > 0 && $result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $event_id = $row['event_id'];
                            $title = htmlspecialchars($row['title']);
                            $description = htmlspecialchars($row['event_description']);
                            $event_date = date('M d, Y', strtotime($row['event_date']));
                            $start_time = date('h:i A', strtotime($row['start_time']));
                            $end_time = date('h:i A', strtotime($row['end_time']));
                            $location = htmlspecialchars($row['location']);
                            $event_type = htmlspecialchars($row['event_type']);
                            $status = htmlspecialchars($row['event_status']);
                            $creator_name = htmlspecialchars($row['creator_name'] ?? 'Unknown');
                            $creator_role = htmlspecialchars($row['creator_role'] ?? 'Unknown');
                            
                            // Status badge colors
                            $status_badge = '';
                            switch($status) {
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
                            $type_badge = ($event_type == 'Exclusive') ? 'badge-danger' : 'badge-primary';
                            
                            // Approval status badge
                            $approval_status = $row['approval_status'] ?? 'Pending';
                            $approval_badge = '';
                            switch($approval_status) {
                                case 'Approved':
                                    $approval_badge = 'badge-success';
                                    break;
                                case 'Rejected':
                                    $approval_badge = 'badge-danger';
                                    break;
                                default:
                                    $approval_badge = 'badge-warning';
                                    break;
                            }
                            
                            echo "<tr data-event-id='$event_id'>
                                <td><strong>$title</strong></td>
                                <td>" . (strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description) . "</td>
                                <td>
                                    <div><strong>$event_date</strong></div>
                                    <small class='text-muted'>$start_time - $end_time</small>
                                </td>
                                <td>$location</td>
                                <td><span class='badge $type_badge'>$event_type</span></td>
                                <td><span class='badge $status_badge'>$status</span></td>
                                <td><span class='badge $approval_badge'>$approval_status</span></td>
                                <td>
                                    <div>$creator_name</div>
                                    <small class='text-muted'>$creator_role</small>
                                </td>
                                <td>
                                    <div class='btn-group' role='group'>
                                        <button type='button' class='btn btn-info btn-sm view-event-btn' 
                                                data-toggle='modal' data-target='#viewEventModal' 
                                                data-event-id='$event_id' title='View Details'>
                                            <i class='bx bx-show'></i>
                                        </button>
                                        <button type='button' class='btn btn-warning btn-sm edit-event-btn' 
                                                data-toggle='modal' data-target='#editEventModal' 
                                                data-event-id='$event_id' title='Edit Event'>
                                            <i class='bx bx-edit'></i>
                                        </button>
                                        <button type='button' class='btn btn-danger btn-sm delete-event-btn' 
                                                data-event-id='$event_id' title='Delete Event'>
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center text-muted'>
                            <div class='py-4'>
                                <i class='bx bx-calendar-x' style='font-size: 2rem; color: #6c757d;'></i>
                                <p class='mt-2 mb-0'>No events found.</p>
                                <small class='text-muted'>Click 'Add New Event' to create your first event.</small>
                            </div>
                        </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Events pagination">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bx bx-plus"></i> Add New Event
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="addEventForm">
                <div class="modal-body">
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
                        <textarea class="form-control" id="eventDescription" name="event_description" rows="3"></textarea>
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
                        <input type="number" class="form-control" id="absPenalty" name="abs_penalty" 
                               min="0" step="0.01" placeholder="Penalty amount in pesos (₱)">
                    </div>
                    
                    <!-- Section Selection for Exclusive Events -->
                    <div class="form-group" id="sectionSelectionGroup" style="display: none;">
                        <label class="font-weight-bold">
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
                                            <input type="checkbox" 
                                                   class="custom-control-input section-checkbox" 
                                                   id="section_<?php echo $section['section_id']; ?>" 
                                                   name="selected_classes[]" 
                                                   value="<?php echo $section['section_id']; ?>">
                                            <label class="custom-control-label" for="section_<?php echo $section['section_id']; ?>">
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Event Modal -->
<div class="modal fade" id="viewEventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bx bx-show"></i> Event Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="viewEventContent">
                <!-- Event details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bx bx-edit"></i> Edit Event
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="editEventContent">
                <!-- Edit form will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteEventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bx bx-trash"></i> Confirm Delete
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this event?</p>
                <p class="text-danger"><strong>This action cannot be undone.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteEventBtn">Delete Event</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-update event statuses on page load (silently)
    function autoUpdateEventStatuses() {
        $.post('/eam_system_v0.1.1/utils/event_status_updater.php', function(response) {
            try {
                const results = JSON.parse(response);
                if (results.updated > 0) {
                    console.log(`Auto-updated ${results.updated} event statuses`);
                    // Optionally show a subtle notification
                    if (results.updated > 0) {
                        // Show a subtle toast notification
                        showStatusUpdateNotification(results.updated);
                    }
                }
            } catch (e) {
                console.error('Error parsing auto-update response:', e);
            }
        }).fail(function() {
            console.error('Failed to auto-update event statuses');
        });
    }
    
    // Show subtle notification for status updates
    function showStatusUpdateNotification(updatedCount) {
        // Create a subtle notification
        const notification = $(`
            <div class="alert alert-info alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="bx bx-info-circle"></i> 
                Auto-updated ${updatedCount} event status${updatedCount > 1 ? 'es' : ''}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `);
        
        $('body').append(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(function() {
            notification.alert('close');
        }, 3000);
    }
    
    // Run auto-update on page load
    autoUpdateEventStatuses();
    
    // Set up periodic auto-updates every 5 minutes (300,000 ms)
    setInterval(function() {
        // Only run if the events page is still active/visible
        if (document.visibilityState === 'visible' && !document.hidden) {
            autoUpdateEventStatuses();
        }
    }, 300000); // 5 minutes
    
});
</script>

<!-- Note: JavaScript event handlers are now in dashboard.js -->