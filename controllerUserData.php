<?php 
session_start();

// Define IN_APP constant for utility access
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Only require authentication if we're not on authentication pages
$auth_pages = ['login.php', 'signup-user.php', 'forgot-password.php', 'reset-code.php', 'new-password.php', 'user-otp.php', 'password-changed.php'];
$current_page = basename($_SERVER['PHP_SELF']);

if (!in_array($current_page, $auth_pages)) {
    require_once 'utils/auth.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require "utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

// Ensure database connection is available
if (!isset($con) || !$con) {
    die("Database connection failed");
}
$email = "";
$fname = "";
$gender = "";
$lname = "";
$role = "";

$errors = array();

//helper function for sending mail
// sendMail function is now handled by the email utility

// Email functions are now handled by the email utility

// createPasswordResetEmail function is now handled by the email utility


//if user signup button
if(isset($_POST['signup'])){
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);
    
    // Role-specific fields
    $lrn = isset($_POST['lrn']) ? mysqli_real_escape_string($con, $_POST['lrn']) : '';
    $mis_id = isset($_POST['mis_id']) ? mysqli_real_escape_string($con, $_POST['mis_id']) : '';
    $course = isset($_POST['course']) ? mysqli_real_escape_string($con, $_POST['course']) : '';
    if($password !== $cpassword){
        $errors['password'] = "Confirm password not matched!";
    }
    if(empty($role)){
        $errors['role'] = "Please select a role!";
    }
    
    // Validate role-specific fields
    if($role === 'student'){
        if(empty($lrn)){
            $errors['lrn'] = "LRN is required for students!";
        } elseif(strlen($lrn) !== 12){
            $errors['lrn'] = "LRN must be exactly 12 characters!";
        }
        if(empty($mis_id)){
            $errors['mis_id'] = "MIS ID is required for students!";
        } elseif(strlen($mis_id) !== 6){
            $errors['mis_id'] = "MIS ID must be exactly 6 characters!";
        }
    } elseif($role === 'teacher'){
        if(empty($course)){
            $errors['course'] = "Course is required for teachers!";
        }
    }
    $email_check = "SELECT * FROM users WHERE email = ?";
    $stmt = $con->prepare($email_check);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if(mysqli_num_rows($res) > 0){
        $errors['email'] = "Email that you have entered already exist!";
    }
    if(count($errors) === 0){
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $code = rand(111111, 999999);
        $status = "notverified";
            
        // Start transaction
        $con->begin_transaction();
        
        try {
            // Insert into users table
            $insert_data = "INSERT INTO users (email, password, code, role, verification_status, firstname, lastname, gender)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $con->prepare($insert_data);
            $stmt->bind_param("ssisssss", $email, $encpass, $code, $role, $status, $fname, $lname, $gender);
            $data_check = $stmt->execute();
            
            if($data_check){
                $user_id = $con->insert_id;
                
                // Insert into role-specific table
                if($role === 'student'){
                    $insert_student = "INSERT INTO students (student_id, lrn, mis_id) VALUES (?, ?, ?)";
                    $stmt2 = $con->prepare($insert_student);
                    $stmt2->bind_param("iss", $user_id, $lrn, $mis_id);
                    $stmt2->execute();
                } elseif($role === 'teacher'){
                    $insert_teacher = "INSERT INTO teacher (teacher_id, course) VALUES (?, ?)";
                    $stmt2 = $con->prepare($insert_teacher);
                    $stmt2->bind_param("is", $user_id, $course);
                    $stmt2->execute();
                }
                
                // Commit transaction
                $con->commit();
                
                if(sendVerificationEmail($email, $fname, $code)){
                    $info = "We've sent a verification code to your email - $email";
                    $_SESSION['info'] = $info;
                    $_SESSION['email'] = $email;
                    $_SESSION['password'] = $password;
                    header('location: user-otp.php');
                    exit();
                }else{
                    $errors['otp-error'] = "Failed while sending code!";
                }
            }else{
                $con->rollback();
                $errors['db-error'] = "Failed while inserting data into database!";
            }
        } catch (Exception $e) {
            $con->rollback();
            $errors['db-error'] = "Failed while inserting data into database!";
        }
    }

}
// if user clicks verification code submit button
if (isset($_POST['check'])) {
    $_SESSION['info'] = "";
    $otp_code = mysqli_real_escape_string($con, $_POST['otp']);
    $check_code = "SELECT * FROM users WHERE code = ?";
    $stmt = $con->prepare($check_code);
    $stmt->bind_param("i", $otp_code);
    $stmt->execute();
    $code_res = $stmt->get_result();

    if (mysqli_num_rows($code_res) > 0) {
        $fetch_data = mysqli_fetch_assoc($code_res);
        $fetch_code = $fetch_data['code'];
        $role = $fetch_data['role'];
        $email = $fetch_data['email'];
        $code = 0;
        $status = 'verified';

        $update_otp = "UPDATE users SET code = ?, verification_status = ? WHERE code = ?";
        $stmt = $con->prepare($update_otp);
        $stmt->bind_param("isi", $code, $status, $fetch_code);
        $update_res = $stmt->execute();

        if ($update_res) {
            $_SESSION['name'] = $fetch_data['firstname'] . " " . $fetch_data['lastname']; // safer
            $_SESSION['email'] = $email;

            // check account status
            if ($fetch_data['status'] !== 'Approved') {
                header('location: pages/home.php');
                exit();
            } else {
                // if approved, check role
                switch ($role) {
                    case 'admin':
                        header('location: pages/admin.php');
                        break;
                    case 'teacher':
                        header('location: pages/teacher.php');
                        break;
                    case 'student':
                        header('location: pages/student.php');
                        break;
                    default:
                        header('location: pages/home.php'); // fallback
                }
                exit();
            }
        } else {
            $errors['otp-error'] = "Failed while updating code!";
        }
    } else {
        $errors['otp-error'] = "You've entered incorrect code!";
    }
}

