<?php
/**
 * Email Utilities
 * Centralized email templates and sending functionality
 */

// Prevent direct access
if (!defined('IN_APP')) {
    header('HTTP/1.0 403 Forbidden');
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Get local date and time in Philippines timezone
 */
function getLocalDateTime() {
    // Set timezone to Philippines
    date_default_timezone_set('Asia/Manila');
    return date('F j, Y \a\t g:i A');
}

/**
 * Base email template with common styles and structure
 */
function getBaseEmailTemplate($title, $content, $footerText = null) {
    $defaultFooter = $footerText ?: '&copy; 2025 EAMS - San Agustin National High School';
    
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($title) . '</title>
        <style>
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                margin: 0;
                padding: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
            }
            .email-wrapper {
                padding: 20px;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .email-container {
                max-width: 600px;
                width: 100%;
                background-color: #ffffff;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                position: relative;
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 40px 20px;
                text-align: center;
                color: white;
                position: relative;
                overflow: hidden;
            }
            .header::before {
                content: "";
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
                animation: float 6s ease-in-out infinite;
            }
            @keyframes float {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                50% { transform: translateY(-20px) rotate(180deg); }
            }
            .header h1 {
                margin: 0;
                font-size: 32px;
                font-weight: 700;
                position: relative;
                z-index: 1;
            }
            .header p {
                margin: 10px 0 0 0;
                font-size: 18px;
                opacity: 0.9;
                position: relative;
                z-index: 1;
            }
            .logo {
                font-size: 28px;
                font-weight: bold;
                margin-bottom: 15px;
                position: relative;
                z-index: 1;
            }
            .content {
                padding: 50px 40px;
                text-align: center;
            }
            .greeting {
                font-size: 24px;
                color: #333;
                margin-bottom: 25px;
                font-weight: 600;
            }
            .instructions {
                color: #555;
                line-height: 1.8;
                margin-bottom: 35px;
                font-size: 16px;
                text-align: left;
            }
            .footer {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                padding: 30px 20px;
                text-align: center;
                color: #666;
                font-size: 14px;
                border-top: 1px solid #dee2e6;
            }
            .highlight {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                font-weight: 700;
            }
            .security-note {
                background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
                border: 2px solid #ffc107;
                border-radius: 15px;
                padding: 20px;
                margin: 30px 0;
                color: #856404;
                position: relative;
                overflow: hidden;
            }
            .security-note::before {
                content: "🔒";
                position: absolute;
                top: 15px;
                right: 20px;
                font-size: 24px;
                opacity: 0.3;
            }
            .security-note strong {
                color: #6c5ce7;
                font-size: 16px;
            }
            .steps {
                background-color: #f8f9fa;
                border-radius: 15px;
                padding: 25px;
                margin: 30px 0;
                text-align: left;
            }
            .steps h3 {
                color: #495057;
                margin-bottom: 15px;
                font-size: 18px;
            }
            .steps ol {
                color: #6c757d;
                line-height: 1.8;
                padding-left: 20px;
            }
            .steps li {
                margin-bottom: 8px;
            }
            .contact-info {
                background-color: #e8f4fd;
                border: 1px solid #bee5eb;
                border-radius: 8px;
                padding: 15px;
                margin: 20px 0;
                color: #0c5460;
            }
            @media (max-width: 600px) {
                .email-wrapper { padding: 10px; }
                .content { padding: 30px 20px; }
                .header h1 { font-size: 28px; }
            }
        </style>
    </head>
    <body>
        <div class="email-wrapper">
            <div class="email-container">
                <div class="header">
                    <div class="logo">EAMS</div>
                    <h1>' . htmlspecialchars($title) . '</h1>
                    <p>San Agustin National High School</p>
                </div>
                
                <div class="content">
                    ' . $content . '
                </div>
                
                <div class="footer">
                    <p><strong>' . $defaultFooter . '</strong></p>
                    <p>Event Attendance Management System</p>
                    <p style="margin-top: 15px; font-size: 12px; color: #999;">
                        This is an automated message. Please do not reply to this email.
                    </p>
                </div>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Create verification email template
 */
function createVerificationEmail($name, $code) {
    $content = '
        <div class="greeting">Hello <span class="highlight">' . htmlspecialchars($name) . '</span>!</div>
        
        <p class="instructions">
            Welcome to EAMS! We\'re excited to have you join our school community. 
            To complete your registration and secure your account, please use the 
            verification code below:
        </p>
        
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 40px; margin: 40px 0; color: white; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
            <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); animation: pulse 4s ease-in-out infinite;"></div>
            <div style="font-size: 56px; font-weight: 900; letter-spacing: 12px; margin: 25px 0; text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3); position: relative; z-index: 1; font-family: \'Courier New\', monospace;">' . $code . '</div>
            <p style="margin: 0; font-size: 18px; font-weight: 600; position: relative; z-index: 1;">Your Verification Code</p>
        </div>
        
        <div class="steps">
            <h3>📋 How to verify your account:</h3>
            <ol>
                <li>Copy the verification code above</li>
                <li>Return to the EAMS verification page</li>
                <li>Paste or type the code in the verification field</li>
                <li>Click "Verify Account" to complete registration</li>
            </ol>
        </div>
        
        <div class="security-note">
            <strong>🔐 Security Notice:</strong><br>
            This verification code is unique to your account and will expire after use. 
            Never share this code with anyone. EAMS staff will never ask for your 
            verification code via phone, email, or any other method.
        </div>
        
        <p class="instructions">
            If you didn\'t create an EAMS account or if you have any questions, 
            please contact our school administration immediately. 
            <strong>Do not reply to this email.</strong>
        </p>';
    
    return getBaseEmailTemplate('Email Verification', $content);
}

/**
 * Create user removal email template
 */
function createUserRemovalEmail($name, $email, $role) {
    $content = '
        <div class="greeting">Dear <span class="highlight">' . htmlspecialchars($name) . '</span>,</div>
        
        <div style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); border-radius: 20px; padding: 40px; margin: 40px 0; color: white; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(231, 76, 60, 0.3);">
            <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
            <h2 style="margin: 0; font-size: 24px;">Your Account Has Been Removed</h2>
        </div>
        
        <div class="instructions">
            <p>We are writing to inform you that your EAMS account has been permanently removed from our system.</p>
            
            <p><strong>Account Details:</strong></p>
            <ul>
                <li><strong>Name:</strong> ' . htmlspecialchars($name) . '</li>
                <li><strong>Email:</strong> ' . htmlspecialchars($email) . '</li>
                <li><strong>Role:</strong> ' . htmlspecialchars($role) . '</li>
                <li><strong>Removal Date:</strong> ' . getLocalDateTime() . '</li>
            </ul>
            
            <p><strong>What this means:</strong></p>
            <ul>
                <li>You will no longer be able to access the EAMS system</li>
                <li>All your account data has been permanently deleted</li>
                <li>You will not receive any further notifications from EAMS</li>
            </ul>
        </div>
        
        <div class="contact-info">
            <strong>Need Assistance?</strong><br>
            If you believe this removal was made in error or if you have any questions, 
            please contact the school administration immediately.
        </div>
        
        <p class="instructions">
            Thank you for your understanding. We appreciate your time with EAMS.
        </p>';
    
    return getBaseEmailTemplate('Account Removal Notice', $content);
}

