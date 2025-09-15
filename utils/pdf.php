<?php
/**
 * PDF Utility Functions using mPDF
 * Provides common PDF generation functionality
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

/**
 * Create and configure mPDF instance
 * 
 * @param array $options Configuration options
 * @return Mpdf
 */
function createMpdfInstance($options = []) {
    // Default configuration
    $defaultConfig = [
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 16,
        'margin_bottom' => 16,
        'margin_header' => 9,
        'margin_footer' => 9,
        'tempDir' => sys_get_temp_dir(),
        'default_font_size' => 10,
        'default_font' => 'Arial'
    ];
    
    // Merge with provided options
    $config = array_merge($defaultConfig, $options);
    
    // Create mPDF instance
    $mpdf = new Mpdf($config);
    
    // Set default styles
    $mpdf->SetTitle('EAM System Report');
    $mpdf->SetAuthor('EAM System');
    $mpdf->SetCreator('EAM System');
    $mpdf->SetSubject('System Generated Report');
    
    return $mpdf;
}

/**
 * Generate PDF from HTML content
 * 
 * @param string $html HTML content
 * @param string $filename Output filename
 * @param string $outputMode Output mode (I=inline, D=download, F=file, S=string)
 * @param array $options mPDF configuration options
 * @return void|string
 */
function generatePdfFromHtml($html, $filename = 'document.pdf', $outputMode = 'D', $options = []) {
    try {
        $mpdf = createMpdfInstance($options);
        
        // Write HTML content
        $mpdf->WriteHTML($html);
        
        // Output the PDF
        return $mpdf->Output($filename, $outputMode);
        
    } catch (Exception $e) {
        error_log("PDF Generation Error: " . $e->getMessage());
        throw new Exception("Failed to generate PDF: " . $e->getMessage());
    }
}

/**
 * Generate attendance report PDF
 * 
 * @param array $attendanceData Attendance records
 * @param array $studentInfo Student information
 * @param array $filters Applied filters
 * @param string $outputMode Output mode
 * @return void|string
 */
function generateAttendanceReportPdf($attendanceData, $studentInfo, $filters = [], $outputMode = 'D') {
    $html = buildAttendanceReportHtml($attendanceData, $studentInfo, $filters);
    $filename = 'attendance_report_' . $studentInfo['firstname'] . '_' . $studentInfo['lastname'] . '_' . date('Y-m-d') . '.pdf';
    
    return generatePdfFromHtml($html, $filename, $outputMode);
}

/**
 * Build HTML content for attendance report
 * 
 * @param array $attendanceData Attendance records
 * @param array $studentInfo Student information
 * @param array $filters Applied filters
 * @return string
 */
