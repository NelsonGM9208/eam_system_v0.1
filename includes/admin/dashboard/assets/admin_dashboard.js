/**
 * Admin Dashboard JavaScript
 * Handles dashboard interactions and real-time updates
 */

// Wait for jQuery to be available
function initAdminDashboard() {
    if (typeof $ === 'undefined') {
        console.log('jQuery not available, retrying in 100ms...');
        setTimeout(initAdminDashboard, 100);
        return;
    }

    // Check if we're on the admin dashboard page
    if (!$('.container-fluid').length || !$('h2:contains("Admin Dashboard")').length) {
        console.log('Admin Dashboard not found, skipping initialization...');
        return;
    }

    console.log('jQuery available and Admin Dashboard found, initializing...');

    $(document).ready(function () {
        // Add subtle hover effects to cards
        $('.card').hover(
            function() {
                $(this).addClass('shadow').removeClass('shadow-sm');
            },
            function() {
                $(this).addClass('shadow-sm').removeClass('shadow');
            }
        );

        // Add subtle click effects to quick action buttons
        $('.btn-block').on('click', function() {
            $(this).addClass('btn-pressed');
            setTimeout(() => {
                $(this).removeClass('btn-pressed');
            }, 100);
        });

        // Auto-refresh statistics every 30 seconds for testing
        setInterval(function() {
            refreshDashboardStatistics();
        }, 30000); // 30 seconds

        // Add loading animation to statistics cards
        animateStatistics();

        // Initialize real-time updates
        initializeRealTimeUpdates();

        // Add click handlers for pending items
        initializePendingItemHandlers();
    });
}

/**
 * Refresh dashboard statistics without page reload
 */
function refreshDashboardStatistics() {
    console.log('Refreshing admin dashboard statistics...');
    
    if (typeof $ !== 'undefined') {
        $.ajax({
            url: '/eam_system_v0.1.1/includes/admin/dashboard/ajax/refresh_statistics.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log('Statistics refresh response:', data);
                if (data && data.success) {
                    updateStatisticsDisplay(data.statistics);
                    updatePendingItemsDisplay(data.pending_items);
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to refresh statistics:', error);
            }
        });
    }
}

/**
 * Animate statistics cards on load
 */
function animateStatistics() {
    $('.card.border-left-primary, .card.border-left-warning, .card.border-left-success, .card.border-left-info').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(20px)'
        }).delay(index * 100).animate({
            'opacity': '1'
        }, 500).css('transform', 'translateY(0)');
    });
}

/**
 * Initialize real-time updates for dashboard
 */
function initializeRealTimeUpdates() {
    // Update time display every minute
    setInterval(function() {
        updateTimeDisplay();
    }, 60000);
    
    // Check for new activities every 2 minutes
    setInterval(function() {
        checkForNewActivities();
    }, 120000);
}

/**
 * Update time display
 */
function updateTimeDisplay() {
    const now = new Date();
    const timeString = now.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    
    $('.current-time').text(timeString);
}

/**
 * Check for new activities
 */
function checkForNewActivities() {
    // This could be expanded to make an AJAX call to check for new activities
    // and update the recent activities section dynamically
    console.log('Checking for new activities...');
}

/**
 * Initialize pending item handlers
 */
function initializePendingItemHandlers() {
    // Add click handlers for pending user items
    $(document).on('click', '.pending-user-item', function() {
        const userId = $(this).data('user-id');
        console.log('Pending user clicked:', userId);
        // Could open a modal or navigate to user details
    });
    
    // Add click handlers for pending event items
    $(document).on('click', '.pending-event-item', function() {
        const eventId = $(this).data('event-id');
        console.log('Pending event clicked:', eventId);
        // Could open a modal or navigate to event details
    });
}

/**
 * Show notification toast
 */