/**
 * Create password reset email template
 */
function createPasswordResetEmail($name, $code) {
    $content = '
        <div class="greeting">Hello <span class="highlight">' . htmlspecialchars($name) . '</span>!</div>
        
        <p class="instructions">
            We received a request to reset your password for your EAMS account. 
            If you made this request, please use the reset code below:
        </p>
        
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 40px; margin: 40px 0; color: white; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
            <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); animation: pulse 4s ease-in-out infinite;"></div>
            <div style="font-size: 48px; font-weight: 900; letter-spacing: 8px; margin: 25px 0; text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3); position: relative; z-index: 1; font-family: \'Courier New\', monospace;">' . $code . '</div>
            <p style="margin: 0; font-size: 18px; font-weight: 600; position: relative; z-index: 1;">Your Password Reset Code</p>
        </div>
        
        <div class="steps">
            <h3>📋 How to reset your password:</h3>
            <ol>
                <li>Copy the reset code above</li>
                <li>Return to the EAMS password reset page</li>
                <li>Enter the code and your new password</li>
                <li>Click "Reset Password" to complete the process</li>
            </ol>
        </div>
        
        <div class="security-note">
            <strong>🔐 Security Notice:</strong><br>
            This reset code will expire after use. Never share this code with anyone. 
            If you didn\'t request a password reset, please ignore this email and 
            contact our school administration immediately.
        </div>
        
        <p class="instructions">
            If you didn\'t request a password reset or if you have any questions, 
            please contact our school administration immediately. 
            <strong>Do not reply to this email.</strong>
        </p>';
    
    return getBaseEmailTemplate('Password Reset', $content);
}

