// Prevent multiple initializations
let usersInitialized = false;

console.log('users.js loaded successfully!');

// Function to reset initialization state (for navigation)
function resetUsersInit() {
    usersInitialized = false;
    console.log('Users initialization state reset');
}

function initUsers() {
    console.log('initUsers called');
    
    // Check if already initialized
    if (usersInitialized) {
        console.log('Users already initialized, skipping...');
        return;
    }
    
    // Only initialize if we're on the users page or dashboard (which includes usersTBL)
    const currentPage = new URLSearchParams(window.location.search).get('page');
    console.log('Current page detected:', currentPage);
    if (currentPage !== 'users' && currentPage !== 'dashboard') {
        console.log('Not on users or dashboard page, skipping initialization');
        return;
    }
    
    // Check if required elements exist
    if ($('#usersTable').length === 0) {
        console.error('usersTable element not found');
        return;
    }
    
    console.log('All required elements found, initializing...');
    usersInitialized = true;
    
    // Search functionality
    console.log('Binding search event handler to #userSearch');
    console.log('userSearch element exists:', $('#userSearch').length > 0);
    $(document).on('input', '#userSearch', function() {
        const searchTerm = $(this).val().toLowerCase();
        console.log('Search term:', searchTerm);
        filterTable();
    });
    
    // Role filter functionality
    console.log('Binding role filter event handler to #roleFilter');
    $(document).on('change', '#roleFilter', function() {
        filterTable();
    });
    
    // Status filter functionality
    console.log('Binding status filter event handler to #statusFilter');
    $(document).on('change', '#statusFilter', function() {
        filterTable();
    });
    
    // Clear filters functionality
    console.log('Binding clear filters event handler to #clearFilters');
    $(document).on('click', '#clearFilters', function() {
        $('#userSearch').val('');
        $('#roleFilter').val('');
        $('#statusFilter').val('');
        filterTable();
    });
    
    // Filter table function
    function filterTable() {
        console.log('filterTable called');
        const searchTerm = $('#userSearch').val().toLowerCase();
        const roleFilter = $('#roleFilter').val().toLowerCase();
        const statusFilter = $('#statusFilter').val().toLowerCase();
        
        console.log('Filters:', {searchTerm, roleFilter, statusFilter});
        console.log('Total rows to filter:', $('#usersTable tbody tr').length);
        
        $('#usersTable tbody tr').each(function() {
            const $row = $(this);
            const fullName = $row.find('td:nth-child(2)').text().toLowerCase();
            const email = $row.find('td:nth-child(3)').text().toLowerCase();
            const role = $row.find('td:nth-child(4) .badge').text().toLowerCase();
            const status = $row.find('td:nth-child(5) .badge').text().toLowerCase();
            
            // Check if row matches search criteria
            const matchesSearch = !searchTerm || 
                fullName.includes(searchTerm) || 
                email.includes(searchTerm);
            
            const matchesRole = !roleFilter || role === roleFilter;
            const matchesStatus = !statusFilter || status === statusFilter;
            
            if (matchesSearch && matchesRole && matchesStatus) {
                $row.show();
            } else {
                $row.hide();
            }
        });
        
        // Update results count
        updateResultsCount();
    }
    
    // Update results count function
    function updateResultsCount() {
        const visibleRows = $('#usersTable tbody tr:visible').length;
        const totalRows = $('#usersTable tbody tr').length;
        const noResultsRow = $('#no-results-row');
        
        // Hide/show no results row
        if (visibleRows === 0 && totalRows > 0) {
            if (noResultsRow.length === 0) {
                $('#usersTable tbody').append(`
                    <tr id="no-results-row">
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-search-alt" style="font-size: 2rem; color: #6c757d;"></i>
                                <p class="mt-2 mb-0">No users found matching your criteria</p>
                            </div>
                        </td>
                    </tr>
                `);
            }
        } else {
            noResultsRow.remove();
        }
        
        // Update the results summary
        const actualVisibleRows = visibleRows - (noResultsRow.length > 0 ? 1 : 0);
        $('.card-body p.mb-2.text-muted').text(`Showing ${actualVisibleRows} of ${totalRows} results`);
    }
    
    // View User Modal Handler - Only for users table
    console.log('Binding view button event handler');
    $(document).on('click', '#usersTable .view-btn', function() {
        console.log('View button clicked in users');
        
        const userId = $(this).data('id');
        const modal = $('#viewUserModal');
        
        // Show loading state
        $('#viewUserContent').html('<div class="text-center p-4"><i class="bx bx-loader-alt bx-spin" style="font-size: 2rem;"></i><p class="mt-2">Loading user details...</p></div>');
        modal.modal('show');
        
        // Load user details
        $.get('../includes/admin/load_modal.php', {modal: 'user_details', id: userId})
            .done(function(data) {
                $('#viewUserContent').html(data);
                // Update modal title
                $('#viewUserModal .modal-title').text('User Details');
            })
            .fail(function() {
                $('#viewUserContent').html('<div class="text-center p-4 text-danger"><i class="bx bx-error" style="font-size: 2rem;"></i><p class="mt-2">Failed to load user details</p></div>');
            });
    });

    // Edit User Modal Handler - Only for users table
    console.log('Binding edit button event handler');
    $(document).on('click', '#usersTable .edit-btn', function() {
        console.log('Edit button clicked in users');
        
        const userId = $(this).data('id');
        const modal = $('#editUserModal');
        
        // Show loading state
        $('#editUserContent').html('<div class="text-center p-4"><i class="bx bx-loader-alt bx-spin" style="font-size: 2rem;"></i><p class="mt-2">Loading edit form...</p></div>');
        modal.modal('show');
        
        // Load edit form
        $.get('../includes/admin/modals/edit_user.php', {id: userId})
            .done(function(data) {
                $('#editUserContent').html(data);
            })
            .fail(function() {
                $('#editUserContent').html('<div class="text-center p-4 text-danger"><i class="bx bx-error" style="font-size: 2rem;"></i><p class="mt-2">Failed to load edit form</p></div>');
            });
    });

    // Delete User Handler - Only for users table
    console.log('Binding delete button event handler');
    let deleteUserId = null;
    $(document).on('click', '#usersTable .delete-btn', function() {
        console.log('Delete button clicked in users');
        deleteUserId = $(this).data('id');
        $('#deleteUserModal').modal('show');
    });

    // Delete Confirmation Handler
    $(document).on('click', '#confirmDeleteUserBtn', function() {
        console.log('Confirm delete button clicked');
        console.log('deleteUserId:', deleteUserId);
        
        if (!deleteUserId) {
            console.error('No user ID found for deletion');
            alert('Error: No user selected for deletion');
            return;
        }
        
        const $btn = $(this);
        
        // Disable button and show loading
        $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Deleting...');
        
        $.post('../includes/admin/users_crud.php', {
            action: 'delete',
            id: deleteUserId
        })
        .done(function(response) {
            if (response.includes('successfully')) {
                alert('User deleted successfully!');
                $('#deleteUserModal').modal('hide');
                // Reload the page to show updated data
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('Error: ' + response);
            }
        })
        .fail(function() {
            alert('Failed to delete user. Please try again.');
        })
        .always(function() {
            // Re-enable button
            $btn.prop('disabled', false).html('Delete User');
            deleteUserId = null;
        });
    });
}

// Hook for AJAX page loading - only initialize when users page is loaded
// Use a centralized hook system to avoid conflicts
if (typeof window.onFragmentLoaded === 'function') {
    // If hook already exists, extend it
    const originalHook = window.onFragmentLoaded;
    window.onFragmentLoaded = function(page) {
        originalHook(page);
        if (page === 'users') {
            console.log('Users page loaded via AJAX, initializing...');
            // Reset initialization state and initialize
            resetUsersInit();
            initUsers();
        }
    };
} else {
    // If no hook exists, create it
    window.onFragmentLoaded = function(page) {
        if (page === 'users') {
            console.log('Users page loaded via AJAX, initializing...');
            // Reset initialization state and initialize
            resetUsersInit();
            initUsers();
        }
    };
}

// Also initialize on DOM ready if we're already on the users page or dashboard
$(document).ready(function() {
    const currentPage = new URLSearchParams(window.location.search).get('page');
    if (currentPage === 'users') {
        console.log('DOM ready - on users page, initializing...');
        initUsers();
    }
});
