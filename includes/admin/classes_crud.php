<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
require_once __DIR__ . "/../../utils/index.php";
require_once __DIR__ . "/../../config/database.php";

// Helper functions
function getCurrentAdmin()
{
    // Check if session has expired
    if (isset($_SESSION['last_activity'])) {
        $timeoutSeconds = 1800; // 30 minutes
        if ((time() - $_SESSION['last_activity']) > $timeoutSeconds) {
            return 'System';
        }
    }
    
    // Check for admin role using multiple session variables
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
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
                    $stmt->close();
                    return $row['user_id'];
                }
            }
            $stmt->close();
        }
    }
    
    // If we still can't find a valid admin, return 0 instead of 'System'
    return 0;
}

function getCurrentUserRole()
{
    return $_SESSION['role'] ?? 'admin';
}

function logAction($admin, $role, $action)
{
    // Simple logging function - can be enhanced later
    error_log("Action logged: $action by $admin ($role) at " . date('Y-m-d H:i:s'));
}

function sanitize($data)
{
    global $con;
    return mysqli_real_escape_string($con, trim($data));
}

// Set content type to JSON
header('Content-Type: application/json');

// Get current admin user
$currentAdmin = getCurrentAdmin();
$currentRole = getCurrentUserRole();

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        createClass();
        break;
    case 'update':
        updateClass();
        break;
    case 'delete':
        deleteClass();
        break;
    case 'assign_teacher':
        assignTeacher();
        break;
    case 'bulk_assign_teacher':
        bulkAssignTeacher();
        break;
    case 'enroll_student':
        enrollStudent();
        break;
    case 'unenroll_student':
        unenrollStudent();
        break;
    case 'bulk_enroll':
        bulkEnrollStudents();
        break;
    case 'bulk_unenroll':
        bulkUnenrollStudents();
        break;
    case 'get_class_details':
        getClassDetails();
        break;
    case 'get_enrolled_students':
        getEnrolledStudents();
        break;
    case 'get_available_students':
        getAvailableStudents();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function createClass() {
    global $con, $currentAdmin, $currentRole;
    
    try {
        $grade = sanitize($_POST['grade']);
        $section = sanitize($_POST['section']);
        $description = sanitize($_POST['description'] ?? '');
        $teacher_id = intval($_POST['teacher_id'] ?? 0);
        
        // Validate required fields
        if (empty($grade) || empty($section) || empty($teacher_id)) {
            throw new Exception('Grade, Section, and Teacher are required');
        }
        
        // Check if section already exists for this grade
        $check_query = "SELECT section_id FROM section WHERE grade = ? AND section = ?";
        $check_stmt = $con->prepare($check_query);
        $check_stmt->bind_param("ss", $grade, $section);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            throw new Exception('Section already exists for this grade');
        }
        
        // Validate teacher (now required)
        $teacher_query = "SELECT user_id FROM users WHERE user_id = ? AND role = 'teacher' AND status = 'Approved'";
        $teacher_stmt = $con->prepare($teacher_query);
        $teacher_stmt->bind_param("i", $teacher_id);
        $teacher_stmt->execute();
        $teacher_result = $teacher_stmt->get_result();
        
        if ($teacher_result->num_rows == 0) {
            throw new Exception('Invalid teacher selected');
        }
        
        // Start transaction
        $con->begin_transaction();
        
        // Insert new section
        $insert_query = "INSERT INTO section (grade, section, description, teacher_id) VALUES (?, ?, ?, ?)";
        $insert_stmt = $con->prepare($insert_query);
        $insert_stmt->bind_param("sssi", $grade, $section, $description, $teacher_id);
        
        if (!$insert_stmt->execute()) {
            throw new Exception('Failed to create class: ' . $insert_stmt->error);
        }
        
        $section_id = $con->insert_id;

        // Log the action
        if (!isset($currentRole)) {
            $currentRole = $_SESSION['role'] ?? '';
        }
        logAction($currentAdmin, $currentRole, "Created new class: Grade $grade - $section");

        $con->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Class created successfully',
            'section_id' => $section_id
        ]);
        
    } catch (Exception $e) {
        $con->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function updateClass() {
    global $con, $currentAdmin, $currentRole;
    
    try {
        $section_id = intval($_POST['section_id']);
        $grade = sanitize($_POST['grade']);
        $section = sanitize($_POST['section']);
        $description = sanitize($_POST['description'] ?? '');
        
        // Validate required fields
        if (empty($grade) || empty($section)) {
            throw new Exception('Grade and Section are required');
        }
        
        // Check if section exists
        $check_query = "SELECT section_id FROM section WHERE section_id = ?";
        $check_stmt = $con->prepare($check_query);
        $check_stmt->bind_param("i", $section_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            throw new Exception('Class not found');
        }
        
        // Check if section name already exists for this grade (excluding current section)
        $name_check_query = "SELECT section_id FROM section WHERE grade = ? AND section = ? AND section_id != ?";
        $name_check_stmt = $con->prepare($name_check_query);
        $name_check_stmt->bind_param("ssi", $grade, $section, $section_id);
        $name_check_stmt->execute();
        $name_check_result = $name_check_stmt->get_result();
        
        if ($name_check_result->num_rows > 0) {
            throw new Exception('Section name already exists for this grade');
        }
        
        // Update section
        $update_query = "UPDATE section SET grade = ?, section = ?, description = ?, updated_at = NOW() WHERE section_id = ?";
        $update_stmt = $con->prepare($update_query);
        $update_stmt->bind_param("sssi", $grade, $section, $description, $section_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception('Failed to update class: ' . $update_stmt->error);
        }
        
        // Log the action
        logAction($currentAdmin, $currentRole, "Updated class: Grade $grade - $section");
        
        echo json_encode([
            'success' => true,
            'message' => 'Class updated successfully'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function deleteClass() {
    global $con, $currentAdmin, $currentRole;
    
    try {
        $section_id = intval($_POST['section_id']);
        
        // Check if section exists
        $check_query = "SELECT s.*, COUNT(e.enrollment_id) as student_count 
                       FROM section s 
                       LEFT JOIN enrollment e ON s.section_id = e.section_id AND e.status = 'Active'
                       WHERE s.section_id = ?";
        $check_stmt = $con->prepare($check_query);
        $check_stmt->bind_param("i", $section_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            throw new Exception('Class not found');
        }
        
        $section_data = $check_result->fetch_assoc();
        
        // Check if there are enrolled students
        if ($section_data['student_count'] > 0) {
            throw new Exception('Cannot delete class with enrolled students. Please unenroll all students first.');
        }
        
        // Start transaction
        $con->begin_transaction();
        
        // Delete from event_section table first (foreign key constraint)
        $delete_event_section = "DELETE FROM event_section WHERE section_id = ?";
        $delete_event_stmt = $con->prepare($delete_event_section);
        $delete_event_stmt->bind_param("i", $section_id);
        $delete_event_stmt->execute();
        
        // Delete from enrollment table
        $delete_enrollment = "DELETE FROM enrollment WHERE section_id = ?";
        $delete_enrollment_stmt = $con->prepare($delete_enrollment);
        $delete_enrollment_stmt->bind_param("i", $section_id);
        $delete_enrollment_stmt->execute();
        
        // Delete the section
        $delete_query = "DELETE FROM section WHERE section_id = ?";
        $delete_stmt = $con->prepare($delete_query);
        $delete_stmt->bind_param("i", $section_id);
        
        if (!$delete_stmt->execute()) {
            throw new Exception('Failed to delete class: ' . $delete_stmt->error);
        }
        
        // Log the action
        logAction($currentAdmin, $currentRole, "Deleted class: Grade {$section_data['grade']} - {$section_data['section']}");
        
        $con->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Class deleted successfully'
        ]);
        
    } catch (Exception $e) {
        $con->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function assignTeacher() {
    global $con, $currentAdmin, $currentRole;
    
    try {
        $section_id = intval($_POST['section_id']);
        $teacher_id = intval($_POST['teacher_id']);
        
        // Validate section exists
        $section_query = "SELECT section_id, grade, section FROM section WHERE section_id = ?";
        $section_stmt = $con->prepare($section_query);
        $section_stmt->bind_param("i", $section_id);
        $section_stmt->execute();
        $section_result = $section_stmt->get_result();
        
        if ($section_result->num_rows == 0) {
            throw new Exception('Class not found');
        }
        
        $section_data = $section_result->fetch_assoc();
        
        // Validate teacher
        $teacher_query = "SELECT user_id, CONCAT(firstname, ' ', lastname) as name FROM users WHERE user_id = ? AND role = 'teacher' AND status = 'Approved'";
        $teacher_stmt = $con->prepare($teacher_query);
        $teacher_stmt->bind_param("i", $teacher_id);
        $teacher_stmt->execute();
        $teacher_result = $teacher_stmt->get_result();
        
        if ($teacher_result->num_rows == 0) {
            throw new Exception('Invalid teacher selected');
        }
        
        $teacher_data = $teacher_result->fetch_assoc();
        
        // Check if teacher is already assigned to another class
        $existing_query = "SELECT section_id FROM section WHERE teacher_id = ? AND section_id != ?";
        $existing_stmt = $con->prepare($existing_query);
        $existing_stmt->bind_param("ii", $teacher_id, $section_id);
        $existing_stmt->execute();
        $existing_result = $existing_stmt->get_result();
        
        if ($existing_result->num_rows > 0) {
            throw new Exception('Teacher is already assigned to another class');
        }
        
        // Update section with teacher
        $update_query = "UPDATE section SET teacher_id = ?, updated_at = NOW() WHERE section_id = ?";
        $update_stmt = $con->prepare($update_query);
        $update_stmt->bind_param("ii", $teacher_id, $section_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception('Failed to assign teacher: ' . $update_stmt->error);
        }
        
        // Log the action
        logAction($currentAdmin, $currentRole, "Assigned teacher {$teacher_data['name']} to class Grade {$section_data['grade']} - {$section_data['section']}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Teacher assigned successfully',
            'teacher_name' => $teacher_data['name']
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function bulkAssignTeacher() {
    global $con, $currentAdmin, $currentRole;
    
    try {
        $section_ids = $_POST['section_ids'] ?? [];
        $teacher_id = intval($_POST['teacher_id']);
        
        if (empty($section_ids) || !is_array($section_ids)) {
            throw new Exception('No classes selected');
        }
        
        if ($teacher_id <= 0) {
            throw new Exception('Invalid teacher selected');
        }
        
        // Validate teacher
        $teacher_query = "SELECT user_id, CONCAT(firstname, ' ', lastname) as name FROM users WHERE user_id = ? AND role = 'teacher' AND status = 'Approved'";
        $teacher_stmt = $con->prepare($teacher_query);
        $teacher_stmt->bind_param("i", $teacher_id);
        $teacher_stmt->execute();
        $teacher_result = $teacher_stmt->get_result();
        
        if ($teacher_result->num_rows == 0) {
            throw new Exception('Invalid teacher selected');
        }
        
        $teacher_data = $teacher_result->fetch_assoc();
        
        // Start transaction
        $con->begin_transaction();
        
        $assigned_count = 0;
        $errors = [];
        
        foreach ($section_ids as $section_id) {
            $section_id = intval($section_id);
            
            try {
                // Check if section exists and is not already assigned
                $check_query = "SELECT section_id, grade, section FROM section WHERE section_id = ? AND teacher_id IS NULL";
                $check_stmt = $con->prepare($check_query);
                $check_stmt->bind_param("i", $section_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    $section_data = $check_result->fetch_assoc();
                    
                    // Update section with teacher
                    $update_query = "UPDATE section SET teacher_id = ?, updated_at = NOW() WHERE section_id = ?";
                    $update_stmt = $con->prepare($update_query);
                    $update_stmt->bind_param("ii", $teacher_id, $section_id);
                    
                    if ($update_stmt->execute()) {
                        $assigned_count++;
                    }
                }
            } catch (Exception $e) {
                $errors[] = "Section ID $section_id: " . $e->getMessage();
            }
        }
        
        if ($assigned_count > 0) {
            // Log the action
            logAction($currentAdmin, $currentRole, "Bulk assigned teacher {$teacher_data['name']} to $assigned_count classes");
            
            $con->commit();
            
            $message = "Successfully assigned teacher to $assigned_count classes";
            if (!empty($errors)) {
                $message .= ". Some assignments failed: " . implode(', ', $errors);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'assigned_count' => $assigned_count
            ]);
        } else {
            throw new Exception('No classes were assigned. All selected classes may already have teachers assigned.');
        }
        
    } catch (Exception $e) {
        $con->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function enrollStudent() {
    global $con, $currentAdmin, $currentRole;
    
    try {
        $section_id = intval($_POST['section_id']);
        $student_id = intval($_POST['student_id']);
        
        // Validate section exists
        $section_query = "SELECT section_id, grade, section FROM section WHERE section_id = ?";
        $section_stmt = $con->prepare($section_query);
        $section_stmt->bind_param("i", $section_id);
        $section_stmt->execute();
        $section_result = $section_stmt->get_result();
        
        if ($section_result->num_rows == 0) {
            throw new Exception('Class not found');
        }
        
        $section_data = $section_result->fetch_assoc();
        
        // Validate student exists and is approved
        $student_query = "SELECT user_id, CONCAT(firstname, ' ', lastname) as name FROM users WHERE user_id = ? AND role = 'student' AND status = 'Approved'";
        $student_stmt = $con->prepare($student_query);
        $student_stmt->bind_param("i", $student_id);
        $student_stmt->execute();
        $student_result = $student_stmt->get_result();
        
        if ($student_result->num_rows == 0) {
            throw new Exception('Invalid student selected');
        }
        
        $student_data = $student_result->fetch_assoc();
        
        // Check if student is already enrolled in this section
        $enrollment_check = "SELECT enrollment_id FROM enrollment WHERE student_id = ? AND section_id = ? AND status = 'Active'";
        $enrollment_check_stmt = $con->prepare($enrollment_check);
        $enrollment_check_stmt->bind_param("ii", $student_id, $section_id);
        $enrollment_check_stmt->execute();
        $enrollment_check_result = $enrollment_check_stmt->get_result();
        
        if ($enrollment_check_result->num_rows > 0) {
            throw new Exception('Student is already enrolled in this class');
        }
        
        // Check if student is enrolled in another section
        $other_enrollment = "SELECT s.grade, s.section FROM enrollment e 
                           JOIN section s ON e.section_id = s.section_id 
                           WHERE e.student_id = ? AND e.status = 'Active'";
        $other_enrollment_stmt = $con->prepare($other_enrollment);
        $other_enrollment_stmt->bind_param("i", $student_id);
        $other_enrollment_stmt->execute();
        $other_enrollment_result = $other_enrollment_stmt->get_result();
        
        if ($other_enrollment_result->num_rows > 0) {
            $other_section = $other_enrollment_result->fetch_assoc();
            throw new Exception("Student is already enrolled in Grade {$other_section['grade']} - {$other_section['section']}");
        }
        
        // Enroll student
        $enroll_query = "INSERT INTO enrollment (student_id, section_id, enrollment_date, status) VALUES (?, ?, CURDATE(), 'Active')";
        $enroll_stmt = $con->prepare($enroll_query);
        $enroll_stmt->bind_param("ii", $student_id, $section_id);
        
        if (!$enroll_stmt->execute()) {
            throw new Exception('Failed to enroll student: ' . $enroll_stmt->error);
        }
        
        // Log the action
        logAction($currentAdmin, $currentRole, "Enrolled student {$student_data['name']} in class Grade {$section_data['grade']} - {$section_data['section']}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Student enrolled successfully',
            'student_name' => $student_data['name']
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function unenrollStudent() {
    global $con, $currentAdmin, $currentRole;
    
    try {
        $section_id = intval($_POST['section_id']);
        $student_id = intval($_POST['student_id']);
        
        // Check if enrollment exists
        $enrollment_query = "SELECT e.*, u.firstname, u.lastname, s.grade, s.section 
                           FROM enrollment e 
                           JOIN users u ON e.student_id = u.user_id 
                           JOIN section s ON e.section_id = s.section_id 
                           WHERE e.student_id = ? AND e.section_id = ? AND e.status = 'Active'";
        $enrollment_stmt = $con->prepare($enrollment_query);
        $enrollment_stmt->bind_param("ii", $student_id, $section_id);
        $enrollment_stmt->execute();
        $enrollment_result = $enrollment_stmt->get_result();
        
        if ($enrollment_result->num_rows == 0) {
            throw new Exception('Enrollment not found');
        }
        
        $enrollment_data = $enrollment_result->fetch_assoc();
        
        // Update enrollment status to inactive
        $unenroll_query = "UPDATE enrollment SET status = 'Inactive', updated_at = NOW() WHERE student_id = ? AND section_id = ? AND status = 'Active'";
        $unenroll_stmt = $con->prepare($unenroll_query);
        $unenroll_stmt->bind_param("ii", $student_id, $section_id);
        
        if (!$unenroll_stmt->execute()) {
            throw new Exception('Failed to unenroll student: ' . $unenroll_stmt->error);
        }
        
        // Log the action
        $student_name = $enrollment_data['firstname'] . ' ' . $enrollment_data['lastname'];
        logAction($currentAdmin, $currentRole, "Unenrolled student $student_name from class Grade {$enrollment_data['grade']} - {$enrollment_data['section']}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Student unenrolled successfully',
            'student_name' => $student_name
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function bulkEnrollStudents() {
    global $con, $currentAdmin, $currentRole;
    
    try {
        $section_id = intval($_POST['section_id']);
        $student_ids = $_POST['student_ids'] ?? [];
        
        if (empty($student_ids) || !is_array($student_ids)) {
            throw new Exception('No students selected');
        }
        
        // Validate section exists
        $section_query = "SELECT section_id, grade, section FROM section WHERE section_id = ?";
        $section_stmt = $con->prepare($section_query);
        $section_stmt->bind_param("i", $section_id);
        $section_stmt->execute();
        $section_result = $section_stmt->get_result();
        
        if ($section_result->num_rows == 0) {
            throw new Exception('Class not found');
        }
        
        $section_data = $section_result->fetch_assoc();
        
        // Start transaction
        $con->begin_transaction();
        
        $enrolled_count = 0;
        $errors = [];
        
        foreach ($student_ids as $student_id) {
            $student_id = intval($student_id);
            
            try {
                // Validate student
                $student_query = "SELECT user_id, CONCAT(firstname, ' ', lastname) as name FROM users WHERE user_id = ? AND role = 'student' AND status = 'Approved'";
                $student_stmt = $con->prepare($student_query);
                $student_stmt->bind_param("i", $student_id);
                $student_stmt->execute();
                $student_result = $student_stmt->get_result();
                
                if ($student_result->num_rows == 0) {
                    $errors[] = "Student ID $student_id: Invalid student";
                    continue;
                }
                
                $student_data = $student_result->fetch_assoc();
                
                // Check if already enrolled
                $enrollment_check = "SELECT enrollment_id FROM enrollment WHERE student_id = ? AND section_id = ? AND status = 'Active'";
                $enrollment_check_stmt = $con->prepare($enrollment_check);
                $enrollment_check_stmt->bind_param("ii", $student_id, $section_id);
                $enrollment_check_stmt->execute();
                $enrollment_check_result = $enrollment_check_stmt->get_result();
                
                if ($enrollment_check_result->num_rows > 0) {
                    $errors[] = "{$student_data['name']}: Already enrolled";
                    continue;
                }
                
                // Check if enrolled in another section
                $other_enrollment = "SELECT s.grade, s.section FROM enrollment e 
                                   JOIN section s ON e.section_id = s.section_id 
                                   WHERE e.student_id = ? AND e.status = 'Active'";
                $other_enrollment_stmt = $con->prepare($other_enrollment);
                $other_enrollment_stmt->bind_param("i", $student_id);
                $other_enrollment_stmt->execute();
                $other_enrollment_result = $other_enrollment_stmt->get_result();
                
                if ($other_enrollment_result->num_rows > 0) {
                    $other_section = $other_enrollment_result->fetch_assoc();
                    $errors[] = "{$student_data['name']}: Already enrolled in Grade {$other_section['grade']} - {$other_section['section']}";
                    continue;
                }
                
                // Enroll student
                $enroll_query = "INSERT INTO enrollment (student_id, section_id, enrollment_date, status) VALUES (?, ?, CURDATE(), 'Active')";
                $enroll_stmt = $con->prepare($enroll_query);
                $enroll_stmt->bind_param("ii", $student_id, $section_id);
                
                if ($enroll_stmt->execute()) {
                    $enrolled_count++;
                } else {
                    $errors[] = "{$student_data['name']}: Enrollment failed";
                }
                
            } catch (Exception $e) {
                $errors[] = "Student ID $student_id: " . $e->getMessage();
            }
        }
        
        if ($enrolled_count > 0) {
            // Log the action
            logAction($currentAdmin, $currentRole, "Bulk enrolled $enrolled_count students in class Grade {$section_data['grade']} - {$section_data['section']}");
            
            $con->commit();
            
            $message = "Successfully enrolled $enrolled_count students";
            if (!empty($errors)) {
                $message .= ". Some enrollments failed: " . implode(', ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " and " . (count($errors) - 5) . " more";
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'enrolled_count' => $enrolled_count,
                'errors' => $errors
            ]);
        } else {
            throw new Exception('No students were enrolled. ' . implode(', ', $errors));
        }
        
    } catch (Exception $e) {
        $con->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function bulkUnenrollStudents() {
    global $con, $currentAdmin, $currentRole;
    
    try {
        $section_id = intval($_POST['section_id']);
        $student_ids = $_POST['student_ids'] ?? [];
        
        if (empty($student_ids) || !is_array($student_ids)) {
            throw new Exception('No students selected');
        }
        
        // Validate section exists
        $section_query = "SELECT section_id, grade, section FROM section WHERE section_id = ?";
        $section_stmt = $con->prepare($section_query);
        $section_stmt->bind_param("i", $section_id);
        $section_stmt->execute();
        $section_result = $section_stmt->get_result();
        
        if ($section_result->num_rows == 0) {
            throw new Exception('Class not found');
        }
        
        $section_data = $section_result->fetch_assoc();
        
        // Start transaction
        $con->begin_transaction();
        
        $unenrolled_count = 0;
        $errors = [];
        
        foreach ($student_ids as $student_id) {
            $student_id = intval($student_id);
            
            try {
                // Check if enrollment exists
                $enrollment_query = "SELECT e.*, u.firstname, u.lastname 
                                   FROM enrollment e 
                                   JOIN users u ON e.student_id = u.user_id 
                                   WHERE e.student_id = ? AND e.section_id = ? AND e.status = 'Active'";
                $enrollment_stmt = $con->prepare($enrollment_query);
                $enrollment_stmt->bind_param("ii", $student_id, $section_id);
                $enrollment_stmt->execute();
                $enrollment_result = $enrollment_stmt->get_result();
                
                if ($enrollment_result->num_rows == 0) {
                    $errors[] = "Student ID $student_id: Not enrolled in this class";
                    continue;
                }
                
                $enrollment_data = $enrollment_result->fetch_assoc();
                
                // Unenroll student
                $unenroll_query = "UPDATE enrollment SET status = 'Inactive', updated_at = NOW() WHERE student_id = ? AND section_id = ? AND status = 'Active'";
                $unenroll_stmt = $con->prepare($unenroll_query);
                $unenroll_stmt->bind_param("ii", $student_id, $section_id);
                
                if ($unenroll_stmt->execute()) {
                    $unenrolled_count++;
                } else {
                    $errors[] = "{$enrollment_data['firstname']} {$enrollment_data['lastname']}: Unenrollment failed";
                }
                
            } catch (Exception $e) {
                $errors[] = "Student ID $student_id: " . $e->getMessage();
            }
        }
        
        if ($unenrolled_count > 0) {
            // Log the action
            logAction($currentAdmin, $currentRole, "Bulk unenrolled $unenrolled_count students from class Grade {$section_data['grade']} - {$section_data['section']}");
            
            $con->commit();
            
            $message = "Successfully unenrolled $unenrolled_count students";
            if (!empty($errors)) {
                $message .= ". Some unenrollments failed: " . implode(', ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " and " . (count($errors) - 5) . " more";
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'unenrolled_count' => $unenrolled_count,
                'errors' => $errors
            ]);
        } else {
            throw new Exception('No students were unenrolled. ' . implode(', ', $errors));
        }
        
    } catch (Exception $e) {
        $con->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function getClassDetails() {
    global $con;
    
    try {
        $section_id = intval($_GET['section_id']);
        
        // Get class details with teacher and student count
        $query = "SELECT s.*, 
                  CONCAT(u.firstname, ' ', u.lastname) as teacher_name,
                  u.email as teacher_email,
                  COUNT(e.enrollment_id) as student_count
                  FROM section s 
                  LEFT JOIN users u ON s.teacher_id = u.user_id 
                  LEFT JOIN enrollment e ON s.section_id = e.section_id AND e.status = 'Active'
                  WHERE s.section_id = ?
                  GROUP BY s.section_id";
        
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            throw new Exception('Class not found');
        }
        
        $class_data = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'data' => $class_data
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function getEnrolledStudents() {
    global $con;
    
    try {
        $section_id = intval($_GET['section_id']);
        
        // Get enrolled students
        $query = "SELECT e.*, u.user_id, u.firstname, u.lastname, u.email, u.gender
                  FROM enrollment e 
                  JOIN users u ON e.student_id = u.user_id 
                  WHERE e.section_id = ? AND e.status = 'Active'
                  ORDER BY u.firstname, u.lastname";
        
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $students
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function getAvailableStudents() {
    global $con;
    
    try {
        $section_id = intval($_GET['section_id']);
        $search = sanitize($_GET['search'] ?? '');
        
        // Build query for available students (not enrolled in any class)
        $query = "SELECT u.user_id, u.firstname, u.lastname, u.email, u.gender, u.status
                  FROM users u 
                  WHERE u.role = 'student' AND u.status = 'Approved'
                  AND u.user_id NOT IN (
                      SELECT student_id FROM enrollment WHERE status = 'Active'
                  )";
        
        $params = [];
        $types = '';
        
        if (!empty($search)) {
            $query .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ?)";
            $search_param = "%$search%";
            $params = [$search_param, $search_param, $search_param];
            $types = 'sss';
        }
        
        $query .= " ORDER BY u.firstname, u.lastname LIMIT 50";
        
        $stmt = $con->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $students
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>