function buildAttendanceReportHtml($attendanceData, $studentInfo, $filters = []) {
    $totalRecords = count($attendanceData);
    $presentCount = count(array_filter($attendanceData, function($r) { return $r['remark'] === 'Present'; }));
    $lateCount = count(array_filter($attendanceData, function($r) { return $r['remark'] === 'Late'; }));
    $absentCount = count(array_filter($attendanceData, function($r) { return $r['remark'] === 'Absent'; }));
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>SANHS EAMS - Attendance Report</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 20px; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
            .header h1 { margin: 0; color: #333; font-size: 18px; }
            .header h2 { margin: 5px 0; color: #666; font-size: 14px; }
            .student-info { margin-bottom: 20px; }
            .student-info table { width: 100%; border-collapse: collapse; }
            .student-info td { padding: 5px; border: 1px solid #ddd; }
            .student-info td:first-child { background-color: #f5f5f5; font-weight: bold; width: 30%; }
            .summary { margin-bottom: 20px; }
            .summary table { width: 100%; border-collapse: collapse; }
            .summary td { padding: 8px; border: 1px solid #ddd; text-align: center; }
            .summary td:first-child { background-color: #f5f5f5; font-weight: bold; }
            .attendance-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            .attendance-table th, .attendance-table td { padding: 6px; border: 1px solid #ddd; text-align: left; }
            .attendance-table th { background-color: #f5f5f5; font-weight: bold; }
            .attendance-table tr:nth-child(even) { background-color: #f9f9f9; }
            .status-present { color: #28a745; font-weight: bold; }
            .status-late { color: #ffc107; font-weight: bold; }
            .status-absent { color: #dc3545; font-weight: bold; }
            .footer { margin-top: 30px; text-align: center; font-size: 8px; color: #666; }
            .page-break { page-break-before: always; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>San Agustin National High School</h1>
            <h2>Event Attendance Management System</h2>
            <h2>Attendance Report</h2>
        </div>
        
        <div class="student-info">
            <table>
                <tr><td>Student Name:</td><td>' . htmlspecialchars($studentInfo['firstname'] . ' ' . $studentInfo['lastname']) . '</td></tr>
                <tr><td>Email:</td><td>' . htmlspecialchars($studentInfo['email']) . '</td></tr>
                <tr><td>Report Generated:</td><td>' . date('F j, Y g:i A') . '</td></tr>';
    
    if (!empty($filters)) {
        $html .= '<tr><td>Filters Applied:</td><td>';
        $filterTexts = [];
        if (!empty($filters['event'])) $filterTexts[] = 'Event: ' . $filters['event'];
        if (!empty($filters['status'])) $filterTexts[] = 'Status: ' . $filters['status'];
        if (!empty($filters['date_from'])) $filterTexts[] = 'From: ' . $filters['date_from'];
        if (!empty($filters['date_to'])) $filterTexts[] = 'To: ' . $filters['date_to'];
        $html .= implode(', ', $filterTexts) . '</td></tr>';
    }
    
    $html .= '
            </table>
        </div>
        
        <div class="summary">
            <table>
                <tr>
                    <td>Total Records</td>
                    <td>Present</td>
                    <td>Late</td>
                    <td>Absent</td>
                </tr>
                <tr>
                    <td>' . $totalRecords . '</td>
                    <td class="status-present">' . $presentCount . '</td>
                    <td class="status-late">' . $lateCount . '</td>
                    <td class="status-absent">' . $absentCount . '</td>
                </tr>
            </table>
        </div>
        
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Time In</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($attendanceData as $record) {
        $statusClass = '';
        switch ($record['remark']) {
            case 'Present':
                $statusClass = 'status-present';
                break;
            case 'Late':
                $statusClass = 'status-late';
                break;
            case 'Absent':
                $statusClass = 'status-absent';
                break;
        }
        
        $html .= '
                <tr>
                    <td>' . htmlspecialchars($record['event_title']) . '</td>
                    <td>' . date('M j, Y', strtotime($record['event_date'])) . '</td>
                    <td>' . date('g:i A', strtotime($record['start_time'])) . ' - ' . date('g:i A', strtotime($record['end_time'])) . '</td>
                    <td>' . htmlspecialchars($record['location']) . '</td>
                    <td class="' . $statusClass . '">' . htmlspecialchars($record['remark']) . '</td>
                    <td>' . ($record['time_in'] ? date('g:i A', strtotime($record['time_in'])) : 'N/A') . '</td>
                    <td>' . htmlspecialchars($record['remarks'] ?? '') . '</td>
                </tr>';
    }
    
    $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>This report was generated by the EAM System on ' . date('F j, Y \a\t g:i A') . '</p>
            <p>San Agustin National High School - Event Attendance Management System</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Generate QR code PDF
 * 
 * @param string $qrCodeData QR code data
 * @param string $eventTitle Event title
 * @param string $outputMode Output mode
 * @return void|string
 */
function generateQrCodePdf($qrCodeData, $eventTitle, $outputMode = 'D') {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>SANHS EAMS - QR Code - ' . htmlspecialchars($eventTitle) . '</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
            .header { margin-bottom: 30px; }
            .header h1 { color: #333; font-size: 18px; margin-bottom: 10px; }
            .header h2 { color: #666; font-size: 14px; }
            .qr-container { margin: 30px 0; }
            .qr-code { max-width: 300px; height: auto; }
            .footer { margin-top: 30px; font-size: 10px; color: #666; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>San Agustin National High School</h1>
            <h2>Event QR Code</h2>
            <h2>' . htmlspecialchars($eventTitle) . '</h2>
        </div>
        
        <div class="qr-container">
            <img src="data:image/png;base64,' . base64_encode($qrCodeData) . '" class="qr-code" alt="QR Code">
        </div>
        
        <div class="footer">
            <p>Scan this QR code to access event information</p>
            <p>Generated on ' . date('F j, Y \a\t g:i A') . '</p>
        </div>
    </body>
    </html>';
    
    $filename = 'qr_code_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $eventTitle) . '_' . date('Y-m-d') . '.pdf';
    
    return generatePdfFromHtml($html, $filename, $outputMode);
}
?>
