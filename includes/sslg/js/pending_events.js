/**
 * SSLG Pending Events JavaScript
 * Handles view event modal and other pending events functionality
 */

// Wait for jQuery to be available
function initSSLGPendingEvents() {
    if (typeof $ === 'undefined') {
        console.log('jQuery not available, retrying in 100ms...');
        setTimeout(initSSLGPendingEvents, 100);
        return;
    }

    // Check if we're on a page with pending events table
    if (!$('.view-event-btn').length) {
        console.log('Pending events table not found, skipping initialization...');
        return;
    }

    console.log('jQuery available and pending events table found, initializing...');

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
                            <i class="bx bx-error-circle me-2"></i>
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
                    <div class="spinner-border text-warning" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading event details...</p>
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
                            <i class="bx bx-error-circle me-2"></i>
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
                    <div class="spinner-border text-warning" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading event details...</p>
                </div>
            `);
        });
    });
}

// Initialize the function when the script loads
initSSLGPendingEvents();
