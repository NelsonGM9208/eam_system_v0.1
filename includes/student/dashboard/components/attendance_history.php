<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Attendance History -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-history mr-2"></i>
                    Recent Attendance History
                </h5>
                <span class="badge badge-light">
                    Last 10 records
                </span>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_attendance)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Check-in</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_attendance as $attendance): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($attendance['event_title']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($attendance['location']); ?></small>
                                        </td>
                                        <td><?php echo formatDisplayDate($attendance['event_date'], 'M j, Y'); ?></td>
                                        <td>
                                            <?php echo formatDisplayTime($attendance['start_time']); ?> - 
                                            <?php echo formatDisplayTime($attendance['end_time']); ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            switch($attendance['remark']) {
                                                case 'Present':
                                                    $status_class = 'badge-success';
                                                    break;
                                                case 'Late':
                                                    $status_class = 'badge-warning';
                                                    break;
                                                case 'Absent':
                                                    $status_class = 'badge-danger';
                                                    break;
                                                case 'Excused':
                                                    $status_class = 'badge-info';
                                                    break;
                                                default:
                                                    $status_class = 'badge-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>">
                                                <?php echo htmlspecialchars($attendance['remark']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($attendance['check_in_time']): ?>
                                                <?php echo formatDisplayDate($attendance['check_in_time'], 'M j, g:i A'); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="?page=attendance" class="btn btn-success btn-sm">
                            <i class="bx bx-list-ul me-1"></i>View Full Attendance History
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bx bx-history text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">No Attendance Records</h5>
                        <p class="text-muted">You haven't attended any events yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
