<?php
// Define IN_APP to allow access to utilities
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Include utilities
require_once __DIR__ . "/../../utils/index.php";

// Initialize database if needed
if (!initializeDatabase()) {
    echo displayError("Failed to initialize database.");
    exit;
}

// Pagination setup
$limit = 10;
$current_page = validateInput($_GET['page_num'] ?? 1, 'int', 1);

// Get total users count using utility (excluding deactivated accounts)
$totalUsersCount = getRecordCount('users', 'account_status IS NULL OR account_status != ?', ['deactivated']);
if ($totalUsersCount === false) {
    echo displayError("Failed to get users count.");
    exit;
}

// Calculate pagination using utility
$pagination = calculatePagination($totalUsersCount, $limit, $current_page);

// Get current page users using utility (excluding deactivated accounts)
$query = "SELECT * FROM users WHERE account_status != 'deactivated' OR account_status IS NULL ORDER BY created_at DESC LIMIT ?, ?";
$users = executeQuery($query, [$pagination['offset'], $pagination['recordsPerPage']], 'ii');
if (!$users) {
    echo displayError("Failed to fetch users.");
    exit;
}
?>

<div class="card mt-4 shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 d-flex align-items-center">
            <i class="bx bx-group mr-2"></i>
            Users
        </h5>
        <div>
            <span class="badge badge-light badge-pill"><?php echo $totalUsersCount; ?> Total</span>
        </div>
    </div>
    <div class="card-body">
        <!-- Search and Filter Section -->
        <div class="row mb-3">
            <div class="col-md-4 col-sm-12 mb-2">
                <div class="input-group">
                    <input type="text" class="form-control" id="userSearch" placeholder="Search users...">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bx bx-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <select class="form-control" id="roleFilter">
                    <option value="">All Roles</option>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="sslg">SSLG</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <select class="form-control" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="approved">Approved</option>
                    <option value="pending">Pending</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-12 mb-2">
                <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="clearFilters">
                    <i class="bx bx-x"></i> Clear
                </button>
            </div>
        </div>

        <!-- Results Summary -->
        <p class="mb-2 text-muted">
            Showing <?php echo min($pagination['offset'] + 1, $totalUsersCount); ?> to
            <?php echo min($pagination['offset'] + $pagination['recordsPerPage'], $totalUsersCount); ?> of <?php echo $totalUsersCount; ?> results
        </p>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="usersTable">
                <thead class="thead-light">
                    <tr>
                        <th>Profile</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Verification</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($users && mysqli_num_rows($users) > 0):
                    while ($row = mysqli_fetch_assoc($users)):
                        $id = $row['user_id'];
                        $fullName = sanitizeOutput($row['firstname'] . ' ' . $row['lastname']);
                        $email = sanitizeOutput($row['email']);
                        $userRole = sanitizeOutput($row['role'] ?: 'N/A');
                        $userStatus = sanitizeOutput($row['status'] ?? 'Pending');
                        $verification = sanitizeOutput($row['verification_status']);
                        $profilePhoto = getProfilePhoto($row['profile_photo'] ?? '', 35);
                        
                        // Get badge classes using common utility
                        $role_badge = getBadgeClass($userRole, 'role');
                        $status_badge = getBadgeClass($userStatus, 'status');
                        $verification_badge = getBadgeClass($verification, 'verification');
                ?>
                    <tr data-user-id="<?php echo $id; ?>">
                        <td><?php echo $profilePhoto; ?></td>
                        <td>
                            <div><strong><?php echo $fullName; ?></strong></div>
                            <small class="text-muted">ID: <?php echo $id; ?></small>
                        </td>
                        <td><?php echo $email; ?></td>
                        <td><span class="badge <?php echo $role_badge; ?>"><?php echo ucfirst($userRole); ?></span></td>
                        <td><span class="badge <?php echo $status_badge; ?>"><?php echo ucfirst($userStatus); ?></span></td>
                        <td><span class="badge <?php echo $verification_badge; ?>"><?php echo ucfirst($verification); ?></span></td>
                        <td>
                            <?php echo generateActionButtons(['view', 'edit', 'delete'], $id, 'user', ['role' => $userRole]); ?>
                        </td>
                    </tr>
                <?php
                    endwhile;
                else:
                    $colspan = 7;
                ?>
                    <tr id="no-results-row">
                        <td colspan="<?php echo $colspan; ?>" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <i class="bx bx-user-x" style="font-size: 2rem; color: #6c757d;"></i>
                                <p class="mt-2 mb-0">No users found</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php echo generatePagination($pagination, '?page=users&page_num='); ?>
    </div>
</div>

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

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bx bx-edit"></i> Edit User
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="editUserContent">
                <!-- Edit form will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bx bx-user-x"></i> Deactivate User Account
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to deactivate this user account?</p>
                <div class="alert alert-danger">
                    <strong>What this means:</strong>
                    <ul class="mb-0 mt-2">
                        <li>The user will no longer be able to log in</li>
                        <li>All their data and records will be preserved</li>
                        <li>You can reactivate the account later if needed</li>
                        <li>The user will be moved to the "Deactivated Users" section</li>
                    </ul>
                </div>
                <p class="text-info"><strong>This is a reversible action - you can reactivate the account anytime.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteUserBtn">Deactivate Account</button>
            </div>
        </div>
    </div>
</div>

<!-- Note: JavaScript event handlers are now in users.js -->