/**
 * Create user approval email template
 */
function createUserApprovalEmail($name, $role) {
    $content = '
        <div class="greeting">Congratulations <span class="highlight">' . htmlspecialchars($name) . '</span>!</div>
        
        <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 20px; padding: 40px; margin: 40px 0; color: white; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);">
            <div style="font-size: 48px; margin-bottom: 20px;">✅</div>
            <h2 style="margin: 0; font-size: 24px;">Your Account Has Been Approved!</h2>
        </div>
        
        <div class="instructions">
            <p>Great news! Your EAMS account has been reviewed and approved by our administration.</p>
            
            <p><strong>Account Details:</strong></p>
            <ul>
                <li><strong>Name:</strong> ' . htmlspecialchars($name) . '</li>
                <li><strong>Role:</strong> ' . htmlspecialchars($role) . '</li>
                <li><strong>Approval Date:</strong> ' . getLocalDateTime() . '</li>
            </ul>
            
            <p><strong>What\'s next:</strong></p>
            <ul>
                <li>You can now log in to the EAMS system</li>
                <li>Access all features available to your role</li>
                <li>Start managing events and attendance</li>
            </ul>
        </div>
        
        <div class="contact-info">
            <strong>Ready to get started?</strong><br>
            Visit <a href="http://localhost/eam_system_v0.1.1/login.php" style="color: #0c5460; text-decoration: underline;">EAMS Login Page</a> to access your account.
        </div>
        
        <p class="instructions">
            Welcome to the EAMS community! If you have any questions or need assistance, 
            please don\'t hesitate to contact our school administration.
        </p>';
    
    return getBaseEmailTemplate('Account Approved', $content);
}

/**
 * Create user deactivation email template
 */
function createUserDeactivationEmail($name, $email, $role) {
    $content = '
        <div class="greeting">Dear <span class="highlight">' . htmlspecialchars($name) . '</span>,</div>
        
        <div style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); border-radius: 20px; padding: 40px; margin: 40px 0; color: white; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(255, 193, 7, 0.3);">
            <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
            <h2 style="margin: 0; font-size: 24px;">Your Account Has Been Deactivated</h2>
        </div>
        
        <div class="instructions">
            <p>We are writing to inform you that your EAMS account has been deactivated.</p>
            
            <p><strong>Account Details:</strong></p>
            <ul>
                <li><strong>Name:</strong> ' . htmlspecialchars($name) . '</li>
                <li><strong>Email:</strong> ' . htmlspecialchars($email) . '</li>
                <li><strong>Role:</strong> ' . htmlspecialchars($role) . '</li>
                <li><strong>Deactivation Date:</strong> ' . getLocalDateTime() . '</li>
            </ul>
            
            <p><strong>What this means:</strong></p>
            <ul>
                <li>You will no longer be able to access the EAMS system</li>
                <li>All your data and records remain intact in our system</li>
                <li>Your account can be reactivated by an administrator if needed</li>
            </ul>
        </div>
        
        <div class="contact-info">
            <strong>Need assistance?</strong><br>
            If you believe this deactivation was made in error, please contact the school administration immediately.
        </div>
        
        <p class="instructions">
            Thank you for your understanding. If you have any questions or concerns, 
            please don\'t hesitate to reach out to our school administration.
        </p>';
    
    return getBaseEmailTemplate('Account Deactivated', $content);
}

/**
 * Create user reactivation email template
 */
