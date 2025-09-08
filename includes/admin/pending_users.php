<?php
// Define IN_APP to allow access to utilities
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Include common utilities
require_once __DIR__ . "/../../utils/index.php";

// Get database connection using common utility
$con = getDatabaseConnection();
if (!$con) {
    echo displayError("Database connection failed.");
    exit;
}

// Pagination settings
$records_per_page = 10;
$current_page = validateInput($_GET['page'] ?? 1, 'int', 1);

// Get total number of pending users using common utility (excluding deactivated accounts)
$total_records = getRecordCount('users', "status = 'Pending' AND (account_status IS NULL OR account_status != ?)", ['deactivated']);
if ($total_records === false) {
    echo displayError("Failed to get pending users count.");
    exit;
}

// Calculate pagination using common utility
$pagination = calculatePagination($total_records, $records_per_page, $current_page);

// Only run the query if there are pending users
if ($total_records > 0) {
    // Get pending users with pagination using common utility (excluding deactivated accounts)
    $query = "SELECT user_id, firstname, lastname, email, role, status, verification_status, created_at 
              FROM users 
              WHERE status = 'Pending' AND (account_status != 'deactivated' OR account_status IS NULL)
              ORDER BY created_at ASC 
              LIMIT ?, ?";

    $result = executeQuery($query, [$pagination['offset'], $pagination['recordsPerPage']], 'ii');
    if (!$result) {
        echo displayError("Failed to fetch pending users.");
        exit;
    }
    
    // Store the result for the view
    $users = $result;
} else {
    $result = null;
    $users = null;
}

// Configure the table view for pending users
$tableConfig = [
    'title' => 'Pending Users Approval',
    'showCheckboxes' => true,
    'showStatus' => true,
    'showVerification' => true,
    'showRegistrationDate' => true,
    'actions' => ['view', 'approve', 'reject'],
    'bulkActions' => true,
    'searchPlaceholder' => 'Search pending users...',
    'emptyMessage' => 'No pending users found. All users have been processed.',
    'paginationUrl' => '?page=pending_users&page_num='
];

// Set variables for the view
$totalUsersCount = $total_records;
$limit = $pagination['recordsPerPage'];
$page = $pagination['currentPage'];
$offset = $pagination['offset'];

// Include the reusable table view
include __DIR__ . "/views/users_table_view.php";
?>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bx bx-show"></i> User Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="viewUserContent">
                <!-- User details will be loaded here via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Individual Approve Confirmation Modal -->
<div class="modal fade" id="approveUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bx bx-check-circle"></i> Confirm User Approval
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this user?</p>
                <div class="alert alert-info">
                    <strong id="approveUserName"></strong><br>
                    <div id="approveUserDetails"></div>
                </div>
                <p class="text-success"><strong>This will activate the user account and send a welcome email.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn">Approve User</button>
            </div>
        </div>
    </div>
</div>

<!-- Individual Reject Confirmation Modal -->
<div class="modal fade" id="rejectUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bx bx-x-circle"></i> Confirm User Rejection
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reject this user?</p>
                <div class="alert alert-warning">
                    <strong id="rejectUserName"></strong><br>
                    <div id="rejectUserDetails"></div>
                </div>
                <p class="text-danger"><strong>This will permanently remove the user account.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRejectBtn">Reject User</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve All Confirmation Modal -->
<div class="modal fade" id="approveAllModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bx bx-check-double"></i> Confirm Bulk Approval
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve all <strong id="approveAllCount">0</strong> selected users?</p>
                <p class="text-info"><strong>This will activate all selected user accounts.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmApproveAllBtn">Approve All</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript functionality is handled by pending_users.js -->