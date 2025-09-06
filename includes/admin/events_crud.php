<?php
require_once __DIR__ . "/../../config/database.php";

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
            return 0;
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

// Helper function to get current user's role
function getCurrentUserRole()
{
    return $_SESSION['role'] ?? 'admin';
}

// Handle Add Event
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['event_description']);
    $event_date = sanitize($_POST['event_date']);
    $start_time = sanitize($_POST['start_time']);
    $end_time = sanitize($_POST['end_time']);
    $location = sanitize($_POST['location']);
    $event_type = sanitize($_POST['event_type']);
    $event_status = sanitize($_POST['event_status']);
    $abs_penalty = intval($_POST['abs_penalty'] ?? 0);
    
    // Get current admin user and role
    $createdBy = getCurrentAdmin();
    $creatorRole = getCurrentUserRole(); // Get actual role from session
    
    // Set approval status - if admin creates event, it's automatically approved
    $approval_status = ($creatorRole === 'admin') ? 'Approved' : 'Pending';
    
    // Debug logging
    error_log("Adding event - createdBy: " . $createdBy . ", creatorRole: " . $creatorRole . ", approval_status: " . $approval_status);
    
    // Start transaction
    $con->begin_transaction();
    
    try {
        // Use prepared statement for security
        $stmt = $con->prepare("INSERT INTO events (title, event_description, event_date, start_time, end_time, location, event_type, event_status, abs_penalty, approval_status, created_by, creator_role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Database error: " . $con->error);
        }
        
        $stmt->bind_param("ssssssssisss", $title, $description, $event_date, $start_time, $end_time, $location, $event_type, $event_status, $abs_penalty, $approval_status, $createdBy, $creatorRole);
        
        if (!$stmt->execute()) {
            throw new Exception("Error: " . $stmt->error);
        }
        
        $event_id = $con->insert_id;
        
        // Handle selected classes for exclusive events
        if ($event_type === 'Exclusive' && isset($_POST['selected_classes'])) {
            $selected_classes = json_decode($_POST['selected_classes'], true);
            
            if (is_array($selected_classes) && !empty($selected_classes)) {
                $class_stmt = $con->prepare("INSERT INTO event_classes (event_id, section_id) VALUES (?, ?)");
                if (!$class_stmt) {
                    throw new Exception("Database error: " . $con->error);
                }
                
                foreach ($selected_classes as $section_id) {
                    $class_stmt->bind_param("ii", $event_id, $section_id);
                    if (!$class_stmt->execute()) {
                        throw new Exception("Error adding class: " . $class_stmt->error);
                    }
                }
                $class_stmt->close();
            }
        }
        
        // Commit transaction
        $con->commit();
        echo "Event added successfully. Created by: " . $creatorRole . " (ID: " . $createdBy . "), Status: " . $approval_status;
        
    } catch (Exception $e) {
        // Rollback transaction
        $con->rollback();
        echo "Error: " . $e->getMessage();
    }
    
    exit;
}

