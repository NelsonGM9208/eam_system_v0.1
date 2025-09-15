<?php
/**
 * Student Excuse Letter Management
 * Handles excuse letter submission, status tracking, and document management
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Include utilities
require_once __DIR__ . "/../../utils/index.php";

// Initialize database if needed
if (!initializeDatabase()) {
    echo displayError("Failed to initialize database.");
    exit;
}

// Get database connection
$con = getDatabaseConnection();
if (!$con) {
    echo displayError("Database connection failed.");
    exit;
}

// Get current user info
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo displayError("User not authenticated.");
    exit;
}

// Get user details
$user_query = "SELECT firstname, lastname, email, role FROM users WHERE user_id = ?";
$user_stmt = $con->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Get excuse letters for this student
$excuse_query = "SELECT el.*, 
                CONCAT(u.firstname, ' ', u.lastname) as student_name,
                e.title as event_title,
                e.event_date,
                e.start_time
                FROM excuse_letters el
                LEFT JOIN users u ON el.student_id = u.user_id
                LEFT JOIN events e ON el.event_id = e.event_id
                WHERE el.student_id = ?
                ORDER BY el.created_at DESC";

$excuse_stmt = $con->prepare($excuse_query);
$excuse_stmt->bind_param("i", $user_id);
$excuse_stmt->execute();
$excuse_letters = $excuse_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get available events for excuse submission
$events_query = "SELECT event_id, title, event_date, start_time, end_time, location 
                FROM events 
                WHERE event_status IN ('Upcoming', 'Ongoing', 'Finished')
                AND event_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                ORDER BY event_date DESC, start_time DESC";

$events_result = mysqli_query($con, $events_query);
$available_events = [];
if ($events_result) {
    while ($event = mysqli_fetch_assoc($events_result)) {
        $available_events[] = $event;
    }
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Excuse Letter Management</h2>
                    <p class="text-muted mb-0">Submit and track your excuse letters for events</p>
                </div>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#submitExcuseModal">
                    <i class="bx bx-plus"></i> Submit Excuse Letter
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo count($excuse_letters); ?></h4>
                            <p class="mb-0">Total Submissions</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bx bx-file-blank" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo count(array_filter($excuse_letters, function($e) { return $e['status'] === 'Pending'; })); ?></h4>
                            <p class="mb-0">Pending</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bx bx-time" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo count(array_filter($excuse_letters, function($e) { return $e['status'] === 'Approved'; })); ?></h4>
                            <p class="mb-0">Approved</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bx bx-check-circle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo count(array_filter($excuse_letters, function($e) { return $e['status'] === 'Rejected'; })); ?></h4>
                            <p class="mb-0">Rejected</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bx bx-x-circle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Excuse Letters Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Excuse Letter History</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($excuse_letters)): ?>
                        <div class="text-center py-5">
                            <i class="bx bx-file-blank" style="font-size: 4rem; color: #ccc;"></i>
                            <h5 class="mt-3 text-muted">No Excuse Letters Submitted</h5>
                            <p class="text-muted">Submit your first excuse letter using the button above.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Event</th>
                                        <th>Date</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($excuse_letters as $excuse): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($excuse['event_title']); ?></strong>
                                                    <?php if ($excuse['event_date']): ?>
                                                        <br><small class="text-muted"><?php echo date('M j, Y', strtotime($excuse['event_date'])); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($excuse['event_date']): ?>
                                                    <?php echo date('M j, Y', strtotime($excuse['event_date'])); ?>
                                                    <?php if ($excuse['start_time']): ?>
                                                        <br><small class="text-muted"><?php echo date('g:i A', strtotime($excuse['start_time'])); ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div style="max-width: 200px;">
                                                    <?php echo htmlspecialchars(substr($excuse['reason'], 0, 100)); ?>
                                                    <?php if (strlen($excuse['reason']) > 100): ?>
                                                        <span class="text-muted">...</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                $status_icon = '';
                                                switch ($excuse['status']) {
                                                    case 'Pending':
                                                        $status_class = 'badge-warning';
                                                        $status_icon = 'bx-time';
                                                        break;
                                                    case 'Approved':
                                                        $status_class = 'badge-success';
                                                        $status_icon = 'bx-check-circle';
                                                        break;
                                                    case 'Rejected':
                                                        $status_class = 'badge-danger';
                                                        $status_icon = 'bx-x-circle';
                                                        break;
                                                    default:
                                                        $status_class = 'badge-secondary';
                                                        $status_icon = 'bx-question-mark';
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <i class="bx <?php echo $status_icon; ?>"></i> <?php echo $excuse['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <?php echo date('M j, Y', strtotime($excuse['created_at'])); ?>
                                                    <br><small class="text-muted"><?php echo date('g:i A', strtotime($excuse['created_at'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewExcuseDetails(<?php echo $excuse['excuse_id']; ?>)" title="View Details">
                                                        <i class="bx bx-show"></i>
                                                    </button>
                                                    <?php if ($excuse['status'] === 'Pending'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="editExcuse(<?php echo $excuse['excuse_id']; ?>)" title="Edit">
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Excuse Letter Modals -->
<?php include __DIR__ . '/modals/excuse_letter_modal.php'; ?>

<!-- Excuse Letter JavaScript is included in the main student page -->
