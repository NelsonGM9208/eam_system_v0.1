<?php
// Define IN_APP to allow access to utilities
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Include utilities
require_once __DIR__ . "/../../utils/index.php";

// Users Management Page
// This file handles the display and management of all users in the system

// Pagination setup
$limit = 10;
$page = isset($_GET['page_num']) && is_numeric($_GET['page_num']) ? (int) $_GET['page_num'] : 1;
$offset = ($page - 1) * $limit;

// Initialize database if needed
if (!initializeDatabase()) {
    echo displayError("Failed to initialize database.");
    exit;
}

// Total users
$totalUsersCount = getRecordCount('users');
if ($totalUsersCount === false) {
    echo displayError("Failed to get users count.");
    exit;
}

// Current page users
$users = executeQuery("SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?", [$limit, $offset], 'ii');
if (!$users) {
    echo displayError("Failed to get users data.");
    exit;
}

// Configure the table view for all users
$tableConfig = [
    'title' => 'Users Management',
    'showCheckboxes' => false,
    'showStatus' => true,
    'showVerification' => true,
    'showRegistrationDate' => false,
    'actions' => ['view', 'edit', 'delete'],
    'bulkActions' => false,
    'searchPlaceholder' => 'Search users...',
    'emptyMessage' => 'No users found.',
    'paginationUrl' => '?page=users&page_num='
];

// Variables are already set above for the view

// Include the reusable table view
include __DIR__ . "/usersTBL.php";
?>

<!-- User Management Modals are handled by usersTBL.php -->

