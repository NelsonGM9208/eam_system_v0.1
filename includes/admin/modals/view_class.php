<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
require_once __DIR__ . "/../../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

if (!isset($_GET['id'])) {
    echo "<div class='modal-body'><p class='text-danger'>No class ID provided.</p></div>";
    exit;
}

$sectionId = intval($_GET['id']);

// Fetch class details with teacher and student count
$query = "SELECT s.*, 
          CONCAT(u.firstname, ' ', u.lastname) as teacher_name,
          u.email as teacher_email,
          COUNT(e.enrollment_id) as student_count
          FROM section s 
          LEFT JOIN users u ON s.teacher_id = u.user_id 
          LEFT JOIN enrollment e ON s.section_id = e.section_id AND e.status = 'Active'
          WHERE s.section_id = ?
          GROUP BY s.section_id";

$stmt = $con->prepare($query);
if (!$stmt) {
    echo "<div class='modal-body'><p class='text-danger'>Database error: " . $con->error . "</p></div>";
    exit;
}

$stmt->bind_param("i", $sectionId);
if (!$stmt->execute()) {
    echo "<div class='modal-body'><p class='text-danger'>Database error: " . $stmt->error . "</p></div>";
    exit;
}

$result = $stmt->get_result();
$class = $result->fetch_assoc();

if (!$class) {
    echo "<div class='modal-body'><p class='text-danger'>Class not found.</p></div>";
    exit;
}
?>

<!-- View Class Details Modal -->
<div class="modal fade" id="viewClassModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bx bx-show"></i> Class Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Class Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Grade:</strong></td>
                                <td><span class="badge badge-primary">Grade <?= htmlspecialchars($class['grade']) ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Section:</strong></td>
                                <td><?= htmlspecialchars($class['section']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Description:</strong></td>
                                <td><?= $class['description'] ? htmlspecialchars($class['description']) : '<em class="text-muted">No description</em>' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td><?= date('M d, Y', strtotime($class['created_at'])) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Teacher Information</h6>
                        <?php if ($class['teacher_name']): ?>
                            <div class="d-flex align-items-center">
                                <div class="avatar-lg bg-info text-white rounded-circle d-flex align-items-center justify-content-center mr-3">
                                    <?= strtoupper(substr($class['teacher_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="font-weight-bold"><?= htmlspecialchars($class['teacher_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($class['teacher_email']) ?></small>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="badge badge-warning">No Teacher Assigned</span>
                        <?php endif; ?>
                        
                        <h6 class="mt-3">Student Count</h6>
                        <span class="badge badge-success badge-lg"><?= $class['student_count'] ?> students enrolled</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
