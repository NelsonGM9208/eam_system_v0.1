<?php
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

// Pagination setup
$limit = 15;
$current_page = validateInput($_GET['page_num'] ?? 1, 'int', 1);

// Get total attendance count using utility
$totalAttendanceCount = getRecordCount('attendance');
if ($totalAttendanceCount === false) {
    echo displayError("Failed to get attendance count.");
    exit;
}

// Calculate pagination using utility
$pagination = calculatePagination($totalAttendanceCount, $limit, $current_page);

// Get current page attendance records using utility
$query = "SELECT a.*, 
          CONCAT(u.firstname, ' ', u.lastname) as student_name,
          u.email as student_email,
          e.title as event_title,
          e.event_date,
          e.start_time,
          e.end_time,
          s.grade,
          s.section,
          s.section_id
          FROM attendance a 
          LEFT JOIN users u ON a.student_id = u.user_id 
          LEFT JOIN events e ON a.event_id = e.event_id
          LEFT JOIN enrollment en ON a.student_id = en.student_id AND en.status = 'Active'
          LEFT JOIN section s ON en.section_id = s.section_id
          ORDER BY a.check_in_time DESC 
          LIMIT ?, ?";
$attendance = executeQuery($query, [$pagination['offset'], $pagination['recordsPerPage']], 'ii');
if (!$attendance) {
    echo displayError("Failed to fetch attendance records.");
    exit;
}

// Get filter options
$grades = executeQuery("SELECT DISTINCT grade FROM section ORDER BY grade", [], '');
$events = executeQuery("SELECT event_id, title, event_date FROM events ORDER BY event_date DESC", [], '');
$classes = executeQuery("SELECT section_id, grade, section FROM section ORDER BY grade, section", [], '');
$remarks = ['Present', 'Late', 'Absent', 'Excused'];
?>

