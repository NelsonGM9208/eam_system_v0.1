<?php
// Define IN_APP to allow access to utilities
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Include utilities
require_once __DIR__ . "/../../utils/index.php";

// Deactivated Users Management Page
// This file handles the display and management of deactivated users in the system

// Pagination setup
$limit = 10;
$current_page = validateInput($_GET['page_num'] ?? 1, 'int', 1);

// Get total deactivated users count using utility
$totalUsersCount = getRecordCount('users', 'account_status = ?', ['deactivated']);
if ($totalUsersCount === false) {
    echo displayError("Failed to get deactivated users count.");
    exit;
}

// Calculate pagination using utility
$pagination = calculatePagination($totalUsersCount, $limit, $current_page);

// Get current page deactivated users using utility
$query = "SELECT * FROM users WHERE account_status = 'deactivated' ORDER BY updated_at DESC LIMIT ?, ?";
$users = executeQuery($query, [$pagination['offset'], $pagination['recordsPerPage']], 'ii');
if (!$users) {
    echo displayError("Failed to fetch deactivated users.");
    exit;
}
?>

<div class="card mt-4 shadow-sm border-danger">
    <div class="card-header bg-danger text-white">
        <div class="d-flex align-items-center">
            <i class="bx bx-user-x me-2" style="font-size: 1.5rem;"></i>
            <h4 class="mb-0">Deactivated Users</h4>
        </div>
        <p class="mb-0 mt-2 opacity-75">Manage deactivated user accounts</p>
    </div>
    
    <div class="card-body">
        <!-- Search and Filter Section -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-danger text-white">
                        <i class="bx bx-search"></i>
                    </span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search deactivated users...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="roleFilter">
                    <option value="">All Roles</option>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="sslg">SSLG</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <!-- <div class="col-md-3">
                <button type="button" class="btn btn-outline-danger btn-sm w-100" id="refreshBtn">
                    <i class="bx bx-refresh"></i> Refresh
                </button>
            </div> -->
        </div>

        <!-- Results Summary -->
        <p class="mb-2 text-muted">
            Showing <?php echo min($pagination['offset'] + 1, $totalUsersCount); ?> to
            <?php echo min($pagination['offset'] + $limit, $totalUsersCount); ?> of <?php echo $totalUsersCount; ?> deactivated accounts
        </p>

        <!-- Deactivated Users Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="deactivatedUsersTable">
                <thead class="thead-dark">
                    <tr>
                        <th>Profile</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Account Status</th>
                        <th>Deactivated Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($users && is_object($users) && mysqli_num_rows($users) > 0):
                        while ($row = mysqli_fetch_assoc($users)):
                            $id = $row['user_id'];
                            $fullName = sanitizeOutput($row['firstname'] . ' ' . $row['lastname']);
                            $email = sanitizeOutput($row['email']);
                            $userRole = sanitizeOutput($row['role'] ?: 'N/A');
                            $userStatus = sanitizeOutput($row['status'] ?? 'Pending');
                            $accountStatus = sanitizeOutput($row['account_status'] ?? 'active');
                            $deactivatedDate = formatDate($row['updated_at'] ?? '');
                            
                            $profilePhoto = getProfilePhoto($row['profile_photo'] ?? '', 35);

                            // Get badge classes using common utility
                            $role_badge = getBadgeClass($userRole, 'role');
                            $status_badge = getBadgeClass($userStatus, 'status');
                            $account_status_badge = getBadgeClass($accountStatus, 'account_status');
                    ?>
                        <tr data-user-id="<?php echo $id; ?>" class="table-danger">
                            <td><?php echo $profilePhoto; ?></td>
                            <td>
                                <div><strong><?php echo $fullName; ?></strong></div>
                                <small class="text-muted">ID: <?php echo $id; ?></small>
                            </td>
                            <td><?php echo $email; ?></td>
                            <td><span class="badge <?php echo $role_badge; ?>"><?php echo ucfirst($userRole); ?></span></td>
                            <td><span class="badge <?php echo $status_badge; ?>"><?php echo ucfirst($userStatus); ?></span></td>
                            <td>
                                <span class="badge <?php echo $account_status_badge; ?>">
                                    <?php echo ucfirst($accountStatus); ?>
                                </span>
                            </td>
                            <td><?php echo $deactivatedDate; ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="viewUser(<?php echo $id; ?>)" title="View Details">
                                        <i class="bx bx-show"></i>
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm" onclick="reactivateUser(<?php echo $id; ?>)" title="Reactivate Account">
                                        <i class="bx bx-check-circle"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                <div class="py-4 d-flex flex-column align-items-center justify-content-center">
                                    <i class="bx bx-user-check" style="font-size: 2rem; color: #28a745;"></i>
                                    <p class="mt-2 mb-0">No deactivated users found. All accounts are active!</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php
        $paginationData = [
            'currentPage' => $pagination['currentPage'],
            'totalPages' => $pagination['totalPages'],
            'offset' => $pagination['offset'],
            'recordsPerPage' => $pagination['recordsPerPage'],
            'totalRecords' => $totalUsersCount
        ];
        echo generatePagination($paginationData, '?page=deactivated_users&page_num=');
        ?>
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

