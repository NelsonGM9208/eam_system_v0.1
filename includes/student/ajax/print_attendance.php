<?php
/**
 * AJAX endpoint to print attendance record for a student
 */

// Disable error display to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to prevent any accidental output
ob_start();

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
    $attendance_id = intval($_GET['attendance_id'] ?? 0);
    $student_id = $_SESSION['user_id'];
    
    if ($attendance_id <= 0) {
        throw new Exception('Invalid attendance ID');
    }
    
    // Get detailed attendance information
    $attendance_query = "SELECT a.*, e.title as event_title, e.event_description, e.event_date, 
                                e.start_time, e.end_time, e.location, e.event_type, e.event_status,
                                u.firstname, u.lastname, u.email
                         FROM attendance a
                         JOIN events e ON a.event_id = e.event_id
                         JOIN users u ON a.student_id = u.user_id
                         WHERE a.attendance_id = ? AND a.student_id = ?";
    
    $stmt = $con->prepare($attendance_query);
    $stmt->bind_param("ii", $attendance_id, $student_id);
    $stmt->execute();
    $attendance = $stmt->get_result()->fetch_assoc();
    
    if (!$attendance) {
        throw new Exception('Attendance record not found or access denied');
    }
    
    // Set headers for print view
    header('Content-Type: text/html; charset=utf-8');
    
    // Print-friendly HTML
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SANHS EAMS - Attendance Record - <?php echo htmlspecialchars($attendance['event_title']); ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #333;
            }
            .header {
                text-align: center;
                border-bottom: 2px solid #007bff;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            .header h1 {
                color: #007bff;
                margin: 0;
            }
            .header p {
                margin: 5px 0;
                color: #666;
            }
            .info-section {
                margin-bottom: 25px;
            }
            .info-section h3 {
                color: #007bff;
                border-bottom: 1px solid #ddd;
                padding-bottom: 5px;
                margin-bottom: 15px;
            }
            .info-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
            .info-item {
                margin-bottom: 10px;
            }
            .info-label {
                font-weight: bold;
                color: #555;
            }
            .info-value {
                color: #333;
            }
            .status-badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 4px;
                font-weight: bold;
                font-size: 12px;
            }
            .status-present { background-color: #d4edda; color: #155724; }
            .status-late { background-color: #fff3cd; color: #856404; }
            .status-absent { background-color: #f8d7da; color: #721c24; }
            .status-excused { background-color: #d1ecf1; color: #0c5460; }
            .footer {
                margin-top: 40px;
                text-align: center;
                font-size: 12px;
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 20px;
            }
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Attendance Record</h1>
            <p>Events Attendance Management System</p>
            <p>Generated on: <?php echo date('F j, Y \a\t g:i A'); ?></p>
        </div>
        
        <div class="info-section">
            <h3>Event Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Event Title:</div>
                    <div class="info-value"><?php echo htmlspecialchars($attendance['event_title']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Event Date:</div>
                    <div class="info-value"><?php echo date('F j, Y', strtotime($attendance['event_date'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Start Time:</div>
                    <div class="info-value"><?php echo date('g:i A', strtotime($attendance['start_time'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">End Time:</div>
                    <div class="info-value"><?php echo date('g:i A', strtotime($attendance['end_time'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Location:</div>
                    <div class="info-value"><?php echo htmlspecialchars($attendance['location']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Event Type:</div>
                    <div class="info-value"><?php echo htmlspecialchars($attendance['event_type']); ?></div>
                </div>
            </div>
        </div>
        
        <div class="info-section">
            <h3>Student Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Student Name:</div>
                    <div class="info-value"><?php echo htmlspecialchars($attendance['firstname'] . ' ' . $attendance['lastname']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><?php echo htmlspecialchars($attendance['email']); ?></div>
                </div>
            </div>
        </div>
        
        <div class="info-section">
            <h3>Attendance Details</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Check-in Time:</div>
                    <div class="info-value">
                        <?php 
                        if ($attendance['check_in_time']) {
                            echo date('F j, Y \a\t g:i A', strtotime($attendance['check_in_time']));
                        } else {
                            echo 'Not checked in';
                        }
                        ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Check-out Time:</div>
                    <div class="info-value">
                        <?php 
                        if ($attendance['check_out_time']) {
                            echo date('F j, Y \a\t g:i A', strtotime($attendance['check_out_time']));
                        } else {
                            echo 'Not checked out';
                        }
                        ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status:</div>
                    <div class="info-value">
                        <span class="status-badge status-<?php echo strtolower($attendance['remark']); ?>">
                            <?php echo htmlspecialchars($attendance['remark']); ?>
                        </span>
                    </div>
                </div>
                <?php if ($attendance['notes']): ?>
                <div class="info-item">
                    <div class="info-label">Notes:</div>
                    <div class="info-value"><?php echo htmlspecialchars($attendance['notes']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <p>This is an official attendance record generated by the Events Attendance Management System.</p>
            <p>Record ID: <?php echo $attendance['attendance_id']; ?> | Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <script>
            // Auto-print when page loads
            window.onload = function() {
                window.print();
            };
        </script>
    </body>
    </html>
    <?php
    
} catch (Exception $e) {
    error_log("Error printing attendance: " . $e->getMessage());
    
    header('Content-Type: text/html');
    echo "<!DOCTYPE html><html><head><title>SANHS EAMS - Error</title></head><body>";
    echo "<h1>Error</h1>";
    echo "<p>Error loading attendance record: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</body></html>";
}
?>
