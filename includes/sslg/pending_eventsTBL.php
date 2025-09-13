<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

// Fetch pending events created by current SSLG user
$current_user_id = $_SESSION['user_id'] ?? null;
$pending_events = [];
if ($current_user_id) {
    $pending_query = "SELECT e.*, 
                      CONCAT(u.firstname, ' ', u.lastname) as creator_name,
                      u.role as creator_role
                      FROM events e 
                      LEFT JOIN users u ON e.created_by = u.user_id 
                      WHERE e.created_by = ? AND e.approval_status = 'Pending'
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
                                        <th>Approval Status</th>
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
                                                    class="badge badge-<?php echo $event['event_type'] == 'Exclusive' ? 'danger' : 'primary'; ?>">
                                                    <?php echo htmlspecialchars($event['event_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="bx bx-calendar text-primary"></i>
                                                <?php echo formatEventDate($event['event_date']); ?><br>
                                                <i class="bx bx-time text-secondary"></i>
                                                <?php echo formatDisplayTime($event['start_time']); ?> -
                                                <?php echo formatDisplayTime($event['end_time']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($event['location']); ?></td>
                                            <td>
                                                <span
                                                    class="badge badge-warning"><?php echo htmlspecialchars($event['event_status']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $event['approval_status'] == 'Approved' ? 'success' : 'warning'; ?>">
                                                    <?php echo htmlspecialchars($event['approval_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo formatDisplayDate($event['created_at'], 'M j, Y g:i A'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-info btn-sm view-event-btn"
                                                        data-toggle="modal" data-target="#viewEventModal"
                                                        data-event-id="<?php echo $event['event_id']; ?>" title="View Details">
                                                        <i class="bx bx-show"></i>
                                                    </button>
                                                    <?php if ($event['approval_status'] == 'Pending'): ?>
                                                    <button type="button" class="btn btn-warning btn-sm edit-event-btn"
                                                        data-toggle="modal" data-target="#editEventModal"
                                                        data-event-id="<?php echo $event['event_id']; ?>" title="Edit Event">
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
                            <i class="bx bx-calendar-x" style="font-size: 3rem; color: #6c757d;"></i>
                            <h5 class="mt-3 text-muted">No Pending Events</h5>
                            <p class="text-muted">You haven't created any events yet. Use the form above to create your
                                first
                                event.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Event Modal -->
<?php include __DIR__ . "/modals/view_event_modal.php"; ?>

<!-- Edit Event Modal -->
<?php include __DIR__ . "/modals/edit_event_modal.php"; ?>

<!-- Include JavaScript for Pending Events functionality -->
<script src="/eam_system_v0.1.1/includes/sslg/js/pending_events.js"></script>