/**
 * SSLG Dashboard JavaScript
 * Handles dashboard interactions and event management
 */

// Wait for jQuery to be available
function initSSLGDashboard() {
    if (typeof $ === 'undefined') {
        console.log('jQuery not available, retrying in 100ms...');
        setTimeout(initSSLGDashboard, 100);
        return;
    }

    // Check if we're on the dashboard page
    if (!$('.container-fluid').length || !$('h2:contains("SSLG Dashboard")').length) {
        console.log('SSLG Dashboard not found, skipping initialization...');
        return;
    }

    console.log('jQuery available and SSLG Dashboard found, initializing...');

    $(document).ready(function () {
        // Handle view event button click
        $(document).on('click', '.view-event-btn', function() {
            const eventId = $(this).data('event-id');
            console.log('View event clicked for ID:', eventId);
            
            if (!eventId) {
                alert('Error: No event ID found');
                return;
            }

            // Show loading state
            $('#viewEventModalBody').html(`
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading event details...</p>
                </div>
            `);

            // Load event details via AJAX
            $.get('/eam_system_v0.1.1/includes/sslg/modals/view_event_details.php', { id: eventId })
                .done(function(response) {
                    $('#viewEventModalBody').html(response);
                })
                .fail(function(xhr, status, error) {
                    console.error('Error loading event details:', error);
                    $('#viewEventModalBody').html(`
                        <div class="alert alert-danger">
                            <i class="bx bx-error-circle mr-2"></i>
                            <strong>Error:</strong> Failed to load event details. Please try again.
                        </div>
                    `);
                });
        });

        // Handle edit event button click
        $(document).on('click', '.edit-event-btn', function() {
            const eventId = $(this).data('event-id');
            console.log('Edit event clicked for ID:', eventId);
            
            if (!eventId) {
                alert('Error: No event ID found');
                return;
            }

            // Show loading state
            $('#editEventModalBody').html(`
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading event form...</p>
                </div>
            `);

            // Load edit form via AJAX
            $.get('/eam_system_v0.1.1/includes/sslg/modals/edit_event_form.php', { id: eventId })
                .done(function(response) {
                    $('#editEventModalBody').html(response);
                })
                .fail(function(xhr, status, error) {
                    console.error('Error loading edit form:', error);
                    $('#editEventModalBody').html(`
                        <div class="alert alert-danger">
                            <i class="bx bx-error-circle mr-2"></i>
                            <strong>Error:</strong> Failed to load edit form. Please try again.
                        </div>
                    `);
                });
        });

        // Handle modal close - clear content
        $('#viewEventModal').on('hidden.bs.modal', function () {
            $('#viewEventModalBody').html(`
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading event details...</p>
                </div>
            `);
        });

        $('#editEventModal').on('hidden.bs.modal', function () {
            $('#editEventModalBody').html(`
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading event form...</p>
                </div>
            `);
        });

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

        // Auto-refresh statistics every 5 minutes
        setInterval(function() {
            refreshStatistics();
        }, 300000); // 5 minutes

        // Add loading animation to statistics cards
        animateStatistics();
    });
}

/**
 * Refresh statistics without page reload
 */
function refreshStatistics() {
    console.log('Refreshing dashboard statistics...');
    
    // You can implement AJAX call here to refresh statistics
    // For now, we'll just log that it would refresh
    // This could be expanded to fetch updated statistics from the server
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
    $('.statistics-card').each(function() {
        const $card = $(this);
        const statType = $card.data('stat-type');
        const $number = $card.find('.stat-number');
        
        if (stats[statType] !== undefined) {
            $number.text(formatNumber(stats[statType]));
        }
    });
}

// Initialize the function when the script loads
initSSLGDashboard();
