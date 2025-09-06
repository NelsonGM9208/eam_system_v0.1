// Prevent multiple initializations
let dashboardInitialized = false;

// Function to reset initialization state (for navigation)
function resetDashboardInit() {
    dashboardInitialized = false;
    console.log('Dashboard initialization state reset');
}

function initDashboard() {
    console.log('initDashboard called');
    
    // Check if already initialized
    if (dashboardInitialized) {
        console.log('Dashboard already initialized, skipping...');
        return;
    }
    
    // Initialize if we're on dashboard page or events page (since eventsTBL is used in both)
    const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
    if (currentPage !== 'dashboard' && currentPage !== 'events') {
        console.log('Not on dashboard or events page, skipping initialization');
        return;
    }
    
    // Check if required elements exist
    if ($('#usersTable').length === 0 && $('#eventsTable').length === 0) {
        console.error('No dashboard tables found');
        return;
    }
    
    console.log('Dashboard elements found, initializing...');
    dashboardInitialized = true;
    
    // ===== USERS TABLE FUNCTIONALITY =====
    // User table functionality is now handled by users.js to avoid conflicts
    
    // ===== EVENTS TABLE FUNCTIONALITY =====
    if ($('#eventsTable').length > 0) {
        console.log('Initializing events table functionality');
        
        // Search functionality
        $('#eventSearch').on('input', function() {
            filterEventsTable();
        });
        
        // Status filter
        $('#eventStatusFilter').on('change', function() {
            filterEventsTable();
        });
        
        // Type filter
        $('#eventTypeFilter').on('change', function() {
            filterEventsTable();
        });
        
        // Clear filters functionality
        $(document).on('click', '#clearEventFilters', function() {
            $('#eventSearch').val('');
            $('#eventStatusFilter').val('');
            $('#eventTypeFilter').val('');
            filterEventsTable();
        });
        
        // Combined filter function
        function filterEventsTable() {
            const searchTerm = $('#eventSearch').val().toLowerCase();
            const statusFilter = $('#eventStatusFilter').val().toLowerCase();
            const typeFilter = $('#eventTypeFilter').val().toLowerCase();
            
            console.log('Events filter - Search:', searchTerm, 'Status:', statusFilter, 'Type:', typeFilter);
            
            $('#eventsTable tbody tr').each(function() {
                const $row = $(this);
                const title = $row.find('td:nth-child(1)').text().toLowerCase();
                const description = $row.find('td:nth-child(2)').text().toLowerCase();
                const location = $row.find('td:nth-child(4)').text().toLowerCase();
                const type = $row.find('td:nth-child(5) .badge').text().toLowerCase();
                const status = $row.find('td:nth-child(6) .badge').text().toLowerCase();
                
                console.log('Row data - Title:', title, 'Status:', status, 'Type:', type);
                
                // Check if row matches search criteria
                const matchesSearch = !searchTerm || 
                    title.includes(searchTerm) || 
                    description.includes(searchTerm) ||
                    location.includes(searchTerm);
                
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesType = !typeFilter || type === typeFilter;
                
                console.log('Matches - Search:', matchesSearch, 'Status:', matchesStatus, 'Type:', matchesType);
                
                if (matchesSearch && matchesStatus && matchesType) {
                    $row.show();
                } else {
                    $row.hide();
                }
            });
            
            // Update results count
            updateEventsResultsCount();
        }
        
        // Update results count function
        function updateEventsResultsCount() {
            const visibleRows = $('#eventsTable tbody tr:visible').length;
            const totalRows = $('#eventsTable tbody tr').length;
            const noResultsRow = $('#no-events-results-row');
            
            // Hide/show no results row
            if (visibleRows === 0 && totalRows > 0) {
                if (noResultsRow.length === 0) {
                    $('#eventsTable tbody').append(`
                        <tr id="no-events-results-row">
                            <td colspan="8" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bx bx-search-alt" style="font-size: 2rem; color: #6c757d;"></i>
                                    <p class="mt-2 mb-0">No events found matching your criteria</p>
                                </div>
                            </td>
                        </tr>
                    `);
                }
            } else {
                noResultsRow.remove();
            }
        }
        
        // View event details
        $(document).on('click', '.view-event-btn', function() {
            console.log('View event button clicked');
            const eventId = $(this).data('event-id');
            $('#viewEventContent').load('../includes/admin/modals/view_event.php?id=' + eventId);
        });
        
        // Edit event
        $(document).on('click', '.edit-event-btn', function() {
            console.log('Edit event button clicked');
            const eventId = $(this).data('event-id');
            $('#editEventContent').load('../includes/admin/modals/edit_event.php?id=' + eventId, function() {
                // After loading, check if event type is exclusive and load classes
                setTimeout(function() {
                    const eventType = $('#editEventType').val();
                    if (eventType === 'Exclusive') {
                        loadEditClasses(eventId);
                        $('#editClassSelectionGroup').show();
                    }
                }, 100);
            });
        });
        
        // Handle event type change in edit modal
        $(document).on('change', '#editEventType', function() {
            const eventType = $(this).val();
            const classSelectionGroup = $('#editClassSelectionGroup');
            
            if (eventType === 'Exclusive') {
                const eventId = $('.edit-event-btn').data('event-id');
                loadEditClasses(eventId);
                classSelectionGroup.show();
            } else {
                classSelectionGroup.hide();
            }
        });
        
        // Delete event
        let deleteEventId = null;
        $(document).on('click', '.delete-event-btn', function() {
            console.log('Delete event button clicked');
            deleteEventId = $(this).data('event-id');
            $('#deleteEventModal').modal('show');
        });
        
        // Confirm delete event
        $(document).on('click', '#confirmDeleteEventBtn', function() {
            console.log('Confirm delete event button clicked');
            console.log('deleteEventId:', deleteEventId);
            
            if (!deleteEventId) {
                console.error('No event ID found for deletion');
                alert('Error: No event selected for deletion');
                return;
            }
            
            const $btn = $(this);
            
            // Disable button and show loading
            $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Deleting...');
            
            $.post('../includes/admin/events_crud.php', {
                action: 'delete',
                id: deleteEventId
            })
            .done(function(response) {
                if (response.includes('successfully')) {
                    alert('Event deleted successfully!');
                    $('#deleteEventModal').modal('hide');
                    // Reload the page to show updated data
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('Error: ' + response);
                }
            })
            .fail(function() {
                alert('Failed to delete event. Please try again.');
            })
            .always(function() {
                // Re-enable button
                $btn.prop('disabled', false).html('Delete Event');
                deleteEventId = null;
            });
        });
        
        // Handle event type change to show/hide class selection
        $(document).on('change', '#eventType', function() {
            const eventType = $(this).val();
            const classSelectionGroup = $('#classSelectionGroup');
            
            if (eventType === 'Exclusive') {
                // Load classes and show selection
                loadClasses();
                classSelectionGroup.show();
            } else {
                classSelectionGroup.hide();
            }
        });
        
        // Function to load classes from database
        function loadClasses() {
            $.ajax({
                url: '../includes/admin/get_classes.php',
                type: 'GET',
                success: function(response) {
                    $('#classCheckboxes').html(response);
                },
                error: function() {
                    $('#classCheckboxes').html('<div class="col-12"><p class="text-danger">Error loading classes</p></div>');
                }
            });
        }
        
        // Function to load classes for edit modal
        function loadEditClasses(eventId) {
            $.ajax({
                url: '../includes/admin/get_classes.php',
                type: 'GET',
                success: function(response) {
                    $('#editClassCheckboxes').html(response);
                    
                    // Load selected classes for this event
                    $.ajax({
                        url: '../includes/admin/get_event_classes.php',
                        type: 'GET',
                        data: { event_id: eventId },
                        success: function(selectedClasses) {
                            const classes = JSON.parse(selectedClasses);
                            classes.forEach(function(sectionId) {
                                $('#editClassCheckboxes input[value="' + sectionId + '"]').prop('checked', true);
                            });
                        }
                    });
                },
                error: function() {
                    $('#editClassCheckboxes').html('<div class="col-12"><p class="text-danger">Error loading classes</p></div>');
                }
            });
        }
        
        // Add event form submission
        $(document).on('submit', '#addEventForm', function(e) {
            e.preventDefault();
            console.log('Add event form submitted');
            
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            
            // Disable button and show loading
            $submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Adding...');
            
            // Get form data and add selected classes
            let formData = $form.serialize();
            
            // Add selected classes if event is exclusive
            const selectedClasses = [];
            $('input[name="selected_classes[]"]:checked').each(function() {
                selectedClasses.push($(this).val());
            });
            
            if (selectedClasses.length > 0) {
                formData += '&selected_classes=' + encodeURIComponent(JSON.stringify(selectedClasses));
            }
            
            $.post('../includes/admin/events_crud.php', formData)
            .done(function(response) {
                if (response.includes('successfully')) {
                    alert('Event added successfully!');
                    $('#addEventModal').modal('hide');
                    // Reload the page to show updated data
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('Error: ' + response);
                }
            })
            .fail(function() {
                alert('Failed to add event. Please try again.');
            })
            .always(function() {
                // Re-enable button
                $submitBtn.prop('disabled', false).html('Add Event');
            });
        });
    }
}

// Hook for AJAX page loading - initialize when dashboard or events page is loaded
// Use a centralized hook system to avoid conflicts
if (typeof window.onFragmentLoaded === 'function') {
    // If hook already exists, extend it
    const originalHook = window.onFragmentLoaded;
    window.onFragmentLoaded = function(page) {
        originalHook(page);
        if (page === 'dashboard' || page === 'events') {
            console.log('Dashboard or Events page loaded via AJAX, initializing...');
            // Reset initialization state and initialize
            resetDashboardInit();
            initDashboard();
        }
    };
} else {
    // If no hook exists, create it
    window.onFragmentLoaded = function(page) {
        if (page === 'dashboard' || page === 'events') {
            console.log('Dashboard or Events page loaded via AJAX, initializing...');
            // Reset initialization state and initialize
            resetDashboardInit();
            initDashboard();
        }
    };
}

// Also initialize on DOM ready if we're already on the dashboard or events page
// Note: Initialization is now handled by the global initializePageSpecificJS() function in script.js
// This prevents duplicate initialization and ensures proper loading after login redirects
