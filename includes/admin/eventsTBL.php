<?php
require_once __DIR__ . "/../../config/database.php";

// Check database connection
if (!isset($con) || !$con) {
    echo "<div class='alert alert-danger'>Database connection failed.</div>";
    exit;
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Events Management</h5>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addEventModal">
            <i class="bx bx-plus"></i> Add New Event
        </button>
    </div>
    <div class="card-body">
        <!-- Search and Filter -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="eventSearch" placeholder="Search events...">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bx bx-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-control" id="eventStatusFilter">
                    <option value="">All Status</option>
                    <option value="Upcoming">Upcoming</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Finished">Finished</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-control" id="eventTypeFilter">
                    <option value="">All Types</option>
                    <option value="Open">Open</option>
                    <option value="Exclusive">Exclusive</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="clearEventFilters" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">
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
            <div class="modal-header">
                <h5 class="modal-title">Add New Event</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="addEventForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="eventTitle">Event Title *</label>
                                <input type="text" class="form-control" id="eventTitle" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="eventDate">Event Date *</label>
                                <input type="date" class="form-control" id="eventDate" name="event_date" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="startTime">Start Time *</label>
                                <input type="time" class="form-control" id="startTime" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="endTime">End Time *</label>
                                <input type="time" class="form-control" id="endTime" name="end_time" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="eventLocation">Location *</label>
                                <input type="text" class="form-control" id="eventLocation" name="location" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="eventStatus">Status *</label>
                                <select class="form-control" id="eventStatus" name="event_status" required>
                                    <option value="Upcoming">Upcoming</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Finished">Finished</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="absPenalty">Absence Penalty</label>
                        <input type="number" class="form-control" id="absPenalty" name="abs_penalty" 
                               min="0" step="0.01" placeholder="Penalty amount in pesos (₱)">
                    </div>
                    
                    <!-- Class Selection for Exclusive Events -->
                    <div class="form-group" id="classSelectionGroup" style="display: none;">
                        <label>Select Classes for Exclusive Event</label>
                        <div class="row" id="classCheckboxes">
                            <!-- Classes will be loaded dynamically -->
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
            <div class="modal-header">
                <h5 class="modal-title">Event Details</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
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
            <div class="modal-header">
                <h5 class="modal-title">Edit Event</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
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
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
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

<!-- Note: JavaScript event handlers are now in dashboard.js -->