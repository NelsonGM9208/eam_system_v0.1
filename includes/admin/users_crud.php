<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../utils/index.php";

// Ensure session is started with proper configuration
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters to match the main application
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Refresh session activity to prevent timeout
if (isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

// Debug: Check if we can access the session
error_log("Session ID: " . session_id());
error_log("Session name: " . session_name());
error_log("Session save path: " . session_save_path());

// Helper function to sanitize input
function sanitize($data)
{
    global $con;
    return mysqli_real_escape_string($con, trim($data));
}

// Helper function to get current admin user
function getCurrentAdmin()
{
    // Debug: Log session information
    error_log("Session debug - user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
    error_log("Session debug - role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET'));
    error_log("Session debug - email: " . (isset($_SESSION['email']) ? $_SESSION['email'] : 'NOT SET'));
    error_log("Session debug - last_activity: " . (isset($_SESSION['last_activity']) ? $_SESSION['last_activity'] : 'NOT SET'));
    
    // Check if session has expired
    if (isset($_SESSION['last_activity'])) {
        $timeoutSeconds = 1800; // 30 minutes
        if ((time() - $_SESSION['last_activity']) > $timeoutSeconds) {
            error_log("Session expired - last activity: " . $_SESSION['last_activity'] . ", current time: " . time());
            return 'System';
        }
    }
    
    // Check for admin role using multiple session variables
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        error_log("Returning admin user_id: " . $_SESSION['user_id']);
        return $_SESSION['user_id'];
    }
    
    // Alternative check using email if user_id is not set
    if (isset($_SESSION['email']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        // Try to get user_id from database using email
        global $con;
        $stmt = $con->prepare("SELECT user_id FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $_SESSION['email']);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    error_log("Found admin user_id from email: " . $row['user_id']);
                    $stmt->close();
                    return $row['user_id'];
                }
            }
            $stmt->close();
        }
    }
    
    // If we still can't find a valid admin, return 0 instead of 'System'
    // This will set updated_by to 0, which indicates "System" in the display
    error_log("No valid admin session found, returning 0 for updated_by");
    return 0;
}

// Handle Read (for View modal)
if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $con->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
    if (!$stmt) {
        echo "Database error: " . $con->error;
        exit;
    }

    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        echo "Database error: " . $stmt->error;
        exit;
    }

    $result = $stmt->get_result();
    if ($user = mysqli_fetch_assoc($result)) {
        echo "<p><strong>Name:</strong> " . htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
        echo "<p><strong>Role:</strong> " . htmlspecialchars($user['role']) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($user['status']) . "</p>";
        echo "<p><strong>Verification:</strong> " . htmlspecialchars($user['verification_status']) . "</p>";
    } else {
        echo "<p>User not found.</p>";
    }
    exit;
}

