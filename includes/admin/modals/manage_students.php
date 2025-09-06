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

<!-- Manage Students Modal -->
<div class="modal fade" id="manageStudentsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bx bx-group"></i> Manage Students
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bx bx-info-circle"></i>
                    <strong>Class:</strong> Grade <?= htmlspecialchars($class['grade']) ?> - <?= htmlspecialchars($class['section']) ?>
                </div>
                
                <!-- Enrolled Students Section -->
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-success">
                            <i class="bx bx-check-circle"></i> Enrolled Students
                        </h6>
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm table-hover" id="enrolledStudentsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAllEnrolled" class="form-check-input">
                                        </th>
                                        <th>Student</th>
                                        <th>Email</th>
                                        <th>Enrolled</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="enrolledStudentsBody">
                                    <!-- Enrolled students will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-danger" id="bulkUnenroll" disabled>
                                <i class="bx bx-user-minus"></i> Unenroll Selected
                            </button>
                        </div>
                    </div>
                    
                    <!-- Available Students Section -->
                    <div class="col-md-6">
                        <h6 class="text-primary">
                            <i class="bx bx-user-plus"></i> Available Students
                        </h6>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm" id="studentSearch" placeholder="Search students...">
                        </div>
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm table-hover" id="availableStudentsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAllAvailable" class="form-check-input">
                                        </th>
                                        <th>Student</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="availableStudentsBody">
                                    <!-- Available students will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-success" id="bulkEnroll" disabled>
                                <i class="bx bx-user-plus"></i> Enroll Selected
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
