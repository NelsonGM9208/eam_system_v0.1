<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

// Get current user ID
$current_user_id = $_SESSION['user_id'] ?? null;

// Get statistics
$stats = [
    'total_events' => 0,
    'pending_events' => 0,
    'approved_events' => 0,
    'rejected_events' => 0,
    'upcoming_events' => 0,
    'ongoing_events' => 0,
    'finished_events' => 0
];

if ($current_user_id) {
    // Total events created by this user
    $total_query = "SELECT COUNT(*) as total FROM events WHERE created_by = ?";
    $stmt = mysqli_prepare($con, $total_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $current_user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $stats['total_events'] = $row['total'];
        mysqli_stmt_close($stmt);
    }
    
    // Pending events created by this user
    $pending_query = "SELECT COUNT(*) as total FROM events WHERE created_by = ? AND approval_status = 'Pending'";
    $stmt = mysqli_prepare($con, $pending_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $current_user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $stats['pending_events'] = $row['total'];
        mysqli_stmt_close($stmt);
    }
    
    // Approved events created by this user
    $approved_query = "SELECT COUNT(*) as total FROM events WHERE created_by = ? AND approval_status = 'Approved'";
    $stmt = mysqli_prepare($con, $approved_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $current_user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $stats['approved_events'] = $row['total'];
        mysqli_stmt_close($stmt);
    }
    
    // Rejected events created by this user
    $rejected_query = "SELECT COUNT(*) as total FROM events WHERE created_by = ? AND approval_status = 'Rejected'";
    $stmt = mysqli_prepare($con, $rejected_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $current_user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $stats['rejected_events'] = $row['total'];
        mysqli_stmt_close($stmt);
    }
}

// Get all approved events status counts (not limited to current user)
$status_query = "SELECT event_status, COUNT(*) as total FROM events WHERE approval_status = 'Approved' GROUP BY event_status";
$result = mysqli_query($con, $status_query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        switch($row['event_status']) {
            case 'Upcoming':
                $stats['upcoming_events'] = $row['total'];
                break;
            case 'Ongoing':
                $stats['ongoing_events'] = $row['total'];
                break;
            case 'Finished':
                $stats['finished_events'] = $row['total'];
                break;
        }
    }
}

