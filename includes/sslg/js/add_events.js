/**
 * SSLG Add Events JavaScript
 * Handles form validation, modal interactions, and event creation
 */

// Wait for jQuery to be available
function initSSLGAddEvents() {
    if (typeof $ === 'undefined') {
        console.log('jQuery not available, retrying in 100ms...');
        setTimeout(initSSLGAddEvents, 100);
        return;
    }

    // Check if we're on the add_events page
    if (!$('#addEventForm').length) {
        console.log('Add events form not found, skipping initialization...');
        return;
    }

    console.log('jQuery available and on add_events page, initializing SSLG add events...');

    $(document).ready(function () {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        $('#eventDate').attr('min', today);

        // Debug: Check if elements exist
        console.log('Event type select exists:', $('#eventType').length > 0);
        console.log('Section selection group exists:', $('#sectionSelectionGroup').length > 0);
        console.log('Section checkboxes count:', $('.section-checkbox').length);

        // Show/hide section selection based on event type
        $('#eventType').on('change', function () {
            const eventType = $(this).val();
            console.log('Event type changed to:', eventType);
            console.log('Section selection group element:', $('#sectionSelectionGroup'));

            if (eventType === 'Exclusive') {
                console.log('Showing section selection group');
                $('#sectionSelectionGroup').show();
                console.log('Section selection group visibility after show:', $('#sectionSelectionGroup').is(':visible'));
            } else {
                console.log('Hiding section selection group');
                $('#sectionSelectionGroup').hide();
                // Uncheck all section checkboxes when not exclusive
                $('.section-checkbox').prop('checked', false);
            }
        });

        // Form submission
        $('#addEventForm').on('submit', function (e) {
            e.preventDefault();

            // Validate date (cannot be in the past)
            const eventDate = $('#eventDate').val();
            const today = new Date().toISOString().split('T')[0];

            if (eventDate < today) {
                alert('Cannot create events for past dates. Please select today or a future date.');
                $('#eventDate').focus();
                return;
            }

            // Validate time
            const startTime = $('#startTime').val();
            const endTime = $('#endTime').val();

            if (startTime >= endTime) {
                alert('Start time must be earlier than end time.');
                $('#endTime').focus();
                return;
            }

            // Validate exclusive events have sections selected
            const eventType = $('#eventType').val();
            if (eventType === 'Exclusive') {
                const selectedSections = $('.section-checkbox:checked').length;
                if (selectedSections === 0) {
                    alert('Please select at least one section for exclusive events.');
                    return;
                }
            }

            // Populate confirmation modal with form data
            populateConfirmationModal();
            
            // Show confirmation modal
            $('#confirmEventModal').modal('show');
        });

        // Handle confirmation modal create button
        $('#confirmCreateEvent').on('click', function() {
            let formData = $('#addEventForm').serialize();
            const eventType = $('#eventType').val();

            // Add selected classes as JSON if exclusive
            if (eventType === 'Exclusive') {
                const selectedClasses = [];
                $('.section-checkbox:checked').each(function () {
                    selectedClasses.push($(this).val());
                });
                formData += '&selected_classes=' + encodeURIComponent(JSON.stringify(selectedClasses));
            }

            // Disable the button to prevent double submission
            $(this).prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Creating...');

            $.post('/eam_system_v0.1.1/config/events_crud.php', formData)
                .done(function (response) {
                    if (response.includes('successfully')) {
                        // Show success message
                        $('#confirmEventModal').modal('hide');
                        alert('Event created successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response);
                        // Re-enable the button
                        $('#confirmCreateEvent').prop('disabled', false).html('<i class="bx bx-check me-1"></i>Create Event');
                    }
                })
                .fail(function () {
                    alert('Failed to create event. Please try again.');
                    // Re-enable the button
                    $('#confirmCreateEvent').prop('disabled', false).html('<i class="bx bx-check me-1"></i>Create Event');
                });
        });
    });
}

/**
 * Function to populate confirmation modal with form data
 */
function populateConfirmationModal() {
    const title = $('#eventTitle').val();
    const eventType = $('#eventType').val();
    const description = $('#eventDescription').val();
    const eventDate = $('#eventDate').val();
    const startTime = $('#startTime').val();
    const endTime = $('#endTime').val();
    const location = $('#eventLocation').val();
    const penalty = $('#absPenalty').val();

    // Format date
    const dateObj = new Date(eventDate);
    const formattedDate = dateObj.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });

    // Format time
    const formatTime = (time) => {
        const [hours, minutes] = time.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
    };

    // Get selected sections for exclusive events
    let selectedSections = 'None';
    if (eventType === 'Exclusive') {
        const sections = [];
        $('.section-checkbox:checked').each(function () {
            const label = $(this).next('label').text().trim();
            sections.push(label);
        });
        selectedSections = sections.length > 0 ? sections.join(', ') : 'None selected';
    }

    // Populate modal fields
    $('#confirmTitle').text(title || 'Not specified');
    $('#confirmType').html(`<span class="badge badge-${eventType === 'Exclusive' ? 'danger' : 'primary'}">${eventType}</span>`);
    $('#confirmDescription').text(description || 'No description provided');
    $('#confirmDateTime').text(`${formattedDate} from ${formatTime(startTime)} to ${formatTime(endTime)}`);
    $('#confirmLocation').text(location || 'Not specified');
    $('#confirmPenalty').text(penalty ? `₱${parseFloat(penalty).toFixed(2)}` : 'No penalty');
    $('#confirmSections').text(selectedSections);
}

// Initialize the function when the script loads
initSSLGAddEvents();
