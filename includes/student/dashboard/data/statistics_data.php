<?php
/**
 * Student Dashboard Statistics Data
 * Centralized data fetching for student dashboard statistics
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../../../utils/index.php";

// Get database connection
$con = getDatabaseConnection();

/**
 * Get comprehensive statistics for student dashboard
 */
function getStudentStatistics($con, $student_id) {
    $stats = [
        // Personal Statistics
        'total_events_attended' => 0,
        'total_events_registered' => 0,
        'attendance_rate' => 0,
        'upcoming_events' => 0,
        'pending_excuses' => 0,
        
        // Attendance Statistics
        'present_count' => 0,
        'late_count' => 0,
        'absent_count' => 0,
        'excused_count' => 0,
        
        // Recent Activity
        'recent_attendance' => 0,
        'recent_registrations' => 0,
        'last_attendance_date' => null,
        
        // Academic Information
        'current_section' => null,
        'section_grade' => null,
        'section_name' => null,
        'teacher_name' => null
    ];
    
    try {
        // Get student's current section information
        $section_query = "SELECT s.section_id, s.grade, s.section, s.description,
                                 CONCAT(u.firstname, ' ', u.lastname) as teacher_name
                          FROM enrollment e
                          JOIN section s ON e.section_id = s.section_id
                          LEFT JOIN users u ON s.teacher_id = u.user_id
                          WHERE e.student_id = ? AND e.status = 'Active'
                          ORDER BY e.enrollment_date DESC
                          LIMIT 1";
        
        $stmt = $con->prepare($section_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($section_row = $result->fetch_assoc()) {
            $stats['current_section'] = $section_row['section_id'];
            $stats['section_grade'] = $section_row['grade'];
            $stats['section_name'] = $section_row['section'];
            $stats['teacher_name'] = $section_row['teacher_name'];
        }
        
        // Get attendance statistics
        $attendance_query = "SELECT 
                                COUNT(*) as total_attendance,
                                SUM(CASE WHEN remark = 'Present' THEN 1 ELSE 0 END) as present_count,
                                SUM(CASE WHEN remark = 'Late' THEN 1 ELSE 0 END) as late_count,
                                SUM(CASE WHEN remark = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                                SUM(CASE WHEN remark = 'Excused' THEN 1 ELSE 0 END) as excused_count,
                                MAX(check_in_time) as last_attendance_date
                            FROM attendance 
                            WHERE student_id = ?";
        
        $stmt = $con->prepare($attendance_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($attendance_row = $result->fetch_assoc()) {
            $stats['total_events_attended'] = $attendance_row['total_attendance'];
            $stats['present_count'] = $attendance_row['present_count'];
            $stats['late_count'] = $attendance_row['late_count'];
            $stats['absent_count'] = $attendance_row['absent_count'];
            $stats['excused_count'] = $attendance_row['excused_count'];
            $stats['last_attendance_date'] = $attendance_row['last_attendance_date'];
        }
        
        // Get upcoming events for student's section (both Open events and section-specific events)
        if ($stats['current_section']) {
            $upcoming_query = "SELECT COUNT(DISTINCT e.event_id) as upcoming_count
                               FROM events e
                               LEFT JOIN event_section es ON e.event_id = es.event_id
                               WHERE e.approval_status = 'Approved'
                               AND e.event_status = 'Upcoming'
                               AND e.event_date >= CURDATE()
                               AND (e.event_type = 'Open' OR (e.event_type = 'Exclusive' AND es.section_id = ?))";
            
            $stmt = $con->prepare($upcoming_query);
            $stmt->bind_param("i", $stats['current_section']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($upcoming_row = $result->fetch_assoc()) {
                $stats['upcoming_events'] = $upcoming_row['upcoming_count'];
            }
        } else {
            // If student is not enrolled in any section, count only Open events
            $upcoming_query = "SELECT COUNT(*) as upcoming_count
                               FROM events e
                               WHERE e.approval_status = 'Approved'
                               AND e.event_status = 'Upcoming'
                               AND e.event_date >= CURDATE()
                               AND e.event_type = 'Open'";
            
            $stmt = $con->prepare($upcoming_query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($upcoming_row = $result->fetch_assoc()) {
                $stats['upcoming_events'] = $upcoming_row['upcoming_count'];
            }
        }
        
        // Get pending excuse letters
        $excuse_query = "SELECT COUNT(*) as pending_excuses
                         FROM excuse_letter 
                         WHERE student_id = ? AND status = 'Pending'";
        
        $stmt = $con->prepare($excuse_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($excuse_row = $result->fetch_assoc()) {
            $stats['pending_excuses'] = $excuse_row['pending_excuses'];
        }
        
        // Calculate attendance rate
        if ($stats['total_events_attended'] > 0) {
            $stats['attendance_rate'] = round(($stats['present_count'] + $stats['late_count']) / $stats['total_events_attended'] * 100, 1);
        }
        
        // Get recent activity (last 7 days)
        $recent_date = date('Y-m-d', strtotime('-7 days'));
        
        $recent_attendance_query = "SELECT COUNT(*) as recent_count
                                    FROM attendance 
                                    WHERE student_id = ? AND DATE(check_in_time) >= ?";
        
        $stmt = $con->prepare($recent_attendance_query);
        $stmt->bind_param("is", $student_id, $recent_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($recent_row = $result->fetch_assoc()) {
            $stats['recent_attendance'] = $recent_row['recent_count'];
        }
        
    } catch (Exception $e) {
        error_log("Error fetching student statistics: " . $e->getMessage());
    }
    
    return $stats;
}

/**
 * Get upcoming events for student
 */
function getUpcomingEvents($con, $student_id, $limit = 5) {
    $events = [];
    
    try {
        // Get student's current section
        $section_query = "SELECT section_id FROM enrollment 
                          WHERE student_id = ? AND status = 'Active' 
                          ORDER BY enrollment_date DESC LIMIT 1";
        
        $stmt = $con->prepare($section_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($section_row = $result->fetch_assoc()) {
            $section_id = $section_row['section_id'];
            
            // Get upcoming events for this section (both Open events and section-specific events)
            $events_query = "SELECT DISTINCT e.*, 
                                   CONCAT(u.firstname, ' ', u.lastname) as creator_name
                            FROM events e
                            LEFT JOIN event_section es ON e.event_id = es.event_id
                            LEFT JOIN users u ON e.created_by = u.user_id
                            WHERE e.approval_status = 'Approved'
                            AND e.event_status = 'Upcoming'
                            AND e.event_date >= CURDATE()
                            AND (e.event_type = 'Open' OR (e.event_type = 'Exclusive' AND es.section_id = ?))
                            ORDER BY e.event_date ASC, e.start_time ASC
                            LIMIT ?";
            
            $stmt = $con->prepare($events_query);
            $stmt->bind_param("ii", $section_id, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
        } else {
            // If student is not enrolled in any section, show only Open events
            $events_query = "SELECT e.*, 
                                   CONCAT(u.firstname, ' ', u.lastname) as creator_name
                            FROM events e
                            LEFT JOIN users u ON e.created_by = u.user_id
                            WHERE e.approval_status = 'Approved'
                            AND e.event_status = 'Upcoming'
                            AND e.event_date >= CURDATE()
                            AND e.event_type = 'Open'
                            ORDER BY e.event_date ASC, e.start_time ASC
                            LIMIT ?";
            
            $stmt = $con->prepare($events_query);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
        }
        
    } catch (Exception $e) {
        error_log("Error fetching upcoming events: " . $e->getMessage());
    }
    
    return $events;
}

/**
 * Get recent attendance history for student
 */
function getRecentAttendance($con, $student_id, $limit = 10) {
    $attendance = [];
    
    try {
        $attendance_query = "SELECT a.*, e.title as event_title, e.event_date, e.start_time, e.end_time, e.location
                            FROM attendance a
                            JOIN events e ON a.event_id = e.event_id
                            WHERE a.student_id = ?
                            ORDER BY a.check_in_time DESC
                            LIMIT ?";
        
        $stmt = $con->prepare($attendance_query);
        $stmt->bind_param("ii", $student_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $attendance[] = $row;
        }
        
    } catch (Exception $e) {
        error_log("Error fetching recent attendance: " . $e->getMessage());
    }
    
    return $attendance;
}

/**
 * Get student personal information
 */
function getStudentPersonalInfo($con, $student_id) {
    $info = [];
    
    try {
        $info_query = "SELECT u.*, s.lrn, s.mis_id
                       FROM users u
                       LEFT JOIN students s ON u.user_id = s.student_id
                       WHERE u.user_id = ?";
        
        $stmt = $con->prepare($info_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $info = $row;
        }
        
    } catch (Exception $e) {
        error_log("Error fetching student personal info: " . $e->getMessage());
    }
    
    return $info;
}

// Get all data for dashboard
$student_id = $_SESSION['user_id'] ?? 0;
$student_stats = getStudentStatistics($con, $student_id);
$upcoming_events = getUpcomingEvents($con, $student_id, 5);
$recent_attendance = getRecentAttendance($con, $student_id, 10);
$personal_info = getStudentPersonalInfo($con, $student_id);

// Debug statistics
error_log("Student Statistics Debug:");
error_log("Student ID: " . $student_id);
error_log("Total Events Attended: " . $student_stats['total_events_attended']);
error_log("Attendance Rate: " . $student_stats['attendance_rate']);
error_log("Upcoming Events: " . $student_stats['upcoming_events']);
error_log("Current Section: " . $student_stats['current_section']);
?>