function createUserReactivationEmail($name, $email, $role) {
    $content = '
        <div class="greeting">Welcome back <span class="highlight">' . htmlspecialchars($name) . '</span>!</div>
        
        <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 20px; padding: 40px; margin: 40px 0; color: white; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);">
            <div style="font-size: 48px; margin-bottom: 20px;">🎉</div>
            <h2 style="margin: 0; font-size: 24px;">Your Account Has Been Reactivated!</h2>
        </div>
        
        <div class="instructions">
            <p>Great news! Your EAMS account has been reactivated and you can now access the system again.</p>
            
            <p><strong>Account Details:</strong></p>
            <ul>
                <li><strong>Name:</strong> ' . htmlspecialchars($name) . '</li>
                <li><strong>Email:</strong> ' . htmlspecialchars($email) . '</li>
                <li><strong>Role:</strong> ' . htmlspecialchars($role) . '</li>
                <li><strong>Reactivation Date:</strong> ' . getLocalDateTime() . '</li>
            </ul>
            
            <p><strong>What you can do now:</strong></p>
            <ul>
                <li>Log in to the EAMS system</li>
                <li>Access all features available to your role</li>
                <li>Continue managing events and attendance</li>
            </ul>
        </div>
        
        <div class="contact-info">
            <strong>Ready to get back started?</strong><br>
            Visit <a href="http://localhost/eam_system_v0.1.1/login.php" style="color: #0c5460; text-decoration: underline;">EAMS Login Page</a> to access your account.
        </div>
        
        <p class="instructions">
            Welcome back to the EAMS community! If you have any questions or need assistance, 
            please don\'t hesitate to contact our school administration.
        </p>';
    
    return getBaseEmailTemplate('Account Reactivated', $content);
}

/**
 * Send email using PHPMailer
 */
