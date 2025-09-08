<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
require_once __DIR__ . "/../../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// Get total number of sections
$total_query = "SELECT COUNT(*) as total FROM section";
$total_result = mysqli_query($con, $total_query);
if (!$total_result) {
    echo "<div class='alert alert-danger'>Database error: " . $con->error . "</div>";
    exit;
}
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Ensure page is within valid range
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $records_per_page;
}

// Only run the query if there are sections
if ($total_records > 0) {
    // Get sections with teacher information and student count
    $query = "SELECT s.*, 
              CONCAT(u.firstname, ' ', u.lastname) as teacher_name,
              u.email as teacher_email,
              COUNT(e.enrollment_id) as student_count
              FROM section s 
              LEFT JOIN users u ON s.teacher_id = u.user_id 
              LEFT JOIN enrollment e ON s.section_id = e.section_id AND e.status = 'Active'
              GROUP BY s.section_id
              ORDER BY s.grade ASC, s.section ASC 
              LIMIT ?, ?";

    $stmt = $con->prepare($query);
    if (!$stmt) {
        echo "<div class='alert alert-danger'>Database error: " . $con->error . "</div>";
        exit;
    }

    $stmt->bind_param("ii", $offset, $records_per_page);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = null;
}
?>

<!-- Search and Filter Section -->
<div class="row mb-3">
    <div class="col-md-6 col-sm-12 mb-2">
        <div class="input-group">
            <input type="text" class="form-control" id="classSearch" placeholder="Search classes...">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="bx bx-search"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <select class="form-control" id="gradeFilter">
            <option value="">All Grades</option>
            <option value="7">Grade 7</option>
            <option value="8">Grade 8</option>
            <option value="9">Grade 9</option>
            <option value="10">Grade 10</option>
            <option value="11">Grade 11</option>
            <option value="12">Grade 12</option>
        </select>
    </div>
    <div class="col-md-2 col-sm-6 mb-2">
        <button class="btn btn-outline-secondary w-100" id="clearFilters">
            <i class="bx bx-x"></i> Clear
        </button>
    </div>
    <div class="col-md-1 col-sm-12 mb-2">
        <button class="btn btn-warning w-100" id="bulkAssignTeacher" disabled>
            <i class="bx bx-user-plus"></i>
        </button>
    </div>
</div>

<!-- Classes Table -->
<div class="table-responsive">
    <table class="table table-hover" id="classesTable">
        <thead class="thead-light">
            <tr>
                <th style="width: 50px; text-align: center;">
                    <div class="form-check">
                        <input type="checkbox" id="selectAll" class="form-check-input">
                    </div>
                </th>
                <th>Grade</th>
                <th>Section</th>
                <th>Description</th>
                <th>Teacher</th>
                <th>Students</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="text-align: center;">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input class-checkbox" value="<?= $row['section_id'] ?>">
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-primary">Grade <?= htmlspecialchars($row['grade']) ?></span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['section']) ?></strong>
                        </td>
                        <td>
                            <?php if (!empty($row['description'])): ?>
                                <?= htmlspecialchars($row['description']) ?>
                            <?php else: ?>
                                <em class="text-muted">No description</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['teacher_name']): ?>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center mr-2">
                                        <?= strtoupper(substr($row['teacher_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold"><?= htmlspecialchars($row['teacher_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['teacher_email']) ?></small>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="badge badge-warning">No Teacher Assigned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-success"><?= $row['student_count'] ?> students</span>
                        </td>
                        <td>
                            <small class="text-muted"><?= date('M d, Y', strtotime($row['created_at'])) ?></small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-info" onclick="viewClass(<?= $row['section_id'] ?>)" title="View Details">
                                    <i class="bx bx-show"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="editClass(<?= $row['section_id'] ?>)" title="Edit Class">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="assignTeacher(<?= $row['section_id'] ?>)" title="Assign Teacher">
                                    <i class="bx bx-user-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="manageStudents(<?= $row['section_id'] ?>)" title="Manage Students">
                                    <i class="bx bx-group"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteClass(<?= $row['section_id'] ?>)" title="Delete Class">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <i class="bx bx-book-open text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No Classes Found</h5>
                            <p class="text-muted">Start by creating your first class section.</p>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#addClassModal">
                                <i class="bx bx-plus"></i> Add First Class
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <nav aria-label="Classes pagination">
        <ul class="pagination justify-content-center">
            <?php
            $pagination = calculatePagination($page, $total_pages, 5);
            foreach ($pagination as $p):
                $isActive = ($p == $page) ? 'active' : '';
                $isDisabled = ($p == '...') ? 'disabled' : '';
            ?>
                <li class="page-item <?= $isActive ?> <?= $isDisabled ?>">
                    <?php if ($p != '...'): ?>
                        <a class="page-link" href="?page=classes&num=<?= $p ?>"><?= $p ?></a>
                    <?php else: ?>
                        <span class="page-link"><?= $p ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
<?php endif; ?>
