// Prevent multiple initializations
let pendingUsersInitialized = false;

// Test if script is loading
console.log('pending_users.js loaded successfully!');
console.log('*** VERSION: NEW CACHE-BUSTED VERSION - ' + new Date().toISOString() + ' ***');

// Test if jQuery is available
console.log('jQuery available:', typeof $ !== 'undefined');
console.log('Document ready state:', document.readyState);

// FORCE INITIALIZATION - Only if not already initialized
function forceInitializePendingUsers() {
    if (pendingUsersInitialized) {
        console.log('FORCE INITIALIZATION SKIPPED - Already initialized');
        return;
    }
    console.log('FORCE INITIALIZING PENDING USERS...');
    window.forceInit = true; // Set force flag
    pendingUsersInitialized = false; // Reset flag
    initPendingUsers();
    window.forceInit = false; // Clear force flag
}

// Function to reset initialization state (for navigation)
function resetPendingUsersInit() {
    pendingUsersInitialized = false;
    console.log('Pending users initialization state reset');
}

function initPendingUsers() {
    console.log('initPendingUsers called');
    
    // Check if already initialized (unless forced)
    if (pendingUsersInitialized && !window.forceInit) {
        console.log('Pending users already initialized, skipping...');
        return;
    }
    
    console.log('Initializing pending users...');
    
    // Only initialize if we're actually on the pending users page
    const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
    if (currentPage !== 'pending_users') {
        console.log('Not on pending users page, skipping initialization');
        return;
    }
    
    // Check if required elements exist
    if ($('#userSearch').length === 0) {
        console.error('userSearch element not found');
        return;
    }
    if ($('#roleFilter').length === 0) {
        console.error('roleFilter element not found');
        return;
    }
    if ($('#usersTable').length === 0) {
        console.error('usersTable element not found');
        return;
    }
    
    console.log('All required elements found, initializing...');
    console.log('Found elements:', {
        userSearch: $('#userSearch').length,
        roleFilter: $('#roleFilter').length,
        usersTable: $('#usersTable').length,
        approveButtons: $('#usersTable .approve-btn').length,
        rejectButtons: $('#usersTable .reject-btn').length
    });
    
    // Remove any existing event handlers to prevent duplicates
    $(document).off('click', '#usersTable button');
    $(document).off('click', '#usersTable .view-btn');
    $(document).off('click', '#usersTable .approve-btn');
    $(document).off('click', '#usersTable .reject-btn');
    
    // Test event delegation with a simple click handler
    $(document).on('click', '#usersTable button', function() {
        console.log('Any button in usersTable clicked:', this.className);
    });
    
    pendingUsersInitialized = true;
    
    // Global variables for state management
    let currentSearchTerm = '';
    let currentRoleFilter = '';
    
    // Combined search and filter function
    function filterTable() {
        const searchTerm = $('#userSearch').val().toLowerCase();
        const roleFilter = $('#roleFilter').val().toLowerCase();
        
        console.log('Filtering table with search:', searchTerm, 'role:', roleFilter);
        
        // Update global state
        currentSearchTerm = searchTerm;
        currentRoleFilter = roleFilter;
        
        // Remove any existing "no results" row
        $('#no-results-row').remove();
        
        let visibleCount = 0;
        
        $('#usersTable tbody tr').each(function() {
            const row = $(this);
            // Skip the "no results" row
            if (row.attr('id') === 'no-results-row') return;
            
            const text = row.text().toLowerCase();
            // Role column position depends on whether checkboxes are enabled
            // With checkboxes: Profile(2), Name(3), Email(4), Role(5)
            // Without checkboxes: Profile(1), Name(2), Email(3), Role(4)
            const roleColumnIndex = $('#selectAll').length > 0 ? 5 : 4;
            const rowRole = row.find(`td:nth-child(${roleColumnIndex}) span`).text().toLowerCase();
            
            const matchesSearch = searchTerm === '' || text.indexOf(searchTerm) > -1;
            const matchesRole = roleFilter === '' || rowRole === roleFilter;
            
            const isVisible = matchesSearch && matchesRole;
            row.toggle(isVisible);
            
            if (isVisible) visibleCount++;
        });
        
        // Show "no results" message if needed
        if (visibleCount === 0 && $('#usersTable tbody tr').length > 0) {
            let message = 'No pending users found.';
            if (searchTerm || roleFilter) {
                message = 'No pending users match your search criteria.';
                if (searchTerm) message += ` Search: "${searchTerm}"`;
                if (roleFilter) message += ` Role: "${roleFilter}"`;
            }
            
            $('#usersTable tbody').append(`
                <tr id="no-results-row">
                    <td colspan="6" class="text-center text-muted">
                        <div class="py-4">
                            <i class="bx bx-search" style="font-size: 2rem; color: #6c757d;"></i>
                            <p class="mt-2 mb-0">${message}</p>
                            <small class="text-muted">Try adjusting your search or filter criteria.</small>
                        </div>
                    </td>
                </tr>
            `);
        }
        
        // Update table state
        updateTableState();
        
        // Uncheck all checkboxes when filters change
        $('.user-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateApproveAllButton();
    }
    
    // Search functionality
    console.log('Binding search event handler to #userSearch');
    $('#userSearch').on('keyup', function() {
        console.log('Search input changed:', $(this).val());
        filterTable();
    });
    
    // Role filter
    console.log('Binding role filter event handler to #roleFilter');
    $('#roleFilter').on('change', function() {
        console.log('Role filter changed:', $(this).val());
        filterTable();
    });
    
    // Select all functionality - only work with visible rows
    $('#selectAll').change(function() {
        const isChecked = $(this).is(':checked');
        // Only check visible rows (respecting filters)
        $('.user-checkbox:visible').prop('checked', isChecked);
        updateApproveAllButton();
    });
    
    // Individual checkbox change
    $(document).on('change', '.user-checkbox', function() {
        updateApproveAllButton();
        updateSelectAllCheckbox();
    });
    
    // Update approve all button state
    function updateApproveAllButton() {
        const checkedCount = $('.user-checkbox:checked').length;
        $('#bulkActionBtn').prop('disabled', checkedCount === 0);
        $('#bulkActionBtn').html(`<i class='bx bx-check-double'></i> Approve Selected (${checkedCount})`);
    }
    
    // Update select all checkbox state
    function updateSelectAllCheckbox() {
        const visibleCheckboxes = $('.user-checkbox:visible').length;
        const checkedVisibleCheckboxes = $('.user-checkbox:visible:checked').length;
        $('#selectAll').prop('checked', visibleCheckboxes > 0 && checkedVisibleCheckboxes === visibleCheckboxes);
    }
    
    // View user details - use event delegation (only for pending users page)
    console.log('Binding view button event handler');
    $(document).on('click', '#usersTable .view-btn', function() {
        console.log('View button clicked in pending users');
        
        const userId = $(this).data('id');
        const modal = $('#viewUserModal');
        
        // Show loading state
        $('#viewUserContent').html('<div class="text-center p-4"><i class="bx bx-loader-alt bx-spin" style="font-size: 2rem;"></i><p class="mt-2">Loading user details...</p></div>');
        modal.modal('show');
        
        // Load user details
        $.get('/eam_system_v0.1.1/includes/admin/load_modal.php', {modal: 'user_details', id: userId})
            .done(function(data) {
                $('#viewUserContent').html(data);
            })
            .fail(function() {
                $('#viewUserContent').html('<div class="text-center p-4 text-danger"><i class="bx bx-error" style="font-size: 2rem;"></i><p class="mt-2">Failed to load user details</p></div>');
            });
    });
    
    // Approve individual user - use event delegation (only for pending users page)
    $(document).on('click', '#usersTable .approve-btn', function() {
        console.log('Approve button clicked');
        console.log('Button element:', this);
        console.log('Button classes:', this.className);
        console.log('*** NEW CODE VERSION EXECUTING ***');
        const userId = $(this).data('id');
        const userRow = $(this).closest('tr');
        
        // More robust selectors that target the specific table row content
        // Use data attributes and more specific selectors to avoid conflicts
        const userName = userRow.find('td:nth-child(3) div strong, td:nth-child(2) div strong').first().text().trim();
        
        // SIMPLE EMAIL EXTRACTION FIX
        // Based on console output: ['', '', 'Dennis Dionsay\nID: 10', 'ddionsay@sanagustinnhs.net', 'Sslg', ...]
        // Email is in the 4th cell (index 3)
        let userEmail = userRow.find('td:nth-child(4)').text().trim();
        
        console.log('SIMPLE EMAIL EXTRACTION:');
        console.log('- Direct 4th cell result:', userEmail);
        
        // Fallback: if email extraction failed, try to find any cell with @ symbol
        if (!userEmail || !userEmail.includes('@')) {
            console.log('Email extraction failed, trying fallback...');
            userRow.find('td').each(function(index) {
                const text = $(this).text().trim();
                console.log(`Cell ${index + 1}:`, text);
                if (text.includes('@') && text.length < 100) {
                    userEmail = text;
                    console.log('Found email in cell', index + 1, ':', text);
                    return false;
                }
            });
        }
        
        // For role, look specifically for the role badge within this table row
        let userRole = '';
        userRow.find('td span.badge').each(function() {
            const badgeText = $(this).text().trim().toLowerCase();
            console.log('Found badge:', badgeText);
            // Check if this is a role badge (not status or verification)
            if (badgeText === 'student' || badgeText === 'teacher' || badgeText === 'admin' || badgeText === 'sslg') {
                userRole = $(this).text().trim();
                return false; // Break the loop
            }
        });
        
        // Fallback: if no role found, try the 5th column (role column)
        if (!userRole) {
            const hasCheckboxes = userRow.find('td:first-child input[type="checkbox"]').length > 0;
            const roleColumnIndex = hasCheckboxes ? 5 : 4; // 5th column with checkboxes, 4th without
            userRole = userRow.find(`td:nth-child(${roleColumnIndex})`).text().trim();
            console.log('Fallback role extraction from column', roleColumnIndex, ':', userRole);
        }
        
        // Debug logging - show all cells and extracted data
        console.log('All table cells in row:', userRow.find('td').map(function() { return $(this).text().trim(); }).get());
        console.log('All badges in row:', userRow.find('td span.badge').map(function() { return $(this).text().trim(); }).get());
        console.log('Email extraction methods:');
        console.log('- Direct 4th cell result:', userEmail);
        console.log('- Final result:', userEmail);
        console.log('Role extraction result:', userRole);
        console.log('Extracted user data for approval:', {
            userId: userId,
            userName: userName,
            userEmail: userEmail,
            userRole: userRole
        });
        
        // Debug: Check if email extraction worked
        if (!userEmail || userEmail.includes('ID:') || !userEmail.includes('@')) {
            console.error('Email extraction failed! userEmail:', userEmail);
            console.log('Available cells with @ symbol:');
            userRow.find('td').each(function(index) {
                const text = $(this).text().trim();
                if (text.includes('@')) {
                    console.log(`Cell ${index + 1}:`, text);
                }
            });
        }
        
        // Validate extracted data
        if (!userId || !userName) {
            console.error('Missing required user data:', { userId, userName });
            alert('Error: Unable to extract user information. Please try again.');
            return;
        }
        
        if (!userEmail || !userEmail.includes('@')) {
            console.warn('Email extraction may have failed:', userEmail);
            // Continue anyway, but show warning
        }
        
        // Store user data for the modal
        window.pendingApproval = {
            userId: userId,
            userName: userName,
            userEmail: userEmail,
            userRole: userRole,
            userRow: userRow
        };
        
        // Populate and show the approval modal
        $('#approveUserName').text(userName);
        $('#approveUserDetails').html(`
            <strong>Name:</strong> ${userName}<br>
            <strong>Email:</strong> ${userEmail}<br>
            <strong>Role:</strong> ${userRole}
        `);
        
        // Debug: Check if confirm button exists
        console.log('Confirm approve button exists:', $('#confirmApproveBtn').length);
        console.log('Confirm approve button element:', $('#confirmApproveBtn')[0]);
        console.log('All buttons in modal:', $('#approveUserModal button'));
        console.log('All elements with confirmApproveBtn ID:', $('[id="confirmApproveBtn"]'));
        
        $('#approveUserModal').modal('show');
        
        // Test: Add a temporary click handler to see if any clicks are detected
        $('#approveUserModal').off('click.test').on('click.test', function(e) {
            console.log('Modal clicked:', e.target);
        });
        
        // Direct test: Try to bind a click handler directly to the button
        $('#confirmApproveBtn').off('click.direct').on('click.direct', function() {
            console.log('DIRECT CLICK HANDLER TRIGGERED!');
        });
    });
    
    // Reject individual user - use event delegation (only for pending users page)
    $(document).on('click', '#usersTable .reject-btn', function() {
        console.log('Reject button clicked');
        console.log('Button element:', this);
        console.log('Button classes:', this.className);
        console.log('*** NEW CODE VERSION EXECUTING (REJECT) ***');
        const userId = $(this).data('id');
        const userRow = $(this).closest('tr');
        
        // More robust selectors that target the specific table row content
        // Use data attributes and more specific selectors to avoid conflicts
        const userName = userRow.find('td:nth-child(3) div strong, td:nth-child(2) div strong').first().text().trim();
        
        // SIMPLE EMAIL EXTRACTION FIX
        // Based on console output: ['', '', 'Dennis Dionsay\nID: 10', 'ddionsay@sanagustinnhs.net', 'Sslg', ...]
        // Email is in the 4th cell (index 3)
        let userEmail = userRow.find('td:nth-child(4)').text().trim();
        
        console.log('SIMPLE EMAIL EXTRACTION (REJECT):');
        console.log('- Direct 4th cell result:', userEmail);
        
        // Fallback: if email extraction failed, try to find any cell with @ symbol
        if (!userEmail || !userEmail.includes('@')) {
            console.log('Email extraction failed, trying fallback...');
            userRow.find('td').each(function(index) {
                const text = $(this).text().trim();
                console.log(`Cell ${index + 1}:`, text);
                if (text.includes('@') && text.length < 100) {
                    userEmail = text;
                    console.log('Found email in cell', index + 1, ':', text);
                    return false;
                }
            });
        }
        
        // For role, look specifically for the role badge within this table row
        let userRole = '';
        userRow.find('td span.badge').each(function() {
            const badgeText = $(this).text().trim().toLowerCase();
            console.log('Found badge (reject):', badgeText);
            // Check if this is a role badge (not status or verification)
            if (badgeText === 'student' || badgeText === 'teacher' || badgeText === 'admin' || badgeText === 'sslg') {
                userRole = $(this).text().trim();
                return false; // Break the loop
            }
        });
        
        // Fallback: if no role found, try the 5th column (role column)
        if (!userRole) {
            const hasCheckboxes = userRow.find('td:first-child input[type="checkbox"]').length > 0;
            const roleColumnIndex = hasCheckboxes ? 5 : 4; // 5th column with checkboxes, 4th without
            userRole = userRow.find(`td:nth-child(${roleColumnIndex})`).text().trim();
            console.log('Fallback role extraction from column', roleColumnIndex, ':', userRole);
        }
        
        // Debug logging - show all cells and extracted data
        console.log('All table cells in row:', userRow.find('td').map(function() { return $(this).text().trim(); }).get());
        console.log('All badges in row:', userRow.find('td span.badge').map(function() { return $(this).text().trim(); }).get());
        console.log('Email extraction methods:');
        console.log('- Direct 4th cell result:', userEmail);
        console.log('- Final result:', userEmail);
        console.log('Role extraction result:', userRole);
        console.log('Extracted user data for rejection:', {
            userId: userId,
            userName: userName,
            userEmail: userEmail,
            userRole: userRole
        });
        
        // Debug: Check if email extraction worked
        if (!userEmail || userEmail.includes('ID:') || !userEmail.includes('@')) {
            console.error('Email extraction failed! userEmail:', userEmail);
            console.log('Available cells with @ symbol:');
            userRow.find('td').each(function(index) {
                const text = $(this).text().trim();
                if (text.includes('@')) {
                    console.log(`Cell ${index + 1}:`, text);
                }
            });
        }
        
        // Validate extracted data
        if (!userId || !userName) {
            console.error('Missing required user data:', { userId, userName });
            alert('Error: Unable to extract user information. Please try again.');
            return;
        }
        
        if (!userEmail || !userEmail.includes('@')) {
            console.warn('Email extraction may have failed:', userEmail);
            // Continue anyway, but show warning
        }
        
        // Store user data for the modal
        window.pendingRejection = {
            userId: userId,
            userName: userName,
            userEmail: userEmail,
            userRole: userRole,
            userRow: userRow
        };
        
        // Populate and show the rejection modal
        $('#rejectUserName').text(userName);
        $('#rejectUserDetails').html(`
            <strong>Name:</strong> ${userName}<br>
            <strong>Email:</strong> ${userEmail}<br>
            <strong>Role:</strong> ${userRole}
        `);
        $('#rejectUserModal').modal('show');
    });
    
    // Approve all selected users
    $('#bulkActionBtn').click(function() {
        const checkedUsers = $('.user-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (checkedUsers.length === 0) {
            showNotification('Please select users to approve.', 'error');
            return;
        }
        
        $('#approveAllCount').text(checkedUsers.length);
        $('#approveAllModal').modal('show');
    });
    
    // Confirm approve all
    $('#confirmApproveAllBtn').click(function() {
        const checkedUsers = $('.user-checkbox:checked').map(function() {
            const userId = $(this).val();
            const userRow = $(this).closest('tr');
            const userName = userRow.find('td:nth-child(2) div strong').text();
            const userEmail = userRow.find('td:nth-child(3)').text();
            const userRole = userRow.find('td:nth-child(4) span').text();
            
            return {
                id: userId,
                name: userName,
                email: userEmail,
                role: userRole
            };
        }).get();
        
        // Disable button to prevent double-click
        const $btn = $(this);
        $btn.prop('disabled', true).html('Processing...');
        
        $.post('../includes/admin/users_crud.php', {
            action: 'bulk_approve',
            user_ids: checkedUsers.map(user => user.id)
        })
        .done(function(response) {
            if(response.includes('successfully')) {
                $('#approveAllModal').modal('hide');
                
                // Remove all checked rows
                $('.user-checkbox:checked').each(function() {
                    $(this).closest('tr').fadeOut(400, function() {
                        $(this).remove();
                    });
                });
                
                // Update table state
                updateTableState();
                
                // Show success message
                showNotification('Users approved successfully!', 'success');
            } else {
                showNotification('Error: ' + response, 'error');
            }
        })
        .fail(function() {
            showNotification('Failed to approve users. Please try again.', 'error');
        })
        .always(function() {
            // Re-enable button
            $btn.prop('disabled', false).html('Approve All');
        });
    });
    
    // Helper function to update table state
    function updateTableState() {
        const visibleRows = $('#usersTable tbody tr:visible').length;
        const totalRows = $('#usersTable tbody tr').length;
        
        // Update pending count (exclude "no results" row)
        const actualVisibleRows = visibleRows - ($('#no-results-row').length > 0 ? 1 : 0);
        // Only update the pending count badge, not all warning badges
        $('.card-header .badge-warning').text(actualVisibleRows + ' Pending');
        
        // Update button states
        updateApproveAllButton();
        updateSelectAllCheckbox();
        
        // Disable buttons if no visible rows
        const hasVisibleRows = actualVisibleRows > 0;
        $('#bulkActionBtn').prop('disabled', !hasVisibleRows);
        $('#selectAll').prop('disabled', !hasVisibleRows);
    }
    
    // Helper function to show notifications
    function showNotification(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 'alert-info';
        
        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `);
        
        // Insert at the top of the card body
        $('.card-body').prepend(notification);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notification.fadeOut(400, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Confirm approve single user
    $('#confirmApproveUserBtn').click(function() {
        const userData = window.pendingApproval;
        if (!userData) return;
        
        // Disable button to prevent double-click
        const $btn = $(this);
        $btn.prop('disabled', true).html('Processing...');
        
        console.log('Sending approval request with data:', {
            action: 'approve',
            id: userData.userId,
            user_name: userData.userName,
            user_email: userData.userEmail,
            user_role: userData.userRole
        });
        
        $.post('/eam_system_v0.1.1/includes/admin/approve_users.php', {
            action: 'approve',
            id: userData.userId,
            user_name: userData.userName,
            user_email: userData.userEmail,
            user_role: userData.userRole
        })
        .done(function(response) {
            console.log('Approval response:', response);
            if(response.includes('successfully')) {
                $('#approveUserModal').modal('hide');
                // Remove the row from the table
                userData.userRow.fadeOut(400, function() {
                    $(this).remove();
                    // Update counts and check if table is empty
                    updateTableState();
                });
                showNotification('User approved successfully!', 'success');
            } else {
                console.log('Approval failed with response:', response);
                showNotification('Error: ' + response, 'error');
            }
        })
        .fail(function(xhr, status, error) {
            console.log('Approval AJAX failed:', {xhr: xhr, status: status, error: error});
            showNotification('Failed to approve user. Please try again.', 'error');
        })
        .always(function() {
            // Re-enable button
            $btn.prop('disabled', false).html('Approve User');
            window.pendingApproval = null;
        });
    });
    
    // Confirm reject single user
    $('#confirmRejectUserBtn').click(function() {
        const userData = window.pendingRejection;
        if (!userData) return;
        
        // Disable button to prevent double-click
        const $btn = $(this);
        $btn.prop('disabled', true).html('Processing...');
        
        $.post('/eam_system_v0.1.1/includes/admin/users_crud.php', {
            action: 'reject',
            id: userData.userId
        })
        .done(function(response) {
            if(response.includes('successfully')) {
                $('#rejectUserModal').modal('hide');
                // Remove the row from the table
                userData.userRow.fadeOut(400, function() {
                    $(this).remove();
                    // Update counts and check if table is empty
                    updateTableState();
                });
                showNotification('User rejected successfully!', 'success');
            } else {
                showNotification('Error: ' + response, 'error');
            }
        })
        .fail(function() {
            showNotification('Failed to reject user. Please try again.', 'error');
        })
        .always(function() {
            // Re-enable button
            $btn.prop('disabled', false).html('Reject User');
            window.pendingRejection = null;
        });
    });
    
    // Initialize table state
    updateTableState();
    
    // Individual Approve Confirmation Handler
    console.log('Setting up confirmApproveBtn handler...');
    $(document).off('click', '#confirmApproveBtn').on('click', '#confirmApproveBtn', function(){
        console.log('Confirm approve button clicked');
        console.log('Button element:', this);
        console.log('Button ID:', this.id);
        console.log('window.pendingApproval:', window.pendingApproval);
        
        if (!window.pendingApproval) {
            console.error('No pending approval data found');
            alert('Error: No user data found for approval');
            return;
        }
        
        const { userId, userName, userEmail, userRole } = window.pendingApproval;
        const $btn = $(this);
        
        // Disable button and show loading
        $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Approving...');
        
        $.post('../includes/admin/users_crud.php', {
            action: 'approve',
            id: userId
        })
        .done(function(response) {
            if (response.includes('successfully')) {
                alert('User approved successfully!');
                $('#approveUserModal').modal('hide');
                // Reload the page to show updated data
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('Error: ' + response);
            }
        })
        .fail(function() {
            alert('Failed to approve user. Please try again.');
        })
        .always(function() {
            // Re-enable button
            $btn.prop('disabled', false).html('Approve User');
            window.pendingApproval = null;
        });
    });

    // Individual Reject Confirmation Handler
    console.log('Setting up confirmRejectBtn handler...');
    $(document).off('click', '#confirmRejectBtn').on('click', '#confirmRejectBtn', function(){
        console.log('Confirm reject button clicked');
        console.log('Button element:', this);
        console.log('Button ID:', this.id);
        console.log('window.pendingRejection:', window.pendingRejection);
        
        if (!window.pendingRejection) {
            console.error('No pending rejection data found');
            alert('Error: No user data found for rejection');
            return;
        }
        
        const { userId, userName, userEmail, userRole } = window.pendingRejection;
        const $btn = $(this);
        
        // Disable button and show loading
        $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Rejecting...');
        
        $.post('../includes/admin/users_crud.php', {
            action: 'reject',
            id: userId
        })
        .done(function(response) {
            if (response.includes('successfully')) {
                alert('User rejected successfully!');
                $('#rejectUserModal').modal('hide');
                // Reload the page to show updated data
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('Error: ' + response);
            }
        })
        .fail(function() {
            alert('Failed to reject user. Please try again.');
        })
        .always(function() {
            // Re-enable button
            $btn.prop('disabled', false).html('Reject User');
            window.pendingRejection = null;
        });
    });
}

// Hook for AJAX page loading - only initialize when pending_users page is loaded
// Use a centralized hook system to avoid conflicts
console.log('Setting up pending users hook...');
if (typeof window.onFragmentLoaded === 'function') {
    console.log('Extending existing onFragmentLoaded hook');
    // If hook already exists, extend it
    const originalHook = window.onFragmentLoaded;
    window.onFragmentLoaded = function(page) {
        console.log('onFragmentLoaded called with page:', page);
        originalHook(page);
        if (page === 'pending_users') {
            console.log('Pending users page loaded via AJAX, initializing...');
            // Reset initialization state and initialize
            resetPendingUsersInit();
            initPendingUsers();
        }
    };
} else {
    console.log('Creating new onFragmentLoaded hook');
    // If no hook exists, create it
    window.onFragmentLoaded = function(page) {
        console.log('onFragmentLoaded called with page:', page);
        if (page === 'pending_users') {
            console.log('Pending users page loaded via AJAX, initializing...');
            // Reset initialization state and initialize
            resetPendingUsersInit();
            initPendingUsers();
        }
    };
}

// Also initialize on DOM ready if we're already on the pending users page
// Note: Initialization is now handled by the global initializePageSpecificJS() function in script.js
// This prevents duplicate initialization and ensures proper loading after login redirects

// GLOBAL FUNCTION - Can be called from anywhere to force initialization
window.forcePendingUsersInit = function() {
    console.log('GLOBAL FORCE INITIALIZATION CALLED');
    forceInitializePendingUsers();
};

// AUTO-FORCE INITIALIZATION - Only if not already initialized
setTimeout(function() {
    const currentPage = new URLSearchParams(window.location.search).get('page');
    if (currentPage === 'pending_users' && !window.pendingUsersInitialized) {
        console.log('AUTO-FORCE INITIALIZATION after timeout');
        forceInitializePendingUsers();
    }
}, 1000);