function showNotification(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="close text-white mr-2 m-auto" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    `;
    
    // Add toast container if it doesn't exist
    if (!$('#toast-container').length) {
        $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
    }
    
    const $toast = $(toastHtml);
    $('#toast-container').append($toast);
    
    // Initialize and show toast
    const toast = new bootstrap.Toast($toast[0]);
    toast.show();
    
    // Remove toast element after it's hidden
    $toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

/**
 * Format numbers with commas
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Update statistics display
 */
function updateStatisticsDisplay(stats) {
    console.log('Updating statistics display with:', stats);
    
    // Update user statistics
    if ($('.card:contains("Total Users")').length) {
        $('.card:contains("Total Users") .h5').text(stats.total_users || 0);
        $('.card:contains("Total Users") .text-success').text((stats.active_users || 0) + ' active');
        $('.card:contains("Total Users") .text-warning').text((stats.pending_users || 0) + ' pending');
    }
    
    // Update event statistics
    if ($('.card:contains("Total Events")').length) {
        $('.card:contains("Total Events") .h5').text(stats.total_events || 0);
        $('.card:contains("Total Events") .text-success').text((stats.approved_events || 0) + ' approved');
        $('.card:contains("Total Events") .text-warning').text((stats.pending_events || 0) + ' pending');
    }
    
    // Update class statistics
    if ($('.card:contains("Classes")').length) {
        $('.card:contains("Classes") .h5').text(stats.total_classes || 0);
    }
    
    // Update attendance statistics
    if ($('.card:contains("Attendance")').length) {
        $('.card:contains("Attendance") .h5').text(stats.total_attendance || 0);
    }
    
    // Update event status breakdown
    if ($('.card:contains("Event Status")').length) {
        $('.card:contains("Upcoming")').text('Upcoming: ' + (stats.upcoming_events || 0));
        $('.card:contains("Ongoing")').text('Ongoing: ' + (stats.ongoing_events || 0));
        $('.card:contains("Finished")').text('Finished: ' + (stats.finished_events || 0));
    }
    
    // Update today's activity
    if ($('.card:contains("Today\'s Activity")').length) {
        $('.card:contains("New Users")').text('New Users: ' + (stats.today_registrations || 0));
        $('.card:contains("New Events")').text('New Events: ' + (stats.today_events || 0));
    }
    
    // Update system health
    if ($('.card:contains("System Health")').length) {
        $('.card:contains("Total Logs")').text('Total Logs: ' + (stats.total_logs || 0));
        $('.card:contains("Today\'s Logs")').text('Today\'s Logs: ' + (stats.today_logs || 0));
    }
}

/**
 * Update pending items display
 */
function updatePendingItemsDisplay(pendingItems) {
    console.log('Updating pending items display with:', pendingItems);
    
    // Update pending users count
    const pendingUsersCount = pendingItems.pending_users ? pendingItems.pending_users.length : 0;
    $('.card:contains("Pending User Approvals") .badge').text(pendingUsersCount + ' pending');
    
    // Update pending events count
    const pendingEventsCount = pendingItems.pending_events ? pendingItems.pending_events.length : 0;
    $('.card:contains("Pending Event Approvals") .badge').text(pendingEventsCount + ' pending');
    
    // Update pending users content
    if (pendingUsersCount === 0) {
        $('.card:contains("Pending User Approvals") .card-body').html(`
            <div class="text-center">
                <i class="bx bx-check-circle text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-3">No Pending Users</h5>
                <p class="text-muted">All user registrations have been processed.</p>
            </div>
        `);
    } else {
        // Show pending users list
        let usersList = '';
        pendingItems.pending_users.forEach(user => {
            usersList += `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>${user.firstname} ${user.lastname}</strong>
                        <br><small class="text-muted">${user.email}</small>
                    </div>
                    <small class="text-muted">${new Date(user.created_at).toLocaleDateString()}</small>
                </div>
            `;
        });
        $('.card:contains("Pending User Approvals") .card-body').html(usersList);
    }
    
    // Update pending events content
    if (pendingEventsCount === 0) {
        $('.card:contains("Pending Event Approvals") .card-body').html(`
            <div class="text-center">
                <i class="bx bx-check-circle text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-3">No Pending Events</h5>
                <p class="text-muted">All event submissions have been processed.</p>
            </div>
        `);
    } else {
        // Show pending events list
        let eventsList = '';
        pendingItems.pending_events.forEach(event => {
            eventsList += `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>${event.title}</strong>
                        <br><small class="text-muted">by ${event.creator_name}</small>
                    </div>
                    <small class="text-muted">${new Date(event.created_at).toLocaleDateString()}</small>
                </div>
            `;
        });
        $('.card:contains("Pending Event Approvals") .card-body').html(eventsList);
    }
}

/**
 * Handle bulk actions
 */
function handleBulkAction(action, items) {
    if (!items || items.length === 0) {
        showNotification('No items selected for bulk action.', 'warning');
        return;
    }
    
    const confirmMessage = `Are you sure you want to ${action} ${items.length} item(s)?`;
    if (confirm(confirmMessage)) {
        // Implement bulk action logic here
        console.log(`Performing bulk ${action} on:`, items);
        showNotification(`Bulk ${action} completed successfully!`, 'success');
    }
}

/**
 * Initialize dashboard widgets
 */
function initializeDashboardWidgets() {
    // Initialize any dashboard-specific widgets
    console.log('Initializing dashboard widgets...');
}

/**
 * Handle responsive adjustments
 */
function handleResponsiveAdjustments() {
    const windowWidth = $(window).width();
    
    if (windowWidth < 768) {
        // Mobile adjustments
        $('.btn-block').css('min-height', '80px');
        $('.timeline-icon').css({
            'width': '35px',
            'height': '35px',
            'font-size': '1rem'
        });
    } else {
        // Desktop adjustments
        $('.btn-block').css('min-height', '120px');
        $('.timeline-icon').css({
            'width': '40px',
            'height': '40px',
            'font-size': '1.2rem'
        });
    }
}

// Handle window resize - only if jQuery is available
if (typeof $ !== 'undefined') {
    $(window).on('resize', function() {
        handleResponsiveAdjustments();
    });
}

// Initialize the function when the script loads
initAdminDashboard();
