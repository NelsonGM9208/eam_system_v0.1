<?php 
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require "config/database.php";
$email = "";
$fname = "";
$gender = "";
$lname = "";

$errors = array();

//helper function for sending mail
function sendMail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nherrera@sanagustinnhs.net';      // your Gmail address
        $mail->Password   = 'egzv ldoc ijfj fsqe';        // Gmail App Password (not your login password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('nherrera@sanagustinnhs.net', 'EAMS | San Agustin National High School');
        $mail->addAddress($to);

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}


//if user signup button
if(isset($_POST['signup'])){
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);
    if($password !== $cpassword){
        $errors['password'] = "Confirm password not matched!";
    }
    $email_check = "SELECT * FROM users WHERE email = '$email'";
    $res = mysqli_query($con, $email_check);
    if(mysqli_num_rows($res) > 0){
        $errors['email'] = "Email that you have entered already exist!";
    }
    if(count($errors) === 0){
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $code = rand(111111, 999999);
        $status = "notverified";
        $insert_data = "INSERT INTO users (email, password, code, role, verification_status, firstname, lastname, gender)
                        values('$email', '$encpass', '$code', '', '$status', '$fname', '$lname', '$gender')";
        $data_check = mysqli_query($con, $insert_data);
        if($data_check){
            $subject = "Email Verification Code";
            $message = "Your verification code is $code";
            if(sendMail($email, $subject, $message)){
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
            $errors['db-error'] = "Failed while inserting data into database!";
        }
    }

}
// if user clicks verification code submit button
if (isset($_POST['check'])) {
    $_SESSION['info'] = "";
    $otp_code = mysqli_real_escape_string($con, $_POST['otp']);
    $check_code = "SELECT * FROM users WHERE code = $otp_code";
    $code_res = mysqli_query($con, $check_code);

    if (mysqli_num_rows($code_res) > 0) {
        $fetch_data = mysqli_fetch_assoc($code_res);
        $fetch_code = $fetch_data['code'];
        $role = $fetch_data['role'];
        $email = $fetch_data['email'];
        $code = 0;
        $status = 'verified';

        $update_otp = "UPDATE users SET code = $code, verification_status = '$status' WHERE code = $fetch_code";
        $update_res = mysqli_query($con, $update_otp);

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
                        header('location: teacher_dashboard.php');
                        break;
                    case 'student':
                        header('location: student_dashboard.php');
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

    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $res = mysqli_query($con, $check_email);

    if (mysqli_num_rows($res) > 0) {
        $fetch = mysqli_fetch_assoc($res);
        $fetch_pass = $fetch['password'];

        if (password_verify($password, $fetch_pass)) {
            $_SESSION['email'] = $email;

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
                    $update_code = "UPDATE users SET code = $code WHERE email = '$email'";
                    mysqli_query($con, $update_code);

                    // send mail
                    $subject = "Email Verification Code";
                    $message = "Your verification code is $code";
                    
                    if(sendMail($email, $subject, $message)){
                        $info = "A verification code has been sent to your email - $email";
                        $_SESSION['info'] = $info;
                        header('location: user-otp.php');
                        exit();
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
        $check_email = "SELECT * FROM users WHERE email='$email'";
        $run_sql = mysqli_query($con, $check_email);
        if(mysqli_num_rows($run_sql) > 0){
            $code = rand(999999, 111111);
            $insert_code = "UPDATE users SET code = $code WHERE email = '$email'";
            $run_query =  mysqli_query($con, $insert_code);
            if($run_query){
                $subject = "Password Reset Code";
                $message = "Your password reset code is $code";
                if(sendMail($email, $subject, $message)){
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
        $check_code = "SELECT * FROM users WHERE code = $otp_code";
        $code_res = mysqli_query($con, $check_code);
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
            $update_pass = "UPDATE users SET code = $code, password = '$encpass' WHERE email = '$email'";
            $run_query = mysqli_query($con, $update_pass);
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