<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

if (!isset($_GET['id'])) {
    echo "<div class='modal-body'><p class='text-danger'>No user ID provided.</p></div>";
    exit;
}

$userId = intval($_GET['id']);

// Fetch user details
$query = "SELECT * FROM users WHERE user_id = ? AND role = 'teacher'";
$stmt = $con->prepare($query);
if (!$stmt) {
    echo "<div class='modal-body'><p class='text-danger'>Database error: " . $con->error . "</p></div>";
    exit;
}

$stmt->bind_param("i", $userId);
if (!$stmt->execute()) {
    echo "<div class='modal-body'><p class='text-danger'>Database error: " . $stmt->error . "</p></div>";
    exit;
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<div class='modal-body'><p class='text-danger'>Teacher not found.</p></div>";
    exit;
}

// Get teacher-specific information from teacher table
$teacherQuery = "SELECT * FROM teacher WHERE teacher_id = ?";
$teacherStmt = $con->prepare($teacherQuery);
if ($teacherStmt) {
    $teacherStmt->bind_param("i", $userId);
    $teacherStmt->execute();
    $teacherResult = $teacherStmt->get_result();
    $teacherInfo = $teacherResult->fetch_assoc();
    $teacherStmt->close();
}
?>

<div class="modal-header">
  <h5 class="modal-title">Teacher Details</h5>
  <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>

<div class="modal-body">
  <div class="row">
    <div class="col-md-8">
      <table class="table table-bordered">
        <tr>
          <th>User ID</th>
          <td><?php echo htmlspecialchars($user['user_id']); ?></td>
        </tr>
        <tr>
          <th>First Name</th>
          <td><?php echo htmlspecialchars($user['firstname']); ?></td>
        </tr>
        <tr>
          <th>Last Name</th>
          <td><?php echo htmlspecialchars($user['lastname']); ?></td>
        </tr>
        <tr>
          <th>Email</th>
          <td><?php echo htmlspecialchars($user['email']); ?></td>
        </tr>
        <tr>
          <th>Gender</th>
          <td><?php echo htmlspecialchars($user['gender'] ?? 'N/A'); ?></td>
        </tr>
        <tr>
          <th>Role</th>
          <td>
            <span class="badge badge-info">Teacher</span>
          </td>
        </tr>
        <tr>
          <th>Status</th>
          <td>
            <span class="badge badge-<?php 
              echo ($user['status'] == 'Approved') ? 'success' : 
                   (($user['status'] == 'Pending') ? 'warning' : 'danger'); 
            ?>">
              <?php echo htmlspecialchars($user['status'] ?? 'Pending'); ?>
            </span>
          </td>
        </tr>
        <tr>
          <th>Verification Status</th>
          <td>
            <span class="badge badge-<?php 
              echo ($user['verification_status'] == 'verified') ? 'success' : 'warning'; 
            ?>">
              <?php echo htmlspecialchars($user['verification_status'] ?? 'notverified'); ?>
            </span>
          </td>
        </tr>
        
        <!-- Teacher-specific information -->
        <?php if (!empty($teacherInfo['course'])): ?>
        <tr>
          <th>Course/Subject</th>
          <td><?php echo htmlspecialchars($teacherInfo['course']); ?></td>
        </tr>
        <?php endif; ?>
        
        <?php if (!empty($user['course'])): ?>
        <tr>
          <th>Course/Subject (from users table)</th>
          <td><?php echo htmlspecialchars($user['course']); ?></td>
        </tr>
        <?php endif; ?>
        
        <tr>
          <th>Created At</th>
          <td><?php echo htmlspecialchars($user['created_at'] ?? 'N/A'); ?></td>
        </tr>
        <tr>
          <th>Last Updated</th>
          <td><?php echo htmlspecialchars($user['updated_at'] ?? 'N/A'); ?></td>
        </tr>
      </table>
      
      <?php if (!empty($user['updated_at']) && $user['updated_at'] !== 'N/A'): ?>
        <div class="mt-3">
          <small class="text-muted">
            <i class="bx bx-time"></i> Last updated by <strong><?php 
              if (!empty($user['updated_by']) && $user['updated_by'] != 0 && $user['updated_by'] !== 'System') {
                // Get admin name from users table using updated_by
                $adminStmt = $con->prepare("SELECT firstname, lastname FROM users WHERE user_id = ?");
                if ($adminStmt) {
                  $adminStmt->bind_param("i", $user['updated_by']);
                  if ($adminStmt->execute()) {
                    $adminResult = $adminStmt->get_result();
                    if ($adminRow = $adminResult->fetch_assoc()) {
                      echo htmlspecialchars($adminRow['firstname'] . ' ' . $adminRow['lastname']);
                    } else {
                      echo htmlspecialchars('Admin ID: ' . $user['updated_by']);
                    }
                  } else {
                    echo htmlspecialchars('Admin ID: ' . $user['updated_by']);
                  }
                  $adminStmt->close();
                } else {
                  echo htmlspecialchars('Admin ID: ' . $user['updated_by']);
                }
              } elseif ($user['updated_by'] == 0) {
                echo 'System (Initial)';
              } elseif (empty($user['updated_by']) || $user['updated_by'] === null) {
                echo 'System (Initial)';
              } else {
                echo 'System';
              }
            ?></strong>
          </small>
        </div>
      <?php endif; ?>
    </div>
    
    <div class="col-md-4 text-center">
      <div class="profile-picture-container">
        <?php if (!empty($user['profile_photo'])): ?>
          <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" 
               class="img-fluid rounded-circle border" 
               style="width: 150px; height: 150px; object-fit: cover;" 
               alt="Profile Picture">
        <?php else: ?>
          <div class="profile-placeholder rounded-circle border d-flex align-items-center justify-content-center mx-auto"
               style="width: 150px; height: 150px; background-color: #f8f9fa; color: #6c757d;">
            <i class="bx bx-user" style="font-size: 4rem;"></i>
          </div>
        <?php endif; ?>
      </div>
      
      <div class="mt-3">
        <h6 class="mb-1"><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h6>
        <span class="badge badge-info">Teacher</span>
        <?php if (!empty($teacherInfo['course'])): ?>
          <div class="mt-2">
            <small class="text-muted">Course: <?php echo htmlspecialchars($teacherInfo['course']); ?></small>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>
