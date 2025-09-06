<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
require_once __DIR__ . "/../../../utils/index.php";
require_once __DIR__ . "/../../../config/database.php";

// Get teachers for dropdown
$teachers_query = "SELECT u.user_id, CONCAT(u.firstname, ' ', u.lastname) as name, u.email 
                   FROM users u 
                   WHERE u.role = 'teacher' AND u.status = 'Approved' 
                   ORDER BY u.firstname, u.lastname";
$teachers_result = mysqli_query($con, $teachers_query);
?>

<!-- Add Class Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bx bx-plus"></i> Add New Class
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addClassForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="addGrade" class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-control" id="addGrade" name="grade" required>
                                <option value="">Select Grade</option>
                                <option value="7">Grade 7</option>
                                <option value="8">Grade 8</option>
                                <option value="9">Grade 9</option>
                                <option value="10">Grade 10</option>
                                <option value="11">Grade 11</option>
                                <option value="12">Grade 12</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="addSection" class="form-label">Section Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addSection" name="section" placeholder="e.g., Galileo, Beryl, etc." required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="addDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="addDescription" name="description" rows="3" placeholder="Optional description for this class section"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="addTeacher" class="form-label">Assign Teacher <span class="text-danger">*</span></label>
                            <select class="form-control" id="addTeacher" name="teacher_id" required>
                                <option value="">Select Teacher</option>
                                <?php while ($teacher = mysqli_fetch_assoc($teachers_result)): ?>
                                    <option value="<?= $teacher['user_id'] ?>"><?= htmlspecialchars($teacher['name']) ?> (<?= htmlspecialchars($teacher['email']) ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Create Class
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
