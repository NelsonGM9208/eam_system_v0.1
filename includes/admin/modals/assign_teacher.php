<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
require_once __DIR__ . "/../../../utils/index.php";
require_once __DIR__ . "/../../../config/database.php";

if (!isset($_GET['id'])) {
    echo "<div class='modal-body'><p class='text-danger'>No class ID provided.</p></div>";
    exit;
}

$sectionId = intval($_GET['id']);

// Fetch class details
$query = "SELECT s.*, CONCAT(u.firstname, ' ', u.lastname) as teacher_name 
          FROM section s 
          LEFT JOIN users u ON s.adviser_id = u.user_id 
          WHERE s.section_id = ?";
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

// Get available teachers
$teachers_query = "SELECT u.user_id, CONCAT(u.firstname, ' ', u.lastname) as name, u.email 
                   FROM users u 
                   WHERE u.role = 'teacher' AND u.status = 'Approved' 
                   ORDER BY u.firstname, u.lastname";
$teachers_result = mysqli_query($con, $teachers_query);
?>

<!-- Assign Teacher Modal -->
<div class="modal fade" id="assignTeacherModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bx bx-user-plus"></i> Assign Teacher
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="assignTeacherForm">
                <input type="hidden" id="assignSectionId" name="section_id" value="<?= $class['section_id'] ?>">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i>
                        <strong>Current Class:</strong> Grade <?= htmlspecialchars($class['grade_level']) ?> - <?= htmlspecialchars($class['section_name']) ?>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="assignTeacherSelect" class="form-label">Select Teacher <span class="text-danger">*</span></label>
                            <select class="form-control" id="assignTeacherSelect" name="teacher_id" required>
                                <option value="">Select Teacher</option>
                                <?php while ($teacher = mysqli_fetch_assoc($teachers_result)): ?>
                                    <option value="<?= $teacher['user_id'] ?>" <?= ($class['adviser_id'] == $teacher['user_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($teacher['name']) ?> (<?= htmlspecialchars($teacher['email']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="bx bx-info-circle"></i>
                                <strong>Note:</strong> Assigning a new teacher will replace the current teacher for this class.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="bx bx-user-plus"></i> Assign Teacher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