// Handle Update (from Edit modal)
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = intval($_POST['id']);
    $firstname = sanitize($_POST['firstname']);
    $lastname = sanitize($_POST['lastname']);
    $email = sanitize($_POST['email']);
    $role = sanitize($_POST['role']);
    $status = sanitize($_POST['status']);
    $verification = sanitize($_POST['verification']);

    // Get role-specific fields
    $lrn = sanitize($_POST['lrn'] ?? '');
    $course = sanitize($_POST['course'] ?? '');

    // Get current admin user and log the result
    $updatedBy = getCurrentAdmin();
    error_log("Update operation - updatedBy: " . $updatedBy . " for user ID: " . $id);

    // Start transaction for multiple table updates
    $con->begin_transaction();

    try {
        // First, get the current user's role to check if it's changing
        $currentRoleStmt = $con->prepare("SELECT role FROM users WHERE user_id = ?");
        if (!$currentRoleStmt) {
            throw new Exception("Database error: " . $con->error);
        }
        $currentRoleStmt->bind_param("i", $id);
        if (!$currentRoleStmt->execute()) {
            throw new Exception("Database error: " . $currentRoleStmt->error);
        }
        $currentRoleResult = $currentRoleStmt->get_result();
        $currentUser = $currentRoleResult->fetch_assoc();
        $currentRole = $currentUser['role'] ?? '';
        $currentRoleStmt->close();

        // Update users table - include updated_at and updated_by
        $stmt = $con->prepare("UPDATE users 
        SET firstname=?, lastname=?, email=?, role=?, status=?, verification_status=?, updated_at=NOW(), updated_by=? 
        WHERE user_id=?");
        if (!$stmt) {
            throw new Exception("Database error: " . $con->error);
        }
        
        $stmt->bind_param(
            "sssssssi", // 7 strings + 1 integer
            $firstname,
            $lastname,
            $email,
            $role,
            $status,
            $verification,
            $updatedBy,
            $id
        );

        if (!$stmt->execute()) {
            throw new Exception("Error updating user: " . $stmt->error);
        }
        $stmt->close();

        // Handle role-specific table operations
        if ($role === 'student') {
            // Check if student record already exists
            $checkStudentStmt = $con->prepare("SELECT student_id FROM students WHERE student_id = ?");
            if (!$checkStudentStmt) {
                throw new Exception("Database error: " . $con->error);
            }
            $checkStudentStmt->bind_param("i", $id);
            $checkStudentStmt->execute();
            $studentExists = $checkStudentStmt->get_result()->num_rows > 0;
            $checkStudentStmt->close();

            if (!$studentExists) {
                // Create new student record with student_id, lrn, and created_at
                $insertStudentStmt = $con->prepare("INSERT INTO students (student_id, lrn) VALUES (?, ?)");
                if (!$insertStudentStmt) {
                    throw new Exception("Database error: " . $con->error);
                }
                $insertStudentStmt->bind_param("is", $id, $lrn);
                if (!$insertStudentStmt->execute()) {
                    throw new Exception("Error creating student record: " . $insertStudentStmt->error);
                }
                $insertStudentStmt->close();
            } else {
                // Update existing student record
                $updateStudentStmt = $con->prepare("UPDATE students SET lrn = ? WHERE student_id = ?");
                if (!$updateStudentStmt) {
                    throw new Exception("Database error: " . $con->error);
                }
                $updateStudentStmt->bind_param("si", $lrn, $id);
                if (!$updateStudentStmt->execute()) {
                    throw new Exception("Error updating student record: " . $updateStudentStmt->error);
                }
                $updateStudentStmt->close();
            }

            // Remove from teacher table if user was previously a teacher
            if ($currentRole === 'teacher') {
                $deleteTeacherStmt = $con->prepare("DELETE FROM teacher WHERE teacher_id = ?");
                if ($deleteTeacherStmt) {
                    $deleteTeacherStmt->bind_param("i", $id);
                    $deleteTeacherStmt->execute();
                    $deleteTeacherStmt->close();
                }
            }

        } elseif ($role === 'teacher') {
            // Check if teacher record already exists
            $checkTeacherStmt = $con->prepare("SELECT teacher_id FROM teacher WHERE teacher_id = ?");
            if (!$checkTeacherStmt) {
                throw new Exception("Database error: " . $con->error);
            }
            $checkTeacherStmt->bind_param("i", $id);
            $checkTeacherStmt->execute();
            $teacherExists = $checkTeacherStmt->get_result()->num_rows > 0;
            $checkTeacherStmt->close();

            if (!$teacherExists) {
                // Create new teacher record with teacher_id and course
                $insertTeacherStmt = $con->prepare("INSERT INTO teacher (teacher_id, course) VALUES (?, ?)");
                if (!$insertTeacherStmt) {
                    throw new Exception("Database error: " . $con->error);
                }
                $insertTeacherStmt->bind_param("is", $id, $course);
                if (!$insertTeacherStmt->execute()) {
                    throw new Exception("Error creating teacher record: " . $insertTeacherStmt->error);
                }
                $insertTeacherStmt->close();
            } else {
                // Update existing teacher record
                $updateTeacherStmt = $con->prepare("UPDATE teacher SET course = ? WHERE teacher_id = ?");
                if (!$updateTeacherStmt) {
                    throw new Exception("Database error: " . $con->error);
                }
                $updateTeacherStmt->bind_param("si", $course, $id);
                if (!$updateTeacherStmt->execute()) {
                    throw new Exception("Error updating teacher record: " . $updateTeacherStmt->error);
                }
                $updateTeacherStmt->close();
            }

            // Remove from students table if user was previously a student
            if ($currentRole === 'student') {
                $deleteStudentStmt = $con->prepare("DELETE FROM students WHERE student_id = ?");
                if ($deleteStudentStmt) {
                    $deleteStudentStmt->bind_param("i", $id);
                    $deleteStudentStmt->execute();
                    $deleteStudentStmt->close();
                }
            }

        } else {
            // For non-student/teacher roles, remove from both tables
            $deleteStudentStmt = $con->prepare("DELETE FROM students WHERE student_id = ?");
            if ($deleteStudentStmt) {
                $deleteStudentStmt->bind_param("i", $id);
                $deleteStudentStmt->execute();
                $deleteStudentStmt->close();
            }

            $deleteTeacherStmt = $con->prepare("DELETE FROM teacher WHERE teacher_id = ?");
            if ($deleteTeacherStmt) {
                $deleteTeacherStmt->bind_param("i", $id);
                $deleteTeacherStmt->execute();
                $deleteTeacherStmt->close();
            }
        }

        // Check if status changed from Pending to Approved and send email
        if ($status === 'Approved') {
            // Get the previous status from the database to check if it was Pending
            $prevStatusStmt = $con->prepare("SELECT status FROM users WHERE user_id = ?");
            if ($prevStatusStmt) {
                $prevStatusStmt->bind_param("i", $id);
                $prevStatusStmt->execute();
                $prevStatusResult = $prevStatusStmt->get_result();
                if ($prevStatusRow = $prevStatusResult->fetch_assoc()) {
                    $previousStatus = $prevStatusRow['status'];
                    if ($previousStatus === 'Pending') {
                        // Send approval email
                        $fullName = $firstname . ' ' . $lastname;
                        if (sendUserApprovalEmail($email, $fullName, $role)) {
                            echo "User updated successfully and approval email sent!";
                        } else {
                            echo "User updated successfully but failed to send approval email.";
                        }
                    } else {
                        echo "User updated successfully.";
                    }
                } else {
                    echo "User updated successfully.";
                }
                $prevStatusStmt->close();
            } else {
                echo "User updated successfully.";
            }
        }
        
        // Commit transaction
        $con->commit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $con->rollback();
        echo "Error: " . $e->getMessage();
    }
    exit;
}