function sendEmail($to, $toName, $subject, $htmlBody, $altBody = null) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'nherrera@sanagustinnhs.net';
        $mail->Password = 'egzv ldoc ijfj fsqe';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('nherrera@sanagustinnhs.net', 'EAMS - San Agustin National High School');
        $mail->addAddress($to, $toName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $altBody;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send verification email
 */
function sendVerificationEmail($email, $name, $code) {
    $subject = "Email Verification Code - EAMS";
    $htmlBody = createVerificationEmail($name, $code);
    $altBody = "Hello $name,\n\nYour EAMS verification code is: $code\n\nEnter this code in the verification page to activate your account.\n\nIf you didn't request this code, please ignore this email.\n\nEAMS - San Agustin National High School";
    
    return sendEmail($email, $name, $subject, $htmlBody, $altBody);
}

/**
 * Send user removal email
 */
function sendUserRemovalEmail($email, $name, $role) {
    $subject = "Account Removal Notice - EAMS";
    $htmlBody = createUserRemovalEmail($name, $email, $role);
    $altBody = "Dear $name,\n\nYour EAMS account has been permanently removed from our system.\n\nAccount Details:\n- Name: $name\n- Email: $email\n- Role: $role\n- Removal Date: " . getLocalDateTime() . "\n\nYou will no longer be able to access the EAMS system. If you believe this removal was made in error, please contact the school administration immediately.\n\nThank you for your understanding.\n\nEAMS - San Agustin National High School";
    
    return sendEmail($email, $name, $subject, $htmlBody, $altBody);
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($email, $name, $code) {
    $subject = "Password Reset Code - EAMS";
    $htmlBody = createPasswordResetEmail($name, $code);
    $altBody = "Hello $name,\n\nYour EAMS password reset code is: $code\n\nEnter this code in the password reset page to set a new password.\n\nIf you didn't request a password reset, please ignore this email.\n\nEAMS - San Agustin National High School";
    
    return sendEmail($email, $name, $subject, $htmlBody, $altBody);
}

/**
 * Create user rejection email template
 */
function createUserRejectionEmail($name, $email, $role) {
    $content = '
        <div class="greeting">Dear <span class="highlight">' . htmlspecialchars($name) . '</span>,</div>
        
        <div style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); border-radius: 20px; padding: 40px; margin: 40px 0; color: white; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(231, 76, 60, 0.3);">
            <div style="font-size: 48px; margin-bottom: 20px;">❌</div>
            <h2 style="margin: 0; font-size: 24px;">Account Application Rejected</h2>
        </div>
        
        <div class="instructions">
            <p>We regret to inform you that your account application for EAMS has been rejected.</p>
            
            <p><strong>Application Details:</strong></p>
            <ul>
                <li><strong>Name:</strong> ' . htmlspecialchars($name) . '</li>
                <li><strong>Email:</strong> ' . htmlspecialchars($email) . '</li>
                <li><strong>Requested Role:</strong> ' . htmlspecialchars($role) . '</li>
                <li><strong>Rejection Date:</strong> ' . getLocalDateTime() . '</li>
            </ul>
            
            <p><strong>What this means:</strong></p>
            <ul>
                <li>Your account application has been declined</li>
                <li>You will not be able to access the EAMS system</li>
                <li>Your application data has been removed from our system</li>
            </ul>
        </div>
        
        <div class="contact-info">
            <strong>Need More Information?</strong><br>
            If you have questions about this decision or would like to discuss your application, 
            please contact the school administration directly.
        </div>
        
        <p class="instructions">
            Thank you for your interest in EAMS. We appreciate your understanding.
        </p>';
    
    return getBaseEmailTemplate('Account Application Rejected', $content);
}

/**
 * Send user approval email
 */
function sendUserApprovalEmail($email, $name, $role) {
    $subject = "Account Approved - EAMS";
    $htmlBody = createUserApprovalEmail($name, $role);
    $altBody = "Congratulations $name,\n\nYour EAMS account has been approved!\n\nAccount Details:\n- Name: $name\n- Role: $role\n- Approval Date: " . getLocalDateTime() . "\n\nYou can now log in to the EAMS system at: http://localhost/eam_system_v0.1.1/login.php\n\nWelcome to the EAMS community!\n\nEAMS - San Agustin National High School";
    
    return sendEmail($email, $name, $subject, $htmlBody, $altBody);
}

function sendUserDeactivationEmail($email, $name, $role) {
    $subject = "Account Deactivated - EAMS";
    $htmlBody = createUserDeactivationEmail($name, $email, $role);
    $altBody = "Dear $name,\n\nYour EAMS account has been deactivated.\n\nAccount Details:\n- Name: $name\n- Email: $email\n- Role: $role\n- Deactivation Date: " . getLocalDateTime() . "\n\nYou will no longer be able to access the EAMS system. If you believe this deactivation was made in error, please contact the school administration immediately.\n\nThank you for your understanding.\n\nEAMS - San Agustin National High School";
    
    return sendEmail($email, $name, $subject, $htmlBody, $altBody);
}

function sendUserReactivationEmail($email, $name, $role) {
    $subject = "Account Reactivated - EAMS";
    $htmlBody = createUserReactivationEmail($name, $email, $role);
    $altBody = "Dear $name,\n\nYour EAMS account has been reactivated!\n\nAccount Details:\n- Name: $name\n- Email: $email\n- Role: $role\n- Reactivation Date: " . getLocalDateTime() . "\n\nYou can now log in to the EAMS system at: http://localhost/eam_system_v0.1.1/login.php\n\nWelcome back to the EAMS community!\n\nEAMS - San Agustin National High School";
    
    return sendEmail($email, $name, $subject, $htmlBody, $altBody);
}

/**
 * Send user rejection email
 */
function sendUserRejectionEmail($email, $name, $role) {
    $subject = "Account Application Rejected - EAMS";
    $htmlBody = createUserRejectionEmail($name, $email, $role);
    $altBody = "Dear $name,\n\nWe regret to inform you that your account application for EAMS has been rejected.\n\nApplication Details:\n- Name: $name\n- Email: $email\n- Requested Role: $role\n- Rejection Date: " . getLocalDateTime() . "\n\nIf you have questions about this decision, please contact the school administration directly.\n\nThank you for your interest in EAMS.\n\nEAMS - San Agustin National High School";
    
    return sendEmail($email, $name, $subject, $htmlBody, $altBody);
}
?>
