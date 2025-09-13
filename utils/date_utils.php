<?php
/**
 * Date and Time Utility Functions
 * Centralized date/time handling with consistent timezone
 */

if (!defined('IN_APP')) {
    header('HTTP/1.0 403 Forbidden');
    exit();
}

/**
 * Get current date and time in Philippines timezone
 * @param string $format Date format (default: 'Y-m-d H:i:s')
 * @return string Formatted date string
 */
function getCurrentDateTime($format = 'Y-m-d H:i:s') {
    // Ensure timezone is set to Philippines
    date_default_timezone_set('Asia/Manila');
    return date($format);
}

/**
 * Format date for display in Philippines timezone
 * @param string $date Date string or timestamp
 * @param string $format Display format (default: 'M j, Y g:i A')
 * @return string Formatted date string
 */
function formatDisplayDate($date, $format = 'M j, Y g:i A') {
    if (empty($date)) {
        return 'N/A';
    }
    // Ensure timezone is set to Philippines
    date_default_timezone_set('Asia/Manila');
    return date($format, strtotime($date));
}

/**
 * Format date for input fields (Y-m-d format)
 * @param string $date Date string
 * @return string Formatted date for input fields
 */
function formatInputDate($date) {
    if (empty($date)) {
        return '';
    }
    // Ensure timezone is set to Philippines
    date_default_timezone_set('Asia/Manila');
    return date('Y-m-d', strtotime($date));
}

/**
 * Format time for input fields (H:i format)
 * @param string $time Time string
 * @return string Formatted time for input fields
 */
function formatInputTime($time) {
    if (empty($time)) {
        return '';
    }
    // Ensure timezone is set to Philippines
    date_default_timezone_set('Asia/Manila');
    return date('H:i', strtotime($time));
}

/**
 * Format time for display (h:i A format)
 * @param string $time Time string
 * @return string Formatted time for display
 */
function formatDisplayTime($time) {
    if (empty($time)) {
        return 'N/A';
    }
    // Ensure timezone is set to Philippines
    date_default_timezone_set('Asia/Manila');
    return date('h:i A', strtotime($time));
}

/**
 * Format event date for display
 * @param string $date Event date
 * @return string Formatted event date
 */
function formatEventDate($date) {
    if (empty($date)) {
        return 'N/A';
    }
    // Ensure timezone is set to Philippines
    date_default_timezone_set('Asia/Manila');
    return date('M d, Y', strtotime($date));
}

/**
 * Format event date with day of week
 * @param string $date Event date
 * @return string Formatted event date with day
 */
function formatEventDateWithDay($date) {
    if (empty($date)) {
        return 'N/A';
    }
    // Ensure timezone is set to Philippines
    date_default_timezone_set('Asia/Manila');
    return date('F d, Y (l)', strtotime($date));
}

/**
 * Get current timestamp
 * @return int Current timestamp
 */
function getCurrentTimestamp() {
    // Ensure timezone is set to Philippines
    date_default_timezone_set('Asia/Manila');
    return time();
}

/**
 * Check if current time is between event start and end times
 * @param string $event_date Event date (Y-m-d format)
 * @param string $start_time Start time (H:i:s format)
 * @param string $end_time End time (H:i:s format)
 * @return string Event status: 'Upcoming', 'Ongoing', or 'Finished'
 */
function getEventStatus($event_date, $start_time, $end_time) {
    // Ensure timezone is set to Philippines
    date_default_timezone_set('Asia/Manila');
    
    $now = new DateTime();
    $event_start = new DateTime($event_date . ' ' . $start_time);
    $event_end = new DateTime($event_date . ' ' . $end_time);
    
    if ($now < $event_start) {
        return 'Upcoming';
    } elseif ($now >= $event_start && $now <= $event_end) {
        return 'Ongoing';
    } else {
        return 'Finished';
    }
}

/**
 * Get timezone information
 * @return array Timezone information
 */
function getTimezoneInfo() {
    // Ensure timezone is set to Philippines
    date_default_timezone_set('Asia/Manila');
    
    return [
        'timezone' => date_default_timezone_get(),
        'current_time' => date('Y-m-d H:i:s T'),
        'timestamp' => time(),
        'utc_time' => gmdate('Y-m-d H:i:s T'),
        'offset' => date('P'),
        'is_dst' => date('I')
    ];
}
?>
