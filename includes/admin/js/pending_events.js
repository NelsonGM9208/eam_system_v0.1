/**
 * Admin Pending Events JavaScript
 * Handles search, filtering, bulk actions, and event approval/rejection
 */

// Wait for jQuery to be available
function initAdminPendingEvents() {
    if (typeof $ === 'undefined') {
        console.log('jQuery not available, retrying in 100ms...');
        setTimeout(initAdminPendingEvents, 100);
        return;
    }

    // Check if we're on the pending events page
    if (!$('#pendingEventsTable').length) {
        console.log('Pending events table not found, skipping initialization...');
        return;
    }

    console.log('jQuery available and pending events table found, initializing...');

    $(document).ready(function () {
        // Search functionality
        $('#eventSearch').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            filterEvents();
        });

        // Filter functionality
        $('#eventTypeFilter, #creatorFilter').on('change', function() {
            filterEvents();
        });

        // Clear filters
        $('#clearEventFilters').on('click', function() {
            $('#eventSearch').val('');
            $('#eventTypeFilter').val('');
            $('#creatorFilter').val('');
            filterEvents();
        });

        // Select all checkbox
        $('#selectAll').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.event-checkbox').prop('checked', isChecked);
            updateBulkButtons();
        });

        // Individual checkbox change
        $(document).on('change', '.event-checkbox', function() {
            updateBulkButtons();
            updateSelectAllState();
        });

        // Bulk approve button
        $('#bulkApproveBtn').on('click', function() {
            const selectedEvents = getSelectedEvents();
            if (selectedEvents.length > 0) {
                showBulkModal('approve', selectedEvents);
            }
        });

        // Bulk reject button
        $('#bulkRejectBtn').on('click', function() {
            const selectedEvents = getSelectedEvents();
            if (selectedEvents.length > 0) {
                showBulkModal('reject', selectedEvents);
            }
        });

        // Individual approve button
        $(document).on('click', '.approve-event-btn', function() {
            const eventId = $(this).data('event-id');
            const eventTitle = $(this).data('event-title');
            const creatorEmail = $(this).data('creator-email');
            
            showApproveModal(eventId, eventTitle, creatorEmail);
        });

        // Individual reject button
        $(document).on('click', '.reject-event-btn', function() {
            const eventId = $(this).data('event-id');
            const eventTitle = $(this).data('event-title');
            const creatorEmail = $(this).data('creator-email');
            
            showRejectModal(eventId, eventTitle, creatorEmail);
        });

        // Handle view event button click
        $(document).on('click', '.view-event-btn', function() {
            const eventId = $(this).data('event-id');
            console.log('View event clicked for ID:', eventId);
            console.log('Button element:', this);
            console.log('Data attributes:', $(this).data());
            
            if (!eventId || eventId <= 0) {
                alert('Error: Invalid event ID found (' + eventId + '). Please refresh the page and try again.');
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
            $.get('/eam_system_v0.1.1/includes/admin/modals/view_event_details.php', { id: eventId })
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

        // Confirm approve event
        $('#confirmApproveEvent').on('click', function() {
            const eventId = $(this).data('event-id');
            const notes = $('#approvalNotes').val();
            
            approveEvent(eventId, notes);
        });

        // Confirm reject event
        $('#confirmRejectEvent').on('click', function() {
            const eventId = $(this).data('event-id');
            const reason = $('#rejectionReason').val();
            
            if (!reason.trim()) {
                alert('Please provide a reason for rejection.');
                $('#rejectionReason').focus();
                return;
            }
            
            rejectEvent(eventId, reason);
        });

        // Confirm bulk action
        $('#confirmBulkAction').on('click', function() {
            const action = $(this).data('action');
            const eventIds = $(this).data('event-ids');
            const notes = $('#bulkNotes').val();
            const reason = $('#bulkReason').val();
            
            if (action === 'reject' && !reason.trim()) {
                alert('Please provide a reason for rejection.');
                $('#bulkReason').focus();
                return;
            }
            
            performBulkAction(action, eventIds, notes, reason);
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
    const typeFilter = $('#eventTypeFilter').val();
    const creatorFilter = $('#creatorFilter').val();
    
    $('#pendingEventsTable tbody tr').each(function() {
        const $row = $(this);
        const title = $row.find('td:nth-child(2)').text().toLowerCase();
        const description = $row.find('td:nth-child(3)').text().toLowerCase();
        const location = $row.find('td:nth-child(5)').text().toLowerCase();
        const eventType = $row.data('event-type');
        const creatorRole = $row.data('creator-role');
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !title.includes(searchTerm) && !description.includes(searchTerm) && !location.includes(searchTerm)) {
            showRow = false;
        }
        
        // Type filter
        if (typeFilter && eventType !== typeFilter) {
            showRow = false;
        }
        
        // Creator filter
        if (creatorFilter && creatorRole !== creatorFilter) {
            showRow = false;
        }
        
        if (showRow) {
            $row.show();
        } else {
            $row.hide();
        }
    });
    
    updateBulkButtons();
}

/**
 * Update bulk action buttons based on selected events
 */
function updateBulkButtons() {
    const selectedCount = $('.event-checkbox:checked').length;
    const $bulkApproveBtn = $('#bulkApproveBtn');
    const $bulkRejectBtn = $('#bulkRejectBtn');
    
    if (selectedCount > 0) {
        $bulkApproveBtn.prop('disabled', false).text(`Approve Events (${selectedCount})`);
        $bulkRejectBtn.prop('disabled', false).text(`Reject Events (${selectedCount})`);
    } else {
        $bulkApproveBtn.prop('disabled', true).text('Approve Events');
        $bulkRejectBtn.prop('disabled', true).text('Reject Events');
    }
}

/**
 * Update select all checkbox state
 */
function updateSelectAllState() {
    const totalCheckboxes = $('.event-checkbox').length;
    const checkedCheckboxes = $('.event-checkbox:checked').length;
    
    if (checkedCheckboxes === 0) {
        $('#selectAll').prop('indeterminate', false).prop('checked', false);
    } else if (checkedCheckboxes === totalCheckboxes) {
        $('#selectAll').prop('indeterminate', false).prop('checked', true);
    } else {
        $('#selectAll').prop('indeterminate', true);
    }
}

/**
 * Get selected event IDs
 */
function getSelectedEvents() {
    const selectedIds = [];
    $('.event-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });
    return selectedIds;
}

/**
 * Show approve modal
 */
function showApproveModal(eventId, eventTitle, creatorEmail) {
    // Get event details from the table row
    const $row = $(`tr[data-event-id="${eventId}"]`);
    const creatorName = $row.find('td:nth-child(7) div').text();
    const eventDate = $row.find('td:nth-child(4) div').text();
    const location = $row.find('td:nth-child(5)').text();
    
    // Populate modal
    $('#approveEventTitle').text(eventTitle);
    $('#approveEventCreator').text(creatorName);
    $('#approveEventDate').text(eventDate);
    $('#approveEventLocation').text(location);
    
    // Set data attributes
    $('#confirmApproveEvent').data('event-id', eventId);
    $('#approvalNotes').val('');
    
    // Show modal
    $('#approveEventModal').modal('show');
}

/**
 * Show reject modal
 */
function showRejectModal(eventId, eventTitle, creatorEmail) {
    // Get event details from the table row
    const $row = $(`tr[data-event-id="${eventId}"]`);
    const creatorName = $row.find('td:nth-child(7) div').text();
    const eventDate = $row.find('td:nth-child(4) div').text();
    const location = $row.find('td:nth-child(5)').text();
    
    // Populate modal
    $('#rejectEventTitle').text(eventTitle);
    $('#rejectEventCreator').text(creatorName);
    $('#rejectEventDate').text(eventDate);
    $('#rejectEventLocation').text(location);
    
    // Set data attributes
    $('#confirmRejectEvent').data('event-id', eventId);
    $('#rejectionReason').val('');
    
    // Show modal
    $('#rejectEventModal').modal('show');
}

/**
 * Show bulk actions modal
 */
function showBulkModal(action, eventIds) {
    const actionText = action === 'approve' ? 'Approve' : 'Reject';
    const actionIcon = action === 'approve' ? 'bx-check' : 'bx-x';
    
    // Populate modal
    $('#bulkSelectedCount').text(`${eventIds.length} events selected`);
    $('#bulkActionType').html(`<i class="bx ${actionIcon}"></i> ${actionText} Events`);
    
    // Show/hide appropriate fields
    if (action === 'approve') {
        $('#bulkNotesGroup').show();
        $('#bulkReasonGroup').hide();
    } else {
        $('#bulkNotesGroup').hide();
        $('#bulkReasonGroup').show();
    }
    
    // Set data attributes
    $('#confirmBulkAction').data('action', action).data('event-ids', eventIds);
    $('#bulkNotes').val('');
    $('#bulkReason').val('');
    
    // Show modal
    $('#bulkActionsModal').modal('show');
}

/**
 * Approve single event
 */
function approveEvent(eventId, notes) {
    const $btn = $('#confirmApproveEvent');
    const originalText = $btn.html();
    
    $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Approving...');
    
    $.post('/eam_system_v0.1.1/config/events_crud.php', {
        action: 'approve',
        event_id: eventId,
        notes: notes
    })
    .done(function(response) {
        if (response.includes('successfully')) {
            alert('Event approved successfully!');
            $('#approveEventModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + response);
            $btn.prop('disabled', false).html(originalText);
        }
    })
    .fail(function() {
        alert('Failed to approve event. Please try again.');
        $btn.prop('disabled', false).html(originalText);
    });
}

/**
 * Reject single event
 */
function rejectEvent(eventId, reason) {
    const $btn = $('#confirmRejectEvent');
    const originalText = $btn.html();
    
    $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Rejecting...');
    
    $.post('/eam_system_v0.1.1/config/events_crud.php', {
        action: 'reject',
        event_id: eventId,
        reason: reason
    })
    .done(function(response) {
        if (response.includes('successfully')) {
            alert('Event rejected successfully!');
            $('#rejectEventModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + response);
            $btn.prop('disabled', false).html(originalText);
        }
    })
    .fail(function() {
        alert('Failed to reject event. Please try again.');
        $btn.prop('disabled', false).html(originalText);
    });
}

/**
 * Perform bulk action
 */
function performBulkAction(action, eventIds, notes, reason) {
    const $btn = $('#confirmBulkAction');
    const originalText = $btn.html();
    
    $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Processing...');
    
    $.post('/eam_system_v0.1.1/config/events_crud.php', {
        action: 'bulk_' + action,
        event_ids: JSON.stringify(eventIds),
        notes: notes,
        reason: reason
    })
    .done(function(response) {
        if (response.includes('successfully')) {
            alert(`Bulk ${action} completed successfully!`);
            $('#bulkActionsModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + response);
            $btn.prop('disabled', false).html(originalText);
        }
    })
    .fail(function() {
        alert(`Failed to ${action} events. Please try again.`);
        $btn.prop('disabled', false).html(originalText);
    });
}

// Initialize the function when the script loads
initAdminPendingEvents();