// if user click login button
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    $check_email = "SELECT * FROM users WHERE email = ?";
    $stmt = $con->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if (mysqli_num_rows($res) > 0) {
        $fetch = mysqli_fetch_assoc($res);
        $fetch_pass = $fetch['password'];

        if (password_verify($password, $fetch_pass)) {
            // Check if account is deactivated
            if (isset($fetch['account_status']) && $fetch['account_status'] === 'deactivated') {
                $errors[] = "Your account has been deactivated. Please contact an administrator.";
            } else {
                $_SESSION['email'] = $email;
                $_SESSION['user_id'] = $fetch['user_id'];
                $_SESSION['role'] = $fetch['role'];

                $status = $fetch['verification_status'];
                if ($status == 'verified') {
                    // check account status
                    if ($fetch['status'] !== 'Approved') {
                        header('location: pages/home.php'); 
                        exit();
                    } else {
                    // if approved, check role
                    switch ($fetch['role']) {
                        case 'admin':
                            header('location: pages/admin.php');
                            break;
                        case 'teacher':
                            header('location: pages/teacher.php');
                            break;
                        case 'student':
                            header('location: pages/student.php');
                            break;
                        case 'sslg':
                            header('location: pages/sslg.php');
                            break;
                        default:
                            header('location: pages/home.php'); // fallback
                    }
                    exit();
                }
            } else {
                $info = "It looks like you haven't verified your email yet - $email";
                $_SESSION['info'] = $info;
                header('location: user-otp.php');
                    // generate a new code
                    $code = rand(111111, 999999);
                    $update_code = "UPDATE users SET code = ? WHERE email = ?";
                    $stmt = $con->prepare($update_code);
                    $stmt->bind_param("is", $code, $email);
                    $stmt->execute();

                    // send mail
                    if(sendVerificationEmail($email, $fname, $code)){
                        $info = "A verification code has been sent to your email - $email";
                        $_SESSION['info'] = $info;
                        header('location: user-otp.php');
                        exit();
                    }
                }
            }
        } else {
            $errors['email'] = "Incorrect email or password!";
        }
    } else {
        $errors['email'] = "It looks like you're not yet a member! Click on the bottom link to signup.";
    }
}


    //if user click continue button in forgot password form
    if(isset($_POST['check-email'])){
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $check_email = "SELECT * FROM users WHERE email = ?";
        $stmt = $con->prepare($check_email);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $run_sql = $stmt->get_result();
        if(mysqli_num_rows($run_sql) > 0){
            $code = rand(999999, 111111);
            $insert_code = "UPDATE users SET code = ? WHERE email = ?";
            $stmt = $con->prepare($insert_code);
            $stmt->bind_param("is", $code, $email);
            $run_query = $stmt->execute();
            if($run_query){
                if(sendPasswordResetEmail($email, $fname, $code)){
                    $info = "We've sent a passwrod reset otp to your email - $email";
                    $_SESSION['info'] = $info;
                    $_SESSION['email'] = $email;
                    header('location: reset-code.php');
                    exit();
                }else{
                    $errors['otp-error'] = "Failed while sending code!";
                }
            }else{
                $errors['db-error'] = "Something went wrong!";
            }
        }else{
            $errors['email'] = "This email address does not exist!";
        }
    }

    //if user click check reset otp button
    if(isset($_POST['check-reset-otp'])){
        $_SESSION['info'] = "";
        $otp_code = mysqli_real_escape_string($con, $_POST['otp']);
        $check_code = "SELECT * FROM users WHERE code = ?";
        $stmt = $con->prepare($check_code);
        $stmt->bind_param("i", $otp_code);
        $stmt->execute();
        $code_res = $stmt->get_result();
        if(mysqli_num_rows($code_res) > 0){
            $fetch_data = mysqli_fetch_assoc($code_res);
            $email = $fetch_data['email'];
            $_SESSION['email'] = $email;
            $info = "Please create a new password that you don't use on any other site.";
            $_SESSION['info'] = $info;
            header('location: new-password.php');
            exit();
        }else{
            $errors['otp-error'] = "You've entered incorrect code!";
        }
    }

    //if user click change password button
    if(isset($_POST['change-password'])){
        $_SESSION['info'] = "";
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);
        if($password !== $cpassword){
            $errors['password'] = "Confirm password not matched!";
        }else{
            $code = 0;
            $email = $_SESSION['email']; //getting this email using session
            $encpass = password_hash($password, PASSWORD_BCRYPT);
            $update_pass = "UPDATE users SET code = ?, password = ? WHERE email = ?";
            $stmt = $con->prepare($update_pass);
            $stmt->bind_param("iss", $code, $encpass, $email);
            $run_query = $stmt->execute();
            if($run_query){
                $info = "Your password changed. Now you can login with your new password.";
                $_SESSION['info'] = $info;
                header('Location: password-changed.php');
            }else{
                $errors['db-error'] = "Failed to change your password!";
            }
        }
    }
    
   //if login now button click
    if(isset($_POST['login-now'])){
        header('Location: login.php');
    }
?>