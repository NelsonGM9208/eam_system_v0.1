/**
 * SSLG Events JavaScript
 * Handles search, filtering, and view event modal functionality
 */

// Wait for jQuery to be available
function initSSLGEvents() {
    if (typeof $ === 'undefined') {
        console.log('jQuery not available, retrying in 100ms...');
        setTimeout(initSSLGEvents, 100);
        return;
    }

    // Check if we're on the events page
    if (!$('#eventsTable').length) {
        console.log('Events table not found, skipping initialization...');
        return;
    }

    console.log('jQuery available and events table found, initializing...');

    $(document).ready(function () {
        // Search functionality
        $('#eventSearch').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            filterEvents();
        });

        // Filter functionality
        $('#eventStatusFilter, #eventTypeFilter').on('change', function() {
            filterEvents();
        });

        // Clear filters
        $('#clearEventFilters').on('click', function() {
            $('#eventSearch').val('');
            $('#eventStatusFilter').val('');
            $('#eventTypeFilter').val('');
            filterEvents();
        });

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
                            <i class="bx bx-error-circle me-2"></i>
                            <strong>Error:</strong> Failed to load event details. Please try again.
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
    });
}

/**
 * Filter events based on search and filter criteria
 */
function filterEvents() {
    const searchTerm = $('#eventSearch').val().toLowerCase();
    const statusFilter = $('#eventStatusFilter').val();
    const typeFilter = $('#eventTypeFilter').val();
    
    $('#eventsTable tbody tr').each(function() {
        const $row = $(this);
        const title = $row.find('td:first').text().toLowerCase();
        const description = $row.find('td:nth-child(2)').text().toLowerCase();
        const location = $row.find('td:nth-child(4)').text().toLowerCase();
        const eventType = $row.data('event-type');
        const eventStatus = $row.data('event-status');
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !title.includes(searchTerm) && !description.includes(searchTerm) && !location.includes(searchTerm)) {
            showRow = false;
        }
        
        // Status filter
        if (statusFilter && eventStatus !== statusFilter) {
            showRow = false;
        }
        
        // Type filter
        if (typeFilter && eventType !== typeFilter) {
            showRow = false;
        }
        
        if (showRow) {
            $row.show();
        } else {
            $row.hide();
        }
    });
    
    // Update visible row count
    updateVisibleRowCount();
}

/**
 * Update the count of visible rows
 */
function updateVisibleRowCount() {
    const visibleRows = $('#eventsTable tbody tr:visible').length;
    const totalRows = $('#eventsTable tbody tr').length;
    
    // You can add a counter display here if needed
    console.log(`Showing ${visibleRows} of ${totalRows} events`);
}

// Initialize the function when the script loads
initSSLGEvents();
