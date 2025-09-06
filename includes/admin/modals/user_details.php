<?php
// Include utilities
require_once __DIR__ . "/../../../utils/index.php";

// Validate input using common utility
$userId = validateInput($_GET['id'] ?? '', 'int', 0);
if (!$userId) {
    echo "<div class='modal-body'><p class='text-danger'>No user ID provided.</p></div>";
    exit;
}

// Fetch user details using common utility
$query = "SELECT * FROM users WHERE user_id = ?";
$result = executeQuery($query, [$userId], 'i');
if (!$result) {
    echo "<div class='modal-body'><p class='text-danger'>Database error occurred.</p></div>";
    exit;
}

$user = $result->fetch_assoc();
if (!$user) {
    echo "<div class='modal-body'><p class='text-danger'>User not found.</p></div>";
    exit;
}
?>

<div class="modal-header">
  <h5 class="modal-title">User Details</h5>
  <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>

<div class="modal-body">
  <div class="row">
    <div class="col-md-8">
      <table class="table table-bordered">
        <tr>
          <th>User ID</th>
          <td><?php echo sanitizeOutput($user['user_id']); ?></td>
        </tr>
        <tr>
          <th>First Name</th>
          <td><?php echo sanitizeOutput($user['firstname']); ?></td>
        </tr>
        <tr>
          <th>Last Name</th>
          <td><?php echo sanitizeOutput($user['lastname']); ?></td>
        </tr>
        <tr>
          <th>Email</th>
          <td><?php echo sanitizeOutput($user['email']); ?></td>
        </tr>
        <tr>
          <th>Gender</th>
          <td><?php echo sanitizeOutput($user['gender'] ?? 'N/A'); ?></td>
        </tr>
        <tr>
          <th>Role</th>
          <td>
            <span class="badge <?php echo getBadgeClass($user['role'] ?? '', 'role'); ?>">
              <?php echo sanitizeOutput($user['role'] ?: 'Unassigned'); ?>
            </span>
          </td>
        </tr>
        <tr>
          <th>Status</th>
          <td>
            <span class="badge <?php echo getBadgeClass($user['status'] ?? 'Pending', 'status'); ?>">
              <?php echo sanitizeOutput($user['status'] ?? 'Pending'); ?>
            </span>
          </td>
        </tr>
        <tr>
          <th>Verification Status</th>
          <td>
            <span class="badge <?php echo getBadgeClass($user['verification_status'] ?? 'unverified', 'verification'); ?>">
              <?php echo sanitizeOutput($user['verification_status'] ?? 'unverified'); ?>
            </span>
          </td>
        </tr>
        
        <!-- Role-specific information -->
        <?php if ($user['role'] === 'student' && !empty($user['lrn'])): ?>
        <tr>
          <th>LRN (Learner Reference Number)</th>
          <td><?php echo sanitizeOutput($user['lrn']); ?></td>
        </tr>
        <?php endif; ?>
        
        <?php if ($user['role'] === 'teacher' && !empty($user['course'])): ?>
        <tr>
          <th>Course/Subject</th>
          <td><?php echo sanitizeOutput($user['course']); ?></td>
        </tr>
        <?php endif; ?>
        
        <tr>
          <th>Created At</th>
          <td><?php echo formatDate($user['created_at'] ?? ''); ?></td>
        </tr>
        <tr>
          <th>Last Updated</th>
          <td><?php echo formatDate($user['updated_at'] ?? ''); ?></td>
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
        <span class="badge badge-<?php 
          echo ($user['role'] == 'admin') ? 'danger' : 
               (($user['role'] == 'teacher') ? 'info' : 
               (($user['role'] == 'student') ? 'success' : 'secondary')); 
        ?>">
          <?php echo htmlspecialchars($user['role'] ?: 'Unassigned'); ?>
        </span>
      </div>
    </div>
  </div>
</div>

<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>
