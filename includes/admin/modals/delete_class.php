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

// Fetch class details with student count
$query = "SELECT s.*, COUNT(e.enrollment_id) as student_count
          FROM section s 
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteClassModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bx bx-trash"></i> Delete Class
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bx bx-error"></i>
                    <strong>Warning!</strong> This action cannot be undone.
                </div>
                <p>Are you sure you want to delete this class section?</p>
                <div class="bg-light p-3 rounded">
                    <strong>Class:</strong> Grade <?= htmlspecialchars($class['grade']) ?> - <?= htmlspecialchars($class['section']) ?><br>
                    <strong>Students:</strong> <?= $class['student_count'] ?> enrolled
                </div>
                <div class="mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmDelete">
                        <label class="form-check-label" for="confirmDelete">
                            I understand this will permanently delete the class and all associated data
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteClass" disabled>
                    <i class="bx bx-trash"></i> Delete Class
                </button>
            </div>
        </div>
    </div>
</div>
