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
$query = "SELECT * FROM section WHERE section_id = ?";
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

<!-- Edit Class Modal -->
<div class="modal fade" id="editClassModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="bx bx-edit"></i> Edit Class
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editClassForm">
                <input type="hidden" id="editSectionId" name="section_id" value="<?= $class['section_id'] ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="editGrade" class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-control" id="editGrade" name="grade" required>
                                <option value="">Select Grade</option>
                                <option value="7" <?= ($class['grade_level'] == '7') ? 'selected' : '' ?>>Grade 7</option>
                                <option value="8" <?= ($class['grade_level'] == '8') ? 'selected' : '' ?>>Grade 8</option>
                                <option value="9" <?= ($class['grade_level'] == '9') ? 'selected' : '' ?>>Grade 9</option>
                                <option value="10" <?= ($class['grade_level'] == '10') ? 'selected' : '' ?>>Grade 10</option>
                                <option value="11" <?= ($class['grade_level'] == '11') ? 'selected' : '' ?>>Grade 11</option>
                                <option value="12" <?= ($class['grade_level'] == '12') ? 'selected' : '' ?>>Grade 12</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="editSection" class="form-label">Section Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editSection" name="section" value="<?= htmlspecialchars($class['section_name']) ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3"><?= htmlspecialchars($class['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-save"></i> Update Class
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