<!-- Reactivate User Confirmation Modal -->
<div class="modal fade" id="reactivateUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bx bx-check-circle"></i> Confirm Account Reactivation
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reactivate this user account?</p>
                <div class="alert alert-success">
                    <div class="mb-2">
                        <strong>Name:</strong> <span id="reactivateUserName">Loading...</span>
                    </div>
                    <div class="mb-2">
                        <strong>Email:</strong> <span id="reactivateUserEmail">Loading...</span>
                    </div>
                    <div class="mb-2">
                        <strong>Role:</strong> <span id="reactivateUserRole">Loading...</span>
                    </div>
                    <div class="mb-2">
                        <strong>Status:</strong> <span class="badge badge-danger">Deactivated</span>
                    </div>
                    <div class="mb-0">
                        <strong>Deactivated:</strong> <span id="reactivateUserDate">Loading...</span>
                    </div>
                </div>
                <p class="text-success"><strong>This will restore the user's ability to log in and access the system.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmReactivateBtn">Reactivate Account</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript functionality -->
<script>
let currentUserId = null;

function viewUser(userId) {
    // Show loading state
    document.getElementById('viewUserContent').innerHTML = '<div class="text-center p-4"><i class="bx bx-loader-alt bx-spin" style="font-size: 2rem;"></i><p class="mt-2">Loading user details...</p></div>';
    $('#viewUserModal').modal('show');
    
    // Load user details via AJAX using the same method as users.js
    fetch('/eam_system_v0.1.1/includes/admin/load_modal.php?modal=user_details&id=' + userId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('viewUserContent').innerHTML = data;
        })
        .catch(error => {
            console.error('Error loading user details:', error);
            document.getElementById('viewUserContent').innerHTML = '<div class="text-center p-4 text-danger"><i class="bx bx-error" style="font-size: 2rem;"></i><p class="mt-2">Failed to load user details</p></div>';
        });
}

function reactivateUser(userId) {
    currentUserId = userId;
    
    // Get basic user info for confirmation
    fetch('/eam_system_v0.1.1/includes/admin/load_modal.php?modal=user_details&id=' + userId)
        .then(response => response.text())
        .then(data => {
            // Extract specific information from the response
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = data;
            
            // Extract name from h6 element
            const nameElement = tempDiv.querySelector('h6');
            const userName = nameElement ? nameElement.textContent.trim() : 'Unknown User';
            
            // Extract email from table
            const emailRow = Array.from(tempDiv.querySelectorAll('tr')).find(row => 
                row.textContent.includes('Email')
            );
            const email = emailRow ? emailRow.querySelector('td').textContent.trim() : 'Unknown Email';
            
            // Extract role from table
            const roleRow = Array.from(tempDiv.querySelectorAll('tr')).find(row => 
                row.textContent.includes('Role')
            );
            const role = roleRow ? roleRow.querySelector('td').textContent.trim() : 'Unknown Role';
            
            // Extract last updated date
            const updatedRow = Array.from(tempDiv.querySelectorAll('tr')).find(row => 
                row.textContent.includes('Last Updated')
            );
            const lastUpdated = updatedRow ? updatedRow.querySelector('td').textContent.trim() : 'Unknown Date';
            
            // Populate the modal fields
            document.getElementById('reactivateUserName').textContent = userName;
            document.getElementById('reactivateUserEmail').textContent = email;
            document.getElementById('reactivateUserRole').textContent = role;
            document.getElementById('reactivateUserDate').textContent = lastUpdated;
            
            $('#reactivateUserModal').modal('show');
        })
        .catch(error => {
            console.error('Error loading user details:', error);
            // Set default values on error
            document.getElementById('reactivateUserName').textContent = 'Unknown User';
            document.getElementById('reactivateUserEmail').textContent = 'Unknown Email';
            document.getElementById('reactivateUserRole').textContent = 'Unknown Role';
            document.getElementById('reactivateUserDate').textContent = 'Unknown Date';
            $('#reactivateUserModal').modal('show');
        });
}

// Reactivate user confirmation
document.getElementById('confirmReactivateBtn').addEventListener('click', function() {
    if (!currentUserId) return;
    
    const $btn = $(this);
    const originalText = $btn.html();
    
    // Show loading state
    $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Reactivating...');
    
    const formData = new FormData();
    formData.append('action', 'reactivate');
    formData.append('id', currentUserId);
    
    fetch('/eam_system_v0.1.1/config/users_crud.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('successfully')) {
            // Show success message
            alert(data);
            // Hide modal and reload page
            $('#reactivateUserModal').modal('hide');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Error: ' + data);
            // Re-enable button on error
            $btn.prop('disabled', false).html(originalText);
        }
    })
    .catch(error => {
        console.error('Error reactivating user:', error);
        alert('Error reactivating user account');
        // Re-enable button on error
        $btn.prop('disabled', false).html(originalText);
    });
});

// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#deactivatedUsersTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Role filter functionality
document.getElementById('roleFilter').addEventListener('change', function() {
    const selectedRole = this.value.toLowerCase();
    const rows = document.querySelectorAll('#deactivatedUsersTable tbody tr');
    
    rows.forEach(row => {
        if (!selectedRole) {
            row.style.display = '';
        } else {
            const roleBadge = row.querySelector('.badge');
            const roleText = roleBadge ? roleBadge.textContent.toLowerCase() : '';
            row.style.display = roleText.includes(selectedRole) ? '' : 'none';
        }
    });
});

// Refresh functionality
//document.getElementById('refreshBtn').addEventListener('click', function() {
 //   location.reload();
//});
</script>
