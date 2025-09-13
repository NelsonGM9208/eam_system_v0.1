<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Pending Items Summary -->
<div class="row mb-4">
    <!-- Pending Users -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-user-x me-2"></i>
                    Pending User Approvals
                </h5>
                <span class="badge badge-light">
                    <?php echo count($pending_items['pending_users']); ?> pending
                </span>
            </div>
            <div class="card-body">
                <?php if (!empty($pending_items['pending_users'])): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pending_items['pending_users'] as $user): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($user['email']); ?> • 
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bx bx-time"></i> 
                                        <?php echo date('M j, Y g:i A', strtotime($user['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="btn-group" role="group">
                                    <a href="?page=pending_users" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-show"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="?page=pending_users" class="btn btn-warning btn-sm">
                            <i class="bx bx-list-ul me-1"></i>View All Pending Users
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bx bx-check-circle text-success" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">No Pending Users</h5>
                        <p class="text-muted">All user registrations have been processed.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pending Events -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-calendar-plus me-2"></i>
                    Pending Event Approvals
                </h5>
                <span class="badge badge-light">
                    <?php echo count($pending_items['pending_events']); ?> pending
                </span>
            </div>
            <div class="card-body">
                <?php if (!empty($pending_items['pending_events'])): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pending_items['pending_events'] as $event): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                    <small class="text-muted">
                                        by <?php echo htmlspecialchars($event['creator_name']); ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bx bx-calendar"></i> 
                                        <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bx bx-time"></i> 
                                        <?php echo date('M j, Y g:i A', strtotime($event['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="btn-group" role="group">
                                    <a href="?page=pending_events" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-show"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="?page=pending_events" class="btn btn-info btn-sm">
                            <i class="bx bx-list-ul me-1"></i>View All Pending Events
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bx bx-check-circle text-success" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">No Pending Events</h5>
                        <p class="text-muted">All event submissions have been processed.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
