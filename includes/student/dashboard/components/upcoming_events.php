<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Upcoming Events -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-calendar-event mr-2"></i>
                    Upcoming Events
                </h5>
                <span class="badge badge-light">
                    <?php echo count($upcoming_events); ?> events
                </span>
            </div>
            <div class="card-body">
                <?php if (!empty($upcoming_events)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($upcoming_events as $event): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                    <small class="text-muted">
                                        <i class="bx bx-calendar mr-1"></i>
                                        <?php echo formatDisplayDate($event['event_date'], 'M j, Y'); ?>
                                        <i class="bx bx-time ml-2 mr-1"></i>
                                        <?php echo formatDisplayTime($event['start_time']); ?> - <?php echo formatDisplayTime($event['end_time']); ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bx bx-map mr-1"></i>
                                        <?php echo htmlspecialchars($event['location']); ?>
                                    </small>
                                    <?php if ($event['event_description']): ?>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars(substr($event['event_description'], 0, 100)); ?>
                                        <?php if (strlen($event['event_description']) > 100): ?>...<?php endif; ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewEventDetails(<?php echo $event['event_id']; ?>)">
                                        <i class="bx bx-show"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="?page=events" class="btn btn-info btn-sm">
                            <i class="bx bx-list-ul mr-1"></i>View All Events
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bx bx-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">No Upcoming Events</h5>
                        <p class="text-muted">There are no upcoming events scheduled for your section.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