// Email functions are now handled by the email utility

// Handle Delete
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Get user details before deletion for email notification
    $userStmt = $con->prepare("SELECT firstname, lastname, email, role FROM users WHERE user_id = ?");
    if (!$userStmt) {
        echo "Error: Database error - " . $con->error;
        exit;
    }
    
    $userStmt->bind_param("i", $id);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userData = $userResult->fetch_assoc();
    $userStmt->close();
    
    if (!$userData) {
        echo "Error: User not found.";
        exit;
    }

    // Start transaction for multiple table deletes
    $con->begin_transaction();

    try {
        // Delete from students table if exists
        $deleteStudentStmt = $con->prepare("DELETE FROM students WHERE student_id = ?");
        if ($deleteStudentStmt) {
            $deleteStudentStmt->bind_param("i", $id);
            $deleteStudentStmt->execute();
            $deleteStudentStmt->close();
        }

        // Delete from teacher table if exists
        $deleteTeacherStmt = $con->prepare("DELETE FROM teacher WHERE teacher_id = ?");
        if ($deleteTeacherStmt) {
            $deleteTeacherStmt->bind_param("i", $id);
            $deleteTeacherStmt->execute();
            $deleteTeacherStmt->close();
        }

        // Finally delete from users table
        $stmt = $con->prepare("DELETE FROM users WHERE user_id=?");
        if (!$stmt) {
            throw new Exception("Database error: " . $con->error);
        }

        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $con->commit();
            
            // Send removal email notification
            $fullName = $userData['firstname'] . ' ' . $userData['lastname'];
            $emailSent = sendUserRemovalEmail($userData['email'], $fullName, $userData['role']);
            
            if ($emailSent) {
                echo "User deleted successfully and removal notification sent.";
            } else {
                echo "User deleted successfully, but failed to send removal notification.";
            }
        } else {
            throw new Exception("Error deleting user: " . $stmt->error);
        }

    } catch (Exception $e) {
        // Rollback transaction on error
        $con->rollback();
        echo "Error: " . $e->getMessage();
    }
    exit;
}

