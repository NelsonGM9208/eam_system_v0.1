<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Recent Activities -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-history me-2"></i>
                    Recent Activities
                </h5>
                <small class="text-muted">Last 24 hours</small>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_activities)): ?>
                    <div class="timeline">
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="timeline-item d-flex mb-3">
                                <div class="timeline-marker">
                                    <div class="timeline-icon bg-<?php echo $activity['color']; ?> text-white">
                                        <i class="bx <?php echo $activity['icon']; ?>"></i>
                                    </div>
                                </div>
                                <div class="timeline-content flex-grow-1 ml-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                            <p class="mb-1 text-muted"><?php echo htmlspecialchars($activity['description']); ?></p>
                                            <small class="text-muted">
                                                <i class="bx bx-time"></i> 
                                                <?php echo formatDisplayDate($activity['timestamp'], 'M j, Y g:i A'); ?>
                                            </small>
                                        </div>
                                        <span class="badge badge-<?php echo $activity['color']; ?>">
                                            <?php echo htmlspecialchars($activity['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bx bx-history text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">No Recent Activities</h5>
                        <p class="text-muted">No activities recorded in the last 24 hours.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
