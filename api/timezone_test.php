<?php
/**
 * Timezone Test Endpoint
 * This endpoint shows the current time in different timezones for debugging
 */

header('Content-Type: application/json');

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

$timezone_info = [
    'server_timezone' => date_default_timezone_get(),
    'current_time_philippines' => date('Y-m-d H:i:s T'),
    'current_timestamp' => time(),
    'utc_time' => gmdate('Y-m-d H:i:s T'),
    'philippines_offset' => date('P'),
    'is_dst' => date('I') // 1 if daylight saving time, 0 otherwise
];

echo json_encode($timezone_info, JSON_PRETTY_PRINT);
?>