// Get recent events (last 5)
$recent_events = [];
if ($current_user_id) {
    $recent_query = "SELECT e.*, 
                     CONCAT(u.firstname, ' ', u.lastname) as creator_name,
                     u.role as creator_role
                     FROM events e 
                     LEFT JOIN users u ON e.created_by = u.user_id 
                     WHERE e.created_by = ? 
                     ORDER BY e.created_at DESC 
                     LIMIT 5";
    $stmt = mysqli_prepare($con, $recent_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $current_user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $recent_events[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">
                                <i class="bx bx-tachometer me-2"></i>
                                SSLG Dashboard
                            </h2>
                            <p class="mb-0 opacity-75">
                                Welcome back! Manage your events and track your activities here.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-flex flex-column">
                                <small class="opacity-75">Last Login</small>
                                <strong><?php 
                                    $last_login_time = $_SESSION['login_time'] ?? 'Never';
                                    if ($last_login_time !== 'Never') {
                                        echo formatDisplayDate($last_login_time, 'M j, Y g:i A');
                                    } else {
                                        echo 'Never';
                                    }
                                ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                My Events
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['total_events']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-calendar-event text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                My Pending
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['pending_events']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-time text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                My Approved
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['approved_events']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-check-circle text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                All Upcoming
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['upcoming_events']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-calendar text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bx bx-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="?page=add_events" class="btn btn-primary btn-block h-100 d-flex flex-column align-items-center justify-content-center">
                                <i class="bx bx-plus-circle mb-2" style="font-size: 2rem;"></i>
                                <span>Create Event</span>
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="?page=events" class="btn btn-success btn-block h-100 d-flex flex-column align-items-center justify-content-center">
                                <i class="bx bx-calendar-check mb-2" style="font-size: 2rem;"></i>
                                <span>All Events</span>
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="?page=attendance" class="btn btn-info btn-block h-100 d-flex flex-column align-items-center justify-content-center">
                                <i class="bx bx-user-check mb-2" style="font-size: 2rem;"></i>
                                <span>Attendance</span>
                            </a>
                        </div>
                            </div>
                        </div>
                            </div>
                        </div>
                    </div>
                        
    <!-- Recent Events -->
    <div class="row mb-4">
                        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-history me-2"></i>
                        Recent Events
                    </h5>
                    <a href="?page=events" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_events)): ?>
                            <div class="table-responsive">
                            <table class="table table-hover">
                                    <thead>
                                        <tr>
                                        <th>Event Title</th>
                                        <th>Date & Time</th>
                                            <th>Status</th>
                                        <th>Approval</th>
                                        <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($recent_events as $event): ?>
                                        <?php
                                        $event_id = $event['event_id'];
                                        $title = htmlspecialchars($event['title']);
                                        $event_date = formatEventDate($event['event_date']);
                                        $start_time = formatDisplayTime($event['start_time']);
                                        $end_time = formatDisplayTime($event['end_time']);
                                        $status = htmlspecialchars($event['event_status']);
                                        $approval_status = htmlspecialchars($event['approval_status']);
                                        
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
                                        
                                        // Approval badge colors
                                        $approval_badge = '';
                                        switch($approval_status) {
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
                                        <tr>
                                            <td>
                                                <strong><?php echo $title; ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($event['location']); ?></small>
                                            </td>
                                            <td>
                                                <div><strong><?php echo $event_date; ?></strong></div>
                                                <small class="text-muted"><?php echo $start_time; ?> - <?php echo $end_time; ?></small>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $status_badge; ?>"><?php echo $status; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $approval_badge; ?>"><?php echo $approval_status; ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-info btn-sm view-event-btn" 
                                                            data-toggle="modal" data-target="#viewEventModal" 
                                                            data-event-id="<?php echo $event_id; ?>" title="View Details">
                                                        <i class="bx bx-show"></i>
                                                    </button>
                                                    <?php if ($approval_status == 'Pending'): ?>
                                                    <button type="button" class="btn btn-warning btn-sm edit-event-btn" 
                                                            data-toggle="modal" data-target="#editEventModal" 
                                                            data-event-id="<?php echo $event_id; ?>" title="Edit Event">
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bx bx-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No Events Yet</h5>
                            <p class="text-muted">You haven't created any events yet. Start by creating your first event!</p>
                            <a href="?page=add_events" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i>Create Your First Event
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Status Overview -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bx bx-pie-chart me-2"></i>
                        All Events Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h3 class="text-warning"><?php echo $stats['upcoming_events']; ?></h3>
                                <small class="text-muted">Upcoming</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h3 class="text-info"><?php echo $stats['ongoing_events']; ?></h3>
                                <small class="text-muted">Ongoing</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h3 class="text-success"><?php echo $stats['finished_events']; ?></h3>
                            <small class="text-muted">Finished</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bx bx-check-square me-2"></i>
                        My Events Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h3 class="text-success"><?php echo $stats['approved_events']; ?></h3>
                                <small class="text-muted">Approved</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h3 class="text-warning"><?php echo $stats['pending_events']; ?></h3>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h3 class="text-danger"><?php echo $stats['rejected_events']; ?></h3>
                            <small class="text-muted">Rejected</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Event Modal -->
<?php include __DIR__ . "/modals/view_event_modal.php"; ?>

<!-- Edit Event Modal -->
<?php include __DIR__ . "/modals/edit_event_modal.php"; ?>

<!-- Include JavaScript for Dashboard functionality -->
<script src="/eam_system_v0.1.1/includes/sslg/js/dashboard.js"></script>