<div class="card mt-4 shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 d-flex align-items-center">
            <i class="bx bx-check-square mr-2"></i>
            Attendance Management
        </h5>
        <div>
            <span class="badge badge-light badge-pill"><?php echo $totalAttendanceCount; ?> Records</span>
        </div>
                </div>
                <div class="card-body">
        <!-- Search and Filter Section -->
        <div class="row mb-3">
            <div class="col-md-3 col-sm-12 mb-2">
                <input type="text" class="form-control" id="attendanceSearch" placeholder="Search attendance...">
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <select class="form-control" id="gradeFilter">
                    <option value="">All Grades</option>
                    <?php if ($grades): ?>
                        <?php foreach ($grades as $grade): ?>
                            <option value="<?php echo htmlspecialchars($grade['grade']); ?>">
                                Grade <?php echo htmlspecialchars($grade['grade']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <select class="form-control" id="eventFilter">
                    <option value="">All Events</option>
                    <?php if ($events): ?>
                        <?php foreach ($events as $event): ?>
                            <option value="<?php echo $event['event_id']; ?>">
                                <?php echo htmlspecialchars($event['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <select class="form-control" id="remarkFilter">
                    <option value="">All Remarks</option>
                    <?php foreach ($remarks as $remark): ?>
                        <option value="<?php echo $remark; ?>"><?php echo $remark; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <select class="form-control" id="classFilter">
                    <option value="">All Classes</option>
                    <?php if ($classes): ?>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['section_id']; ?>">
                                Grade <?php echo htmlspecialchars($class['grade']); ?> - <?php echo htmlspecialchars($class['section']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-1 col-sm-12 mb-2">
                <div class="btn-group w-100" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clearFilters">
                        <i class="bx bx-x"></i>
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="exportAttendance">
                        <i class="bx bx-download"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Results Info -->
        <div class="row mb-3">
            <div class="col-12">
                <small class="text-muted" id="resultsInfo">
                    <?php if ($totalAttendanceCount > 0): ?>
                        Showing <?php echo $pagination['offset'] + 1; ?> to 
                        <?php echo min($pagination['offset'] + $pagination['recordsPerPage'], $totalAttendanceCount); ?> 
                        of <?php echo $totalAttendanceCount; ?> results
                    <?php else: ?>
                        Showing 0 of 0 results
                    <?php endif; ?>
                </small>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="attendanceTable">
                <thead class="thead-light">
                    <tr>
                        <th>Student</th>
                        <th>Event</th>
                        <th>Date & Time</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Remark</th>
                        <th>Penalty</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($attendance && mysqli_num_rows($attendance) > 0): ?>
                        <?php while ($record = mysqli_fetch_assoc($attendance)): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2">
                                            <i class="bx bx-user"></i>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($record['student_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($record['student_email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($record['event_title']); ?></strong>
                                    <?php if ($record['grade'] && $record['section']): ?>
                                        <br>
                                        <small class="text-muted">Grade <?php echo htmlspecialchars($record['grade']); ?> - <?php echo htmlspecialchars($record['section']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo formatEventDate($record['event_date']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo formatDisplayTime($record['start_time']); ?> - 
                                        <?php echo formatDisplayTime($record['end_time']); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo formatDisplayDate($record['check_in_time'], 'M d, h:i A'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($record['check_out_time']): ?>
                                        <span class="badge badge-success">
                                            <?php echo date('M d, h:i A', strtotime($record['check_out_time'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Not checked out</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $remarkClass = '';
                                    switch ($record['remark']) {
                                        case 'Present':
                                            $remarkClass = 'badge-success';
                                            break;
                                        case 'Late':
                                            $remarkClass = 'badge-warning';
                                            break;
                                        case 'Absent':
                                            $remarkClass = 'badge-danger';
                                            break;
                                        case 'Excused':
                                            $remarkClass = 'badge-info';
                                            break;
                                        default:
                                            $remarkClass = 'badge-secondary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $remarkClass; ?>">
                                        <?php echo htmlspecialchars($record['remark']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($record['penalty'] > 0): ?>
                                        <span class="text-danger">₱<?php echo number_format($record['penalty'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">₱0.00</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-info btn-sm view-attendance-btn" 
                                                data-toggle="modal" data-target="#viewAttendanceModal" 
                                                data-attendance-id="<?php echo $record['attendance_id']; ?>" 
                                                data-section-id="<?php echo $record['section_id'] ?? ''; ?>" 
                                                title="View Details">
                                            <i class="bx bx-show"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-3">
                                <i class="bx bx-calendar-x" style="font-size: 2.5rem; color: #6c757d; margin-bottom: 0.75rem;"></i>
                                <p class="text-muted mb-0">No attendance records found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <nav aria-label="Attendance pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($pagination['currentPage'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=attendance&page_num=<?php echo $pagination['currentPage'] - 1; ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $pagination['currentPage'] - 2); $i <= min($pagination['totalPages'], $pagination['currentPage'] + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $pagination['currentPage'] ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=attendance&page_num=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=attendance&page_num=<?php echo $pagination['currentPage'] + 1; ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- View Attendance Modal -->
<div class="modal fade" id="viewAttendanceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bx bx-show"></i> Attendance Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="viewAttendanceContent">
                <!-- Attendance details will be loaded here via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bx bx-download"></i> Export Attendance
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <div class="form-group">
                        <label for="exportFormat">Export Format</label>
                        <select class="form-control" id="exportFormat" name="format" required>
                            <option value="pdf" selected>PDF Report</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exportEvent">Event (Optional)</label>
                        <select class="form-control" id="exportEvent" name="event">
                            <option value="">All Events</option>
                            <?php if ($events): ?>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?php echo $event['event_id']; ?>">
                                        <?php echo htmlspecialchars($event['title']); ?> (<?php echo date('M d, Y', strtotime($event['event_date'])); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exportClass">Class (Optional)</label>
                        <select class="form-control" id="exportClass" name="class">
                            <option value="">All Classes</option>
                            <?php if ($classes): ?>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['section_id']; ?>">
                                        Grade <?php echo htmlspecialchars($class['grade']); ?> - <?php echo htmlspecialchars($class['section']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exportRemarks">Include Remarks (Optional)</label>
                        <select class="form-control" id="exportRemarks" name="remarks">
                            <option value="all">All Remarks</option>
                            <option value="present">Present Only</option>
                            <option value="absent">Absent Only</option>
                            <option value="late">Late Only</option>
                            <option value="excused">Excused Only</option>
                            <option value="none">No Remarks</option>
                        </select>
                    </div>
                </form>
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmExport">
                    <i class="bx bx-download"></i> Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom styles for attendance page -->
<style>
/* Reduce negative space */
.card-body {
    padding: 1rem;
}
.mb-3 {
    margin-bottom: 0.75rem !important;
}
.py-4 {
    padding-top: 1.5rem !important;
    padding-bottom: 1.5rem !important;
}

/* Make header badge smaller */
.card-header .badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
</style>

<!-- Include attendance JavaScript -->
<script src="../assets/js/attendance.js?v=<?php echo time(); ?>"></script>