// Handle Update Event
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = intval($_POST['id']);
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['event_description']);
    $event_date = sanitize($_POST['event_date']);
    $start_time = sanitize($_POST['start_time']);
    $end_time = sanitize($_POST['end_time']);
    $location = sanitize($_POST['location']);
    $event_type = sanitize($_POST['event_type']);
    $event_status = sanitize($_POST['event_status']);
    $abs_penalty = intval($_POST['abs_penalty'] ?? 0);
    
    // Get current admin user and role
    $updatedBy = getCurrentAdmin();
    $creatorRole = getCurrentUserRole();
    
    // Set approval status - if admin updates event, it's automatically approved
    $approval_status = ($creatorRole === 'admin') ? 'Approved' : 'Pending';
    
    // Use prepared statement for security
    $stmt = $con->prepare("UPDATE events SET title=?, event_description=?, event_date=?, start_time=?, end_time=?, location=?, event_type=?, event_status=?, abs_penalty=?, approval_status=?, updated_by=?, updated_at=NOW() WHERE event_id=?");
    if (!$stmt) {
        echo "Database error: " . $con->error;
        exit;
    }
    
    $stmt->bind_param("ssssssssissi", $title, $description, $event_date, $start_time, $end_time, $location, $event_type, $event_status, $abs_penalty, $approval_status, $updatedBy, $id);
    
    if ($stmt->execute()) {
        // Handle exclusive event class selection
        if ($event_type === 'Exclusive' && isset($_POST['selected_classes']) && is_array($_POST['selected_classes'])) {
            $selected_classes = array_map('intval', $_POST['selected_classes']);
            
            // Start transaction for class selection
            $con->begin_transaction();
            
            try {
                // Remove existing class associations
                $delete_stmt = $con->prepare("DELETE FROM event_section WHERE event_id = ?");
                if (!$delete_stmt) {
                    throw new Exception("Database error: " . $con->error);
                }
                $delete_stmt->bind_param("i", $id);
                $delete_stmt->execute();
                $delete_stmt->close();
                
                // Add new class associations
                if (!empty($selected_classes)) {
                    $class_stmt = $con->prepare("INSERT INTO event_section (event_id, section_id) VALUES (?, ?)");
                    if (!$class_stmt) {
                        throw new Exception("Database error: " . $con->error);
                    }
                    
                    foreach ($selected_classes as $section_id) {
                        $class_stmt->bind_param("ii", $id, $section_id);
                        if (!$class_stmt->execute()) {
                            throw new Exception("Error adding class: " . $class_stmt->error);
                        }
                    }
                    $class_stmt->close();
                }
                
                $con->commit();
                echo "Event updated successfully with class selections.";
            } catch (Exception $e) {
                $con->rollback();
                echo "Event updated but error with class selection: " . $e->getMessage();
            }
        } else {
            echo "Event updated successfully.";
        }
    } else {
        echo "Error: " . $stmt->error;
    }
    exit;
}

// Handle Delete Event
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Start transaction for multiple table deletes
    $con->begin_transaction();
    
    try {
        // Delete from event_section table if exists
        $deleteEventSectionStmt = $con->prepare("DELETE FROM event_section WHERE event_id = ?");
        if ($deleteEventSectionStmt) {
            $deleteEventSectionStmt->bind_param("i", $id);
            $deleteEventSectionStmt->execute();
            $deleteEventSectionStmt->close();
        }
        
        // Delete from attendance table if exists
        $deleteAttendanceStmt = $con->prepare("DELETE FROM attendance WHERE event_id = ?");
        if ($deleteAttendanceStmt) {
            $deleteAttendanceStmt->bind_param("i", $id);
            $deleteAttendanceStmt->execute();
            $deleteAttendanceStmt->close();
        }
        
        // Finally delete from events table
        $stmt = $con->prepare("DELETE FROM events WHERE event_id=?");
        if (!$stmt) {
            throw new Exception("Database error: " . $con->error);
        }
        
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $con->commit();
            echo "Event deleted successfully.";
        } else {
            throw new Exception("Error deleting event: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $con->rollback();
        echo "Error: " . $e->getMessage();
    }
    exit;
}

// Handle Read (for View modal)
if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $con->prepare("SELECT e.*, CONCAT(u.firstname, ' ', u.lastname) as creator_name, u.role as creator_role FROM events e LEFT JOIN users u ON e.created_by = u.user_id WHERE e.event_id = ? LIMIT 1");
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
    if ($event = mysqli_fetch_assoc($result)) {
        echo "<p><strong>Title:</strong> " . htmlspecialchars($event['title']) . "</p>";
        echo "<p><strong>Description:</strong> " . htmlspecialchars($event['event_description']) . "</p>";
        echo "<p><strong>Date:</strong> " . htmlspecialchars($event['event_date']) . "</p>";
        echo "<p><strong>Time:</strong> " . htmlspecialchars($event['start_time']) . " - " . htmlspecialchars($event['end_time']) . "</p>";
        echo "<p><strong>Location:</strong> " . htmlspecialchars($event['location']) . "</p>";
        echo "<p><strong>Type:</strong> " . htmlspecialchars($event['event_type']) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($event['event_status']) . "</p>";
        echo "<p><strong>Created By:</strong> " . htmlspecialchars($event['creator_name']) . " (" . htmlspecialchars($event['creator_role']) . ")</p>";
    } else {
        echo "<p>Event not found.</p>";
    }
    exit;
}
?>
