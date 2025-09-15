<?php
// Define IN_APP constant for security
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Include required files
require_once "../controllerUserData.php";
require_once "../utils/auth.php";
require_once "../utils/index.php";

// Constants
const REDIRECT_VERIFIED = '../reset-code.php';
const REDIRECT_UNVERIFIED = '../user-otp.php';
const REDIRECT_LOGIN = '../login.php';

// Initialize variables
$user = null;
$error = null;

try {
    // Validate session
    if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
        header('Location: ' . REDIRECT_LOGIN);
        exit;
    }

    $email = $_SESSION['email'];
    
    // Get user data with proper error handling
    $user = getUserByEmail($email);
    
    if (!$user) {
        throw new Exception("User not found in database");
    }

    // Check verification status and redirect accordingly
    handleUserVerificationStatus($user);

} catch (Exception $e) {
    error_log("Home page error: " . $e->getMessage());
    $error = "An error occurred. Please try again.";
}

/**
 * Get user data by email
 */
function getUserByEmail($email) {
    global $con;
    
    $sql = "SELECT user_id, firstname, lastname, email, verification_status, code, status, role 
            FROM users WHERE email = ?";
    $stmt = $con->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Database preparation failed: " . $con->error);
    }
    
    $stmt->bind_param("s", $email);
    
    if (!$stmt->execute()) {
        throw new Exception("Database execution failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user;
}

/**
 * Handle user verification status and redirects
 */
function handleUserVerificationStatus($user) {
    $status = $user['verification_status'];
    $code = $user['code'];
    
    if ($status === "verified") {
        if ($code != 0) {
            header('Location: ' . REDIRECT_VERIFIED);
            exit;
        }
    } else {
        header('Location: ' . REDIRECT_UNVERIFIED);
        exit;
    }
}

// sanitizeOutput() function is available from utils/security.php

/**
 * Get user display name
 */
function getUserDisplayName($user) {
    if (!$user) return 'User';
    return trim($user['firstname'] . ' ' . $user['lastname']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SANHS EAMS - <?php echo sanitizeOutput(getUserDisplayName($user)); ?> | Home</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="https://mis.sanagustinnhs.net/assets/images/sanhs_logo.png">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6665ee;
            --text-color: #333;
            --error-color: #dc3545;
            --success-color: #28a745;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .navbar {
            padding-left: 100px !important;
            padding-right: 100px !important;
            background: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: #fff !important;
            font-size: 30px !important;
            font-weight: 500;
        }

        .btn-light {
            color: var(--primary-color);
            font-weight: 500;
            border: none;
            padding: 8px 16px;
        }

        .btn-light:hover {
            background-color: #e9ecef;
            color: var(--primary-color);
        }

        .welcome-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            text-align: center;
            padding: 0 20px;
        }

        .welcome-title {
            font-size: 50px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 30px;
        }

        .status-message {
            font-size: 20px;
            font-weight: 500;
            color: var(--text-color);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .error-message {
            color: var(--error-color);
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px auto;
            max-width: 600px;
        }

        @media (max-width: 768px) {
            .navbar {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
            
            .navbar-brand {
                font-size: 24px !important;
            }
            
            .welcome-title {
                font-size: 36px;
            }
            
            .status-message {
                font-size: 18px;
            }
        }
        
        /* Mobile Responsiveness */
        @media screen and (max-width: 768px) {
            .navbar {
                padding: 10px 15px;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .navbar-brand {
                font-size: 1.1rem;
                margin-bottom: 10px;
            }
            
            .welcome-container {
                padding: 20px 15px;
                margin: 20px 15px;
            }
            
            .welcome-title {
                font-size: 1.8rem;
                margin-bottom: 15px;
            }
            
            .status-message {
                font-size: 16px;
                line-height: 1.5;
            }
            
            .error-message {
                padding: 15px;
                margin: 15px;
                font-size: 14px;
            }
        }
        
        @media screen and (max-width: 480px) {
            .navbar {
                padding: 8px 10px;
            }
            
            .navbar-brand {
                font-size: 1rem;
            }
            
            .welcome-container {
                padding: 15px 10px;
                margin: 15px 10px;
            }
            
            .welcome-title {
                font-size: 1.5rem;
            }
            
            .status-message {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <a class="navbar-brand" href="#">Events Attendance Management System</a>
        <button type="button" class="btn btn-light" data-toggle="modal" data-target="#logoutModal">
            Logout
        </button>
    </nav>

    <!-- Main Content -->
    <div class="welcome-container">
        <?php if ($error): ?>
            <div class="error-message">
                <strong>Error:</strong> <?php echo sanitizeOutput($error); ?>
            </div>
        <?php else: ?>
            <h1 class="welcome-title">
                Welcome <?php echo sanitizeOutput(getUserDisplayName($user)); ?>
            </h1>
            
            <p class="status-message">
                🚫 You are on this page because you have not been approved to use the system yet.
                You will be notified once an administrator approves your application.
            </p>
            
            <?php if ($user && $user['status'] === 'Pending'): ?>
                <div class="alert alert-info mt-4" style="max-width: 600px; margin: 30px auto 0;">
                    <strong>Application Status:</strong> Pending Review<br>
                    <strong>Role:</strong> <?php echo sanitizeOutput(ucfirst($user['role'])); ?><br>
                    <strong>Email:</strong> <?php echo sanitizeOutput($user['email']); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Logout Confirmation Modal -->
    <?php include "../includes/confirm-logout.php"; ?>
    
    <!-- Bootstrap JS + jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript -->
    <script src="../assets/js/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
