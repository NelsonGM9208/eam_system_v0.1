<?php
/**
 * AJAX endpoint to get student information
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . "/../../../utils/index.php";
$con = getDatabaseConnection();

try {
    $student_id = $_SESSION['user_id'];
    
    // Get student information with section details
    $student_query = "SELECT u.firstname, u.lastname, u.email, u.gender,
                             s.lrn, s.mis_id,
                             sec.grade, sec.section
                      FROM users u
                      LEFT JOIN students s ON u.user_id = s.student_id
                      LEFT JOIN enrollment e ON u.user_id = e.student_id AND e.status = 'Active'
                      LEFT JOIN section sec ON e.section_id = sec.section_id
                      WHERE u.user_id = ?";
    
    $stmt = $con->prepare($student_query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    
    if (!$student) {
        throw new Exception('Student information not found');
    }
    
    // Format section info
    $section_info = '';
    if ($student['grade'] && $student['section']) {
        $section_info = 'Grade ' . $student['grade'] . ' - ' . $student['section'];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'student' => [
            'firstname' => $student['firstname'],
            'lastname' => $student['lastname'],
            'email' => $student['email'],
            'gender' => $student['gender'],
            'lrn' => $student['lrn'],
            'mis_id' => $student['mis_id'],
            'section' => $section_info
        ]
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error getting student info: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
