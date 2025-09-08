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
                <style>
                    .form-check {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0;
                        padding: 0;
                    }
                    .form-check-input {
                        margin: 0;
                        transform: none;
                    }
                    .table td:first-child {
                        text-align: center;
                        vertical-align: middle;
                        width: 40px;
                    }
                    .table th:first-child {
                        text-align: center;
                        vertical-align: middle;
                        width: 40px;
                    }
                </style>
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
                        <div class="table-responsive" style="max-height: 400px; border: 1px solid #dee2e6; border-radius: 0.375rem;">
                            <table class="table table-sm table-hover mb-0" id="enrolledStudentsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 40px;">
                                            <div class="form-check">
                                                <input type="checkbox" id="selectAllEnrolled" class="form-check-input">
                                            </div>
                                        </th>
                                        <th>Student</th>
                                        <th>Email</th>
                                        <th>Enrolled</th>
                                        <th style="width: 80px;">Action</th>
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
                        <div class="table-responsive" style="max-height: 400px; border: 1px solid #dee2e6; border-radius: 0.375rem;">
                            <table class="table table-sm table-hover mb-0" id="availableStudentsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 40px;">
                                            <div class="form-check">
                                                <input type="checkbox" id="selectAllAvailable" class="form-check-input">
                                            </div>
                                        </th>
                                        <th>Student</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th style="width: 80px;">Action</th>
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
