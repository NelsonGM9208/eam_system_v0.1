<?php
require "../../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

if (!isset($_GET['id'])) {
    echo "<div class='modal-body'><p class='text-danger'>No attendance ID provided.</p></div>";
    exit;
}

$attendanceId = intval($_GET['id']);

// Fetch attendance details with related information
$query = "SELECT a.*, 
          CONCAT(u.firstname, ' ', u.lastname) as student_name,
          u.email as student_email,
          u.gender,
          e.title as event_title,
          e.event_description,
          e.event_date,
          e.start_time,
          e.end_time,
          e.location,
          e.event_type,
          s.grade,
          s.section,
          CONCAT(creator.firstname, ' ', creator.lastname) as event_creator
          FROM attendance a 
          LEFT JOIN users u ON a.student_id = u.user_id 
          LEFT JOIN events e ON a.event_id = e.event_id
          LEFT JOIN users creator ON e.created_by = creator.user_id
          LEFT JOIN enrollment en ON a.student_id = en.student_id AND en.status = 'Active'
          LEFT JOIN section s ON en.section_id = s.section_id
          WHERE a.attendance_id = ?";

$stmt = $con->prepare($query);
if (!$stmt) {
    echo "<div class='modal-body'><p class='text-danger'>Database error: " . $con->error . "</p></div>";
    exit;
}

$stmt->bind_param("i", $attendanceId);
if (!$stmt->execute()) {
    echo "<div class='modal-body'><p class='text-danger'>Database error: " . $stmt->error . "</p></div>";
    exit;
}

$result = $stmt->get_result();
$attendance = $result->fetch_assoc();

if (!$attendance) {
    echo "<div class='modal-body'><p class='text-danger'>Attendance record not found.</p></div>";
    exit;
}

// Format dates and times
$event_date = date('F d, Y (l)', strtotime($attendance['event_date']));
$start_time = date('h:i A', strtotime($attendance['start_time']));
$end_time = date('h:i A', strtotime($attendance['end_time']));
$check_in = date('F d, Y h:i A', strtotime($attendance['check_in_time']));
$check_out = $attendance['check_out_time'] ? date('F d, Y h:i A', strtotime($attendance['check_out_time'])) : 'Not checked out';

// Calculate duration if checked out
$duration = '';
if ($attendance['check_out_time']) {
    $check_in_time = new DateTime($attendance['check_in_time']);
    $check_out_time = new DateTime($attendance['check_out_time']);
    $interval = $check_in_time->diff($check_out_time);
    $duration = $interval->format('%h hours %i minutes');
}

// Remark badge color
$remarkClass = '';
switch ($attendance['remark']) {
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

// Event type badge color
$typeClass = ($attendance['event_type'] == 'Exclusive') ? 'badge-danger' : 'badge-primary';
?>

<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-primary mb-3">
                <i class="bx bx-user"></i> Student Information
            </h6>
            <table class="table table-bordered">
                <tr>
                    <th width="30%">Student ID</th>
                    <td><?php echo htmlspecialchars($attendance['student_id']); ?></td>
                </tr>
                <tr>
                    <th>Full Name</th>
                    <td><strong><?php echo htmlspecialchars($attendance['student_name']); ?></strong></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($attendance['student_email']); ?></td>
                </tr>
                <tr>
                    <th>Gender</th>
                    <td><?php echo htmlspecialchars($attendance['gender']); ?></td>
                </tr>
                <?php if ($attendance['grade'] && $attendance['section']): ?>
                <tr>
                    <th>Class</th>
                    <td>
                        <span class="badge badge-primary">
                            Grade <?php echo htmlspecialchars($attendance['grade']); ?> - <?php echo htmlspecialchars($attendance['section']); ?>
                        </span>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <div class="col-md-6">
            <h6 class="text-primary mb-3">
                <i class="bx bx-calendar-event"></i> Event Information
            </h6>
            <table class="table table-bordered">
                <tr>
                    <th width="30%">Event ID</th>
                    <td><?php echo htmlspecialchars($attendance['event_id']); ?></td>
                </tr>
                <tr>
                    <th>Event Title</th>
                    <td><strong><?php echo htmlspecialchars($attendance['event_title']); ?></strong></td>
                </tr>
                <tr>
                    <th>Event Date</th>
                    <td><?php echo $event_date; ?></td>
                </tr>
                <tr>
                    <th>Time</th>
                    <td><?php echo $start_time . ' - ' . $end_time; ?></td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td><?php echo htmlspecialchars($attendance['location']); ?></td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td>
                        <span class="badge <?php echo $typeClass; ?>">
                            <?php echo htmlspecialchars($attendance['event_type']); ?>
                        </span>
                    </td>
                </tr>
                <?php if ($attendance['event_creator']): ?>
                <tr>
                    <th>Created By</th>
                    <td><?php echo htmlspecialchars($attendance['event_creator']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <h6 class="text-primary mb-3">
                <i class="bx bx-time"></i> Attendance Details
            </h6>
            <table class="table table-bordered">
                <tr>
                    <th width="25%">Attendance ID</th>
                    <td><?php echo htmlspecialchars($attendance['attendance_id']); ?></td>
                </tr>
                <tr>
                    <th>Check In Time</th>
                    <td>
                        <span class="badge badge-info">
                            <?php echo $check_in; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Check Out Time</th>
                    <td>
                        <?php if ($attendance['check_out_time']): ?>
                            <span class="badge badge-success">
                                <?php echo $check_out; ?>
                            </span>
                        <?php else: ?>
                            <span class="badge badge-warning">Not checked out</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($duration): ?>
                <tr>
                    <th>Duration</th>
                    <td>
                        <span class="badge badge-primary">
                            <?php echo $duration; ?>
                        </span>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>Remark</th>
                    <td>
                        <span class="badge <?php echo $remarkClass; ?> badge-lg">
                            <?php echo htmlspecialchars($attendance['remark']); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Penalty</th>
                    <td>
                        <?php if ($attendance['penalty'] > 0): ?>
                            <span class="text-danger font-weight-bold">
                                ₱<?php echo number_format($attendance['penalty'], 2); ?>
                            </span>
                        <?php else: ?>
                            <span class="text-success font-weight-bold">₱0.00</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <?php if ($attendance['event_description']): ?>
    <div class="row mt-4">
        <div class="col-12">
            <h6 class="text-primary mb-3">
                <i class="bx bx-file-text"></i> Event Description
            </h6>
            <div class="card">
                <div class="card-body">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($attendance['event_description'])); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
