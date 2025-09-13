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
$query = "SELECT * FROM users WHERE user_id = ?";
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
    echo "<div class='modal-body'><p class='text-danger'>User not found.</p></div>";
    exit;
}

// Get role-specific information from respective tables
$currentLrn = '';
$currentMisId = '';
$currentCourse = '';

if ($user['role'] === 'student') {
    // Get LRN and MIS ID from students table
    $studentStmt = $con->prepare("SELECT lrn, mis_id FROM students WHERE student_id = ?");
    if ($studentStmt) {
        $studentStmt->bind_param("i", $user['user_id']);
        $studentStmt->execute();
        $studentResult = $studentStmt->get_result();
        if ($studentRow = $studentResult->fetch_assoc()) {
            $currentLrn = $studentRow['lrn'];
            $currentMisId = $studentRow['mis_id'];
        }
        $studentStmt->close();
    }
} elseif ($user['role'] === 'teacher') {
    // Get course from teacher table
    $teacherStmt = $con->prepare("SELECT course FROM teacher WHERE teacher_id = ?");
    if ($teacherStmt) {
        $teacherStmt->bind_param("i", $user['user_id']);
        $teacherStmt->execute();
        $teacherResult = $teacherStmt->get_result();
        if ($teacherRow = $teacherResult->fetch_assoc()) {
            $currentCourse = $teacherRow['course'];
        }
        $teacherStmt->close();
    }
}
?>

<form id="editUserForm">
  <div class="modal-body">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?php echo $user['user_id']; ?>">
    
    <div class="form-group">
      <label for="firstname">First Name</label>
      <input type="text" class="form-control" id="firstname" name="firstname" 
             value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
    </div>
    
    <div class="form-group">
      <label for="lastname">Last Name</label>
      <input type="text" class="form-control" id="lastname" name="lastname" 
             value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
    </div>
    
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" class="form-control" id="email" name="email" 
             value="<?php echo htmlspecialchars($user['email']); ?>" required>
    </div>
    
    <div class="form-group">
      <label for="role">Role</label>
      <select class="form-control" id="role" name="role" required>
        <option value="">Select Role</option>
        <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
        <option value="teacher" <?php echo ($user['role'] == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
        <option value="student" <?php echo ($user['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
        <option value="sslg" <?php echo ($user['role'] == 'sslg') ? 'selected' : ''; ?>>SSLG</option>
      </select>
    </div>
    
    
    <!-- Student-specific fields -->
    <div id="studentFields" style="display: none;">
      <div class="form-group">
        <label for="lrn">LRN (Learner Reference Number)</label>
        <input type="text" class="form-control" id="lrn" name="lrn" 
               value="<?php echo htmlspecialchars($currentLrn ?: ($user['lrn'] ?? '')); ?>" 
               placeholder="Enter LRN number">
      </div>
      <div class="form-group">
        <label for="mis_id">MIS ID</label>
        <input type="text" class="form-control" id="mis_id" name="mis_id" 
               value="<?php echo htmlspecialchars($currentMisId ?: ($user['mis_id'] ?? '')); ?>" 
               placeholder="Enter MIS ID">
      </div>
    </div>
    
    <!-- Teacher-specific fields -->
    <div id="teacherFields" class="form-group" style="display: none;">
      <label for="course">Course/Subject</label>
      <input type="text" class="form-control" id="course" name="course" 
             value="<?php echo htmlspecialchars($currentCourse ?: ($user['course'] ?? '')); ?>" 
             placeholder="Enter course or subject">
    </div>
    
    <!-- Status (Read-only) -->
    <div class="form-group">
      <label for="status">Account Status</label>
      <input type="text" class="form-control" id="status" name="status" 
             value="<?php echo htmlspecialchars($user['status'] ?? 'Pending'); ?>" 
             readonly style="background-color: #f8f9fa;">
      <small class="form-text text-muted">Account status cannot be edited</small>
    </div>
    
    <!-- Verification Status (Read-only) -->
    <div class="form-group">
      <label for="verification">Verification Status</label>
      <input type="text" class="form-control" id="verification" name="verification" 
             value="<?php echo htmlspecialchars($user['verification_status'] ?? 'notverified'); ?>" 
             readonly style="background-color: #f8f9fa;">
      <small class="form-text text-muted">Verification status cannot be edited</small>
    </div>
  </div>

  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
    <button type="submit" class="btn btn-primary">Update User</button>
  </div>
</form>

<script>
$(document).ready(function(){
    // Function to show/hide role-specific fields
    function toggleRoleFields() {
        const selectedRole = $('#role').val();
        
        // Hide all role-specific fields first
        $('#studentFields, #teacherFields').hide();
        
        // Show relevant fields based on selected role
        if (selectedRole === 'student') {
            $('#studentFields').show();
        } else if (selectedRole === 'teacher') {
            $('#teacherFields').show();
        }
    }
    
    // Initial call to set correct field visibility
    toggleRoleFields();
    
    // Show fields immediately if user already has student or teacher role
    const currentRole = '<?php echo $user['role']; ?>';
    if (currentRole === 'student') {
        $('#studentFields').show();
    } else if (currentRole === 'teacher') {
        $('#teacherFields').show();
    }
    
    // Listen for role changes
    $('#role').on('change', toggleRoleFields);
    
    // Form submission
    $('#editUserForm').on('submit', function(e){
        e.preventDefault();
        
        // Validate role-specific fields
        const selectedRole = $('#role').val();
        if (selectedRole === 'student') {
            if (!$('#lrn').val().trim()) {
                alert('Please enter LRN for student role');
                $('#lrn').focus();
                return;
            }
            if (!$('#mis_id').val().trim()) {
                alert('Please enter MIS ID for student role');
                $('#mis_id').focus();
                return;
            }
        }
        if (selectedRole === 'teacher' && !$('#course').val().trim()) {
            alert('Please enter course/subject for teacher role');
            $('#course').focus();
            return;
        }
        
        $.post('/eam_system_v0.1.1/config/users_crud.php', $(this).serialize(), function(response){
            alert(response);
            if(response.includes('successfully')) {
                $('#editModal').modal('hide');
                location.reload();
            }
        });
    });
});
</script>
