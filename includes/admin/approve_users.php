<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../utils/index.php";

// Email functions are now handled by the email utility

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'approve':
            handleSingleApproval();
            break;
        case 'approve_multiple':
            handleMultipleApproval();
            break;
        default:
            echo "Invalid action";
            break;
    }
}

function handleSingleApproval() {
    global $con;
    
    $user_id = $_POST['id'] ?? '';
    $user_name = $_POST['user_name'] ?? '';
    $user_email = $_POST['user_email'] ?? '';
    $user_role = $_POST['user_role'] ?? '';
    
    if (empty($user_id)) {
        echo "User ID is required";
        return;
    }
    
    // Update user status to approved
    $update_query = "UPDATE users SET status = 'Approved' WHERE user_id = ?";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        // Send approval email
        $name_parts = explode(' ', $user_name);
        $firstname = $name_parts[0] ?? '';
        $lastname = implode(' ', array_slice($name_parts, 1)) ?? '';
        
        $email_sent = sendUserApprovalEmail($user_email, $user_name, $user_role);
        
        if ($email_sent) {
            echo "User approved successfully and approval email sent!";
        } else {
            echo "User approved successfully but failed to send approval email.";
        }
    } else {
        echo "Failed to approve user: " . $con->error;
    }
}

function handleMultipleApproval() {
    global $con;
    
    $users = $_POST['users'] ?? [];
    
    if (empty($users) || !is_array($users)) {
        echo "No users selected for approval";
        return;
    }
    
    $success_count = 0;
    $email_success_count = 0;
    $user_ids = array_column($users, 'id');
    
    // Update all selected users to approved status
    $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
    $update_query = "UPDATE users SET status = 'Approved' WHERE user_id IN ($placeholders)";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param(str_repeat('i', count($user_ids)), ...$user_ids);
    
    if ($stmt->execute()) {
        $success_count = $stmt->affected_rows;
        
        // Send approval emails to all approved users
        foreach ($users as $user) {
            $name_parts = explode(' ', $user['name']);
            $firstname = $name_parts[0] ?? '';
            $lastname = implode(' ', array_slice($name_parts, 1)) ?? '';
            
            if (sendUserApprovalEmail($user['email'], $user['name'], $user['role'])) {
                $email_success_count++;
            }
        }
        
        if ($email_success_count == $success_count) {
            echo "$success_count users approved successfully and approval emails sent!";
        } else {
            echo "$success_count users approved successfully. $email_success_count approval emails sent.";
        }
    } else {
        echo "Failed to approve users: " . $con->error;
    }
}
?>

