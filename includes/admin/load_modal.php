<?php
/**
 * Modal Loader
 * Handles loading of modal content via AJAX
 */

// Define IN_APP to allow access to utilities
define('IN_APP', true);

// Include utilities
require_once __DIR__ . "/../../utils/index.php";

// Get the modal type and parameters
$modalType = $_GET['modal'] ?? '';
$userId = validateInput($_GET['id'] ?? '', 'int', 0);

// Validate required parameters
if (!$modalType || !$userId) {
    echo "<div class='modal-body'><p class='text-danger'>Invalid request parameters.</p></div>";
    exit;
}

// First fetch user details with role-specific information
$query = "SELECT u.*, 
                 s.lrn, s.mis_id,
                 t.course
          FROM users u 
          LEFT JOIN students s ON u.user_id = s.student_id 
          LEFT JOIN teacher t ON u.user_id = t.teacher_id 
          WHERE u.user_id = ?";
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

// Route to appropriate modal based on user role
$userRole = $user['role'] ?? 'user';
switch ($userRole) {
    case 'student':
        // Generate student-specific modal content
        $modalTitle = "Student Details";
        $roleBadgeClass = getBadgeClass($userRole, 'role'); // badge-info (blue)
        $roleSpecificInfo = "";
        
        // Add student-specific information
        if (!empty($user['lrn'])) {
            $roleSpecificInfo .= "<tr><th>LRN (Learner Reference Number)</th><td>" . sanitizeOutput($user['lrn']) . "</td></tr>";
        }
        if (!empty($user['mis_id'])) {
            $roleSpecificInfo .= "<tr><th>MIS ID</th><td>" . sanitizeOutput($user['mis_id']) . "</td></tr>";
        }
        break;
        
    case 'teacher':
        // Generate teacher-specific modal content
        $modalTitle = "Teacher Details";
        $roleBadgeClass = getBadgeClass($userRole, 'role'); // badge-warning (yellow/orange)
        $roleSpecificInfo = "";
        
        // Add teacher-specific information
        if (!empty($user['course'])) {
            $roleSpecificInfo .= "<tr><th>Course/Subject</th><td>" . sanitizeOutput($user['course']) . "</td></tr>";
        }
        break;
        
    case 'admin':
    case 'sslg':
        // For admin and sslg roles, use the generic user_details modal
        $modalTitle = "User Details";
        $roleBadgeClass = getBadgeClass($userRole, 'role'); // badge-danger for admin, badge-success for sslg
        $roleSpecificInfo = "";
        break;
}

// Generate the modal content
if ($modalType === 'user_details') {
        ?>
        
        <!-- Only generate modal body content, not the full modal structure -->
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
                    <span class="badge <?php echo $roleBadgeClass; ?>">
                      <?php echo sanitizeOutput(ucfirst($user['role'] ?: 'Unassigned')); ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <th>Status</th>
                  <td>
                    <span class="badge <?php echo getBadgeClass($user['status'] ?? 'Pending', 'status'); ?>">
                      <?php echo sanitizeOutput(ucfirst($user['status'] ?? 'Pending')); ?>
                    </span>
                  </td>
                </tr>
                <tr>
                  <th>Verification Status</th>
                  <td>
                    <span class="badge <?php echo getBadgeClass($user['verification_status'] ?? 'unverified', 'verification'); ?>">
                      <?php echo sanitizeOutput(ucfirst($user['verification_status'] ?? 'unverified')); ?>
                    </span>
                  </td>
                </tr>
                
                <!-- Role-specific information -->
                <?php echo $roleSpecificInfo; ?>
                
                <tr>
                  <th>Created At</th>
                  <td><?php echo formatDate($user['created_at'] ?? ''); ?></td>
                </tr>
                <tr>
                  <th>Last Updated</th>
                  <td><?php echo formatDate($user['updated_at'] ?? ''); ?></td>
                </tr>
              </table>
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
                <h6 class="mb-1"><?php echo sanitizeOutput($user['firstname'] . ' ' . $user['lastname']); ?></h6>
                <span class="badge <?php echo $roleBadgeClass; ?>"><?php echo sanitizeOutput(ucfirst($user['role'] ?: 'Unassigned')); ?></span>
                <?php if ($userRole === 'student' && !empty($user['lrn'])): ?>
                  <div class="mt-2">
                    <small class="text-muted">LRN: <?php echo sanitizeOutput($user['lrn']); ?></small>
                  </div>
                <?php endif; ?>
                <?php if ($userRole === 'student' && !empty($user['mis_id'])): ?>
                  <div class="mt-1">
                    <small class="text-muted">MIS ID: <?php echo sanitizeOutput($user['mis_id']); ?></small>
                  </div>
                <?php endif; ?>
                <?php if ($userRole === 'teacher' && !empty($user['course'])): ?>
                  <div class="mt-2">
                    <small class="text-muted">Course: <?php echo sanitizeOutput($user['course']); ?></small>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php
        } else {
            echo "<div class='modal-body'><p class='text-danger'>Invalid modal type for admin user.</p></div>";
        }
?>
