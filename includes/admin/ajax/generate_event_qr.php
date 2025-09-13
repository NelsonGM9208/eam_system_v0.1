<?php
/**
 * AJAX endpoint to generate QR codes for events
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . "/../../../utils/index.php";
require_once __DIR__ . "/../../../vendor/autoload.php";

// Check if QR code library is available
if (!class_exists('Endroid\QrCode\QrCode')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'QR code library not installed. Please install endroid/qr-code via Composer.'
    ]);
    exit;
}

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\PdfWriter;

try {
    $event_id = intval($_GET['event_id'] ?? 0);
    $format = $_GET['format'] ?? 'json'; // json, png, pdf
    
    if ($event_id <= 0) {
        throw new Exception('Invalid event ID');
    }
    
    $con = getDatabaseConnection();
    
    // Get event details
    $event_query = "SELECT * FROM events WHERE event_id = ? AND approval_status = 'Approved'";
    $stmt = $con->prepare($event_query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    
    if (!$event) {
        throw new Exception('Event not found or not approved');
    }
    
    // Check if event is finished
    if ($event['event_status'] === 'Finished') {
        throw new Exception('QR codes can only be generated for upcoming events.');
    }
    
    // Generate QR code content
    $timestamp = time();
    $qr_content = "EVENT_{$event_id}_{$timestamp}";
    
    // Create QR code
    $qrCode = new QrCode($qr_content, size: 300, margin: 10);
    
    if ($format === 'png') {
        // Return PNG image
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="event_' . $event_id . '_qr.png"');
        echo $result->getString();
        exit;
        
    } elseif ($format === 'pdf') {
        // Return PDF
        $writer = new PdfWriter();
        $result = $writer->write($qrCode);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="event_' . $event_id . '_qr.pdf"');
        echo $result->getString();
        exit;
        
    } else {
        // Return JSON with QR code data
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        $qr_image = base64_encode($result->getString());
        
        // Calculate validity times
        $event_datetime = $event['event_date'] . ' ' . $event['start_time'];
        $valid_from = date('Y-m-d H:i:s', strtotime($event_datetime . ' -1 hour'));
        $valid_until = $event['event_date'] . ' ' . $event['end_time'];
        
        $response = [
            'success' => true,
            'event' => [
                'event_id' => $event['event_id'],
                'title' => $event['title'],
                'event_date' => $event['event_date'],
                'start_time' => $event['start_time'],
                'end_time' => $event['end_time'],
                'location' => $event['location'],
                'event_type' => $event['event_type'],
                'event_status' => $event['event_status']
            ],
            'qr_code' => [
                'content' => $qr_content,
                'image' => 'data:image/png;base64,' . $qr_image,
                'valid_from' => $valid_from,
                'valid_until' => $valid_until,
                'generated_at' => date('Y-m-d H:i:s', $timestamp)
            ]
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    error_log("Error generating QR code: " . $e->getMessage());
    
    if ($format === 'png' || $format === 'pdf') {
        http_response_code(500);
        echo "Error generating QR code: " . $e->getMessage();
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>