// Handle Reject
if (isset($_POST['action']) && $_POST['action'] === 'reject' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Start transaction
    $con->begin_transaction();
    
    try {
        // Get user details for email
        $userStmt = $con->prepare("SELECT firstname, lastname, email, role FROM users WHERE user_id = ?");
        if (!$userStmt) {
            throw new Exception("Database error: " . $con->error);
        }
        
        $userStmt->bind_param("i", $id);
        if (!$userStmt->execute()) {
            throw new Exception("Database error: " . $userStmt->error);
        }
        
        $userResult = $userStmt->get_result();
        $user = $userResult->fetch_assoc();
        
        if (!$user) {
            throw new Exception("User not found");
        }
        
        // Update user status to rejected
        $updateStmt = $con->prepare("UPDATE users SET status = 'Rejected', updated_by = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        if (!$updateStmt) {
            throw new Exception("Database error: " . $con->error);
        }
        
        $adminId = getCurrentAdmin();
        $updateStmt->bind_param("ii", $adminId, $id);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Error rejecting user: " . $updateStmt->error);
        }
        
        // Send rejection email
        $fullName = $user['firstname'] . ' ' . $user['lastname'];
        if (sendUserRejectionEmail($user['email'], $fullName, $user['role'])) {
            echo "User rejected successfully and rejection email sent!";
        } else {
            echo "User rejected successfully but failed to send rejection email.";
        }
        
        // Commit transaction
        $con->commit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $con->rollback();
        echo "Error: " . $e->getMessage();
    }
    exit;
}

// Handle Approve
if (isset($_POST['action']) && $_POST['action'] === 'approve' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Start transaction
    $con->begin_transaction();
    
    try {
        // Get user details for email
        $userStmt = $con->prepare("SELECT firstname, lastname, email, role FROM users WHERE user_id = ?");
        if (!$userStmt) {
            throw new Exception("Database error: " . $con->error);
        }
        
        $userStmt->bind_param("i", $id);
        if (!$userStmt->execute()) {
            throw new Exception("Database error: " . $userStmt->error);
        }
        
        $userResult = $userStmt->get_result();
        $user = $userResult->fetch_assoc();
        
        if (!$user) {
            throw new Exception("User not found");
        }
        
        // Update user status to approved
        $updateStmt = $con->prepare("UPDATE users SET status = 'Approved', updated_by = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        if (!$updateStmt) {
            throw new Exception("Database error: " . $con->error);
        }
        
        $adminId = getCurrentAdmin();
        $updateStmt->bind_param("ii", $adminId, $id);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Error approving user: " . $updateStmt->error);
        }
        
        // Send approval email
        $fullName = $user['firstname'] . ' ' . $user['lastname'];
        if (sendUserApprovalEmail($user['email'], $fullName, $user['role'])) {
            echo "User approved successfully and approval email sent!";
        } else {
            echo "User approved successfully but failed to send approval email.";
        }
        
        // Commit transaction
        $con->commit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $con->rollback();
        echo "Error: " . $e->getMessage();
    }
    exit;
}

// Handle Bulk Approve
if (isset($_POST['action']) && $_POST['action'] === 'bulk_approve' && isset($_POST['user_ids'])) {
    $userIds = $_POST['user_ids'];
    
    if (!is_array($userIds) || empty($userIds)) {
        echo "No users selected for approval.";
        exit;
    }
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($userIds as $userId) {
        $id = intval($userId);
        
        // Start transaction for each user
        $con->begin_transaction();
        
        try {
            // Get user details for email
            $userStmt = $con->prepare("SELECT firstname, lastname, email, role FROM users WHERE user_id = ?");
            if (!$userStmt) {
                throw new Exception("Database error: " . $con->error);
            }
            
            $userStmt->bind_param("i", $id);
            if (!$userStmt->execute()) {
                throw new Exception("Database error: " . $userStmt->error);
            }
            
            $userResult = $userStmt->get_result();
            $user = $userResult->fetch_assoc();
            
            if (!$user) {
                throw new Exception("User not found");
            }
            
            // Update user status to approved
            $updateStmt = $con->prepare("UPDATE users SET status = 'Approved', updated_by = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
            if (!$updateStmt) {
                throw new Exception("Database error: " . $con->error);
            }
            
            $adminId = getCurrentAdmin();
            $updateStmt->bind_param("ii", $adminId, $id);
            
            if (!$updateStmt->execute()) {
                throw new Exception("Error approving user: " . $updateStmt->error);
            }
            
            // Send approval email
            $fullName = $user['firstname'] . ' ' . $user['lastname'];
            sendUserApprovalEmail($user['email'], $fullName, $user['role']); // Don't fail the whole operation if email fails
            
            // Commit transaction
            $con->commit();
            $successCount++;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $con->rollback();
            $errorCount++;
            $errors[] = "User ID {$id}: " . $e->getMessage();
        }
    }
    
    // Return result
    if ($errorCount === 0) {
        echo "All {$successCount} users approved successfully!";
    } else {
        echo "Approved {$successCount} users successfully. {$errorCount} users failed: " . implode(', ', $errors);
    }
    exit;
}
?>