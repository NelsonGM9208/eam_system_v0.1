<?php
/**
 * Reusable Users Table View
 * This file provides a flexible table structure that can be used for:
 * - All users (users.php)
 * - Pending users (pending_users.php)
 * - Any filtered user list
 */

// Utilities are already included by the parent file

// Ensure required variables are set
if (!isset($tableConfig)) {
    $tableConfig = [
        'title' => 'Users',
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
}

// Ensure required data variables are set
if (!isset($users)) {
    $users = [];
}
if (!isset($totalUsersCount)) {
    $totalUsersCount = 0;
}
if (!isset($limit)) {
    $limit = 10;
}
if (!isset($page)) {
    $page = 1;
}
if (!isset($offset)) {
    $offset = 0;
}
?>

<div class="card mt-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?php echo htmlspecialchars($tableConfig['title']); ?></h5>
        <div>
            <?php if ($tableConfig['showCheckboxes']): ?>
                <span class="badge badge-warning badge-pill"><?php echo $totalUsersCount; ?> Users</span>
            <?php else: ?>
                <span class="badge badge-info badge-pill"><?php echo $totalUsersCount; ?> Total</span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Search and Filter Section -->
        <?php if ($tableConfig['showCheckboxes'] || $tableConfig['bulkActions']): ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="userSearch" placeholder="<?php echo htmlspecialchars($tableConfig['searchPlaceholder']); ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bx bx-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-control" id="roleFilter">
                    <option value="">All Roles</option>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="sslg">SSLG</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="col-md-3">
                <?php if ($tableConfig['bulkActions']): ?>
                <button type="button" class="btn btn-success btn-sm" id="bulkActionBtn" disabled>
                    <i class="bx bx-check-double"></i> Approve All
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Results Summary -->
        <p class="mb-2 text-muted">
            Showing <?php echo min($offset + 1, $totalUsersCount); ?> to
            <?php echo min($offset + $limit, $totalUsersCount); ?> of <?php echo $totalUsersCount; ?> results
        </p>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="usersTable">
                <thead class="thead-light">
                    <tr>
                        <?php if ($tableConfig['showCheckboxes']): ?>
                        <th width="5%">
                            <input type="checkbox" id="selectAll" <?php echo $totalUsersCount == 0 ? 'disabled' : ''; ?>>
                        </th>
                        <?php endif; ?>
                        <th>Profile</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <?php if ($tableConfig['showStatus']): ?>
                        <th>Status</th>
                        <?php endif; ?>
                        <?php if ($tableConfig['showVerification']): ?>
                        <th>Verification</th>
                        <?php endif; ?>
                        <?php if ($tableConfig['showRegistrationDate']): ?>
                        <th>Registration Date</th>
                        <?php endif; ?>
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
                            // Use completely unique variable names to avoid any conflicts
                            $userRole = sanitizeOutput($row['role'] ?: 'N/A');
                            $userStatus = sanitizeOutput($row['status'] ?? 'Pending');
                            
                            $verification = sanitizeOutput($row['verification_status']);
                            $created_at = formatDate($row['created_at'] ?? '');
                            
                            $profilePhoto = getProfilePhoto($row['profile_photo'] ?? '', 35);

                            // Get badge classes using common utility
                            $role_badge = getBadgeClass($userRole, 'role');
                            $status_badge = getBadgeClass($userStatus, 'status');
                            $verification_badge = getBadgeClass($verification, 'verification');
                    ?>
                        <tr data-user-id="<?php echo $id; ?>">
                            <?php if ($tableConfig['showCheckboxes']): ?>
                            <td>
                                <input type="checkbox" class="user-checkbox" value="<?php echo $id; ?>">
                            </td>
                            <?php endif; ?>
                            <td><?php echo $profilePhoto; ?></td>
                            <td>
                                <div><strong><?php echo $fullName; ?></strong></div>
                                <small class="text-muted">ID: <?php echo $id; ?></small>
                            </td>
                            <td><?php echo $email; ?></td>
                            <td><span class="badge <?php echo $role_badge; ?>"><?php echo ucfirst($userRole); ?></span></td>
                            <?php if ($tableConfig['showStatus']): ?>
                            <td><span class="badge <?php echo $status_badge; ?>"><?php echo ucfirst($userStatus); ?></span></td>
                            <?php endif; ?>
                            <?php if ($tableConfig['showVerification']): ?>
                            <td><span class="badge <?php echo $verification_badge; ?>"><?php echo ucfirst($verification); ?></span></td>
                            <?php endif; ?>
                            <?php if ($tableConfig['showRegistrationDate']): ?>
                            <td><?php echo $created_at; ?></td>
                            <?php endif; ?>
                            <td>
                                <?php echo generateActionButtons($tableConfig['actions'], $id, 'user'); ?>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                        $colspan = 3 + ($tableConfig['showStatus'] ? 1 : 0) + ($tableConfig['showVerification'] ? 1 : 0) + ($tableConfig['showRegistrationDate'] ? 1 : 0) + ($tableConfig['showCheckboxes'] ? 1 : 0);
                    ?>
                        <tr>
                            <td colspan="<?php echo $colspan; ?>" class="text-center text-muted">
                                <div class="py-4">
                                    <i class="bx bx-user-x" style="font-size: 2rem; color: #6c757d;"></i>
                                    <p class="mt-2 mb-0"><?php echo htmlspecialchars($tableConfig['emptyMessage']); ?></p>
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
            'currentPage' => $page,
            'totalPages' => ceil($totalUsersCount / $limit),
            'offset' => $offset,
            'recordsPerPage' => $limit,
            'totalRecords' => $totalUsersCount
        ];
        echo generatePagination($paginationData, $tableConfig['paginationUrl']);
        ?>
    </div>
</div>
