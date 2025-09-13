<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

// Check database connection
if (!isset($con) || !$con) {
    echo "<div class='alert alert-danger'>Database connection failed.</div>";
    exit;
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// Get total number of pending events
$total_query = "SELECT COUNT(*) as total FROM events WHERE approval_status = 'Pending'";
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
    // Get pending events with pagination
    $query = "SELECT e.*, 
              CONCAT(u.firstname, ' ', u.lastname) as creator_name,
              u.role as creator_role,
              u.email as creator_email
              FROM events e 
              LEFT JOIN users u ON e.created_by = u.user_id 
              WHERE e.approval_status = 'Pending'
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

<div class="container-fluid">
    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0 d-flex align-items-center">
                <i class="bx bx-time mr-2"></i>
                Pending Events Approval
            </h5>
            <span class="badge badge-light">
                <?php echo $total_records; ?> pending events
            </span>
        </div>
        <div class="card-body">
            <!-- Search and Filter -->
            <div class="row mb-3">
                <div class="col-md-3 col-sm-12 mb-2">
                    <div class="input-group">
                        <input type="text" class="form-control" id="eventSearch" placeholder="Search events...">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <select class="form-control" id="eventTypeFilter">
                        <option value="">All Types</option>
                        <option value="Open">Open</option>
                        <option value="Exclusive">Exclusive</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <select class="form-control" id="creatorFilter">
                        <option value="">All Creators</option>
                        <option value="sslg">SSLG</option>
                        <option value="admin">Admin</option>    
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="clearEventFilters">
                        <i class="bx bx-x"></i> Clear
                    </button>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-success btn-sm" id="bulkApproveBtn" disabled>
                            <i class="bx bx-check"></i> Approve Events
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" id="bulkRejectBtn" disabled>
                            <i class="bx bx-x"></i> Reject Events
                        </button>
                    </div>
                </div>
            </div>

            <!-- Events Table -->
            <div class="table-responsive">
                <table class="table table-hover" id="pendingEventsTable">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Event Title</th>
                            <th>Description</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Created By</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($total_records > 0 && $result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $event_id = intval($row['event_id']); // Ensure it's an integer
                                
                                // Debug: Check if event_id is valid
                                if ($event_id <= 0) {
                                    error_log("Invalid event_id found: " . $row['event_id'] . " for event: " . $row['title']);
                                }
                                
                                $title = htmlspecialchars($row['title']);
                                $description = htmlspecialchars($row['event_description']);
                                $event_date = date('M d, Y', strtotime($row['event_date']));
                                $start_time = date('h:i A', strtotime($row['start_time']));
                                $end_time = date('h:i A', strtotime($row['end_time']));
                                $location = htmlspecialchars($row['location']);
                                $event_type = htmlspecialchars($row['event_type']);
                                $creator_name = htmlspecialchars($row['creator_name'] ?? 'Unknown');
                                $creator_role = htmlspecialchars($row['creator_role'] ?? 'Unknown');
                                $creator_email = htmlspecialchars($row['creator_email'] ?? '');
                                $created_at = date('M j, Y g:i A', strtotime($row['created_at']));
                                
                                // Type badge colors
                                $type_badge = ($event_type == 'Exclusive') ? 'badge-danger' : 'badge-primary';
                                
                                echo "<tr data-event-id='$event_id' data-event-type='$event_type' data-creator-role='$creator_role' data-creator-email='$creator_email'>
                                    <td>
                                        <input type='checkbox' class='form-check-input event-checkbox' value='$event_id'>
                                    </td>
                                    <td><strong>$title</strong></td>
                                    <td>" . (strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description) . "</td>
                                    <td>
                                        <div><strong>$event_date</strong></div>
                                        <small class='text-muted'>$start_time - $end_time</small>
                                    </td>
                                    <td>$location</td>
                                    <td><span class='badge $type_badge'>$event_type</span></td>
                                    <td>
                                        <div>$creator_name</div>
                                        <small class='text-muted'>$creator_role</small>
                                    </td>
                                    <td>
                                        <small class='text-muted'>$created_at</small>
                                    </td>
                                    <td>
                                        <div class='btn-group' role='group'>
                                            <button type='button' class='btn btn-info btn-sm view-event-btn' 
                                                    data-toggle='modal' data-target='#viewEventModal' 
                                                    data-event-id='$event_id' title='View Details'>
                                                <i class='bx bx-show'></i>
                                            </button>
                                            <button type='button' class='btn btn-success btn-sm approve-event-btn' 
                                                    data-event-id='$event_id' data-creator-email='$creator_email' 
                                                    data-event-title='$title' title='Approve Event'>
                                                <i class='bx bx-check'></i>
                                            </button>
                                            <button type='button' class='btn btn-danger btn-sm reject-event-btn' 
                                                    data-event-id='$event_id' data-creator-email='$creator_email' 
                                                    data-event-title='$title' title='Reject Event'>
                                                <i class='bx bx-x'></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr>
                                <td colspan='9' class='text-center py-4'>
                                    <i class='bx bx-calendar-check' style='font-size: 3rem; color: #6c757d;'></i>
                                    <h5 class='mt-3 text-muted'>No Pending Events</h5>
                                    <p class='text-muted'>There are no events waiting for approval at the moment.</p>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Pending events pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                <i class="bx bx-chevron-left"></i> Previous
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                Next <i class="bx bx-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <div class="text-center mt-2">
                    <small class="text-muted">
                        Showing page <?php echo $page; ?> of <?php echo $total_pages; ?> 
                        (<?php echo $total_records; ?> total pending events)
                    </small>
                </div>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Event Modal -->
<?php include __DIR__ . "/modals/view_event_modal.php"; ?>

<!-- Approve Event Modal -->
<?php include __DIR__ . "/modals/approve_event_modal.php"; ?>

<!-- Reject Event Modal -->
<?php include __DIR__ . "/modals/reject_event_modal.php"; ?>

<!-- Bulk Actions Modal -->
<?php include __DIR__ . "/modals/bulk_actions_modal.php"; ?>

<!-- Include JavaScript for Admin Pending Events functionality -->
<script src="/eam_system_v0.1.1/includes/admin/js/pending_events.js"></script>