/**
 * Attendance Management JavaScript
 * Handles search, filtering, and modal interactions for attendance management
 */

// Wait for jQuery to be available
function waitForJQuery() {
    if (typeof $ !== 'undefined') {
        $(document).ready(function() {
            // Only initialize attendance management if we're on the attendance page
            const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
            if (currentPage === 'attendance') {
                initAttendance();
            }
        });
    } else {
        setTimeout(waitForJQuery, 100);
    }
}

waitForJQuery();

/**
 * Initialize attendance management functionality
 */
function initAttendance() {
    console.log('Initializing attendance management...');
    
    // Bind event handlers
    bindAttendanceEvents();
    
    // Initialize filters
    initializeFilters();
}

/**
 * Bind all event handlers for attendance management
 */
function bindAttendanceEvents() {
    // Search functionality
    $('#attendanceSearch').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        filterAttendanceTable();
    });
    
    // Filter functionality
    $('#gradeFilter, #eventFilter, #remarkFilter, #classFilter').on('change', function() {
        filterAttendanceTable();
    });
    
    // Clear filters
    $('#clearFilters').on('click', function() {
        clearAllFilters();
    });
    
    // Export functionality
    $('#exportAttendance').on('click', function() {
        showExportModal();
    });
    
    // Confirm export
    $('#confirmExport').on('click', function() {
        exportAttendance();
    });
    
    // View attendance modal
    $(document).on('click', '.view-attendance-btn', function() {
        const attendanceId = $(this).data('attendance-id');
        viewAttendance(attendanceId);
    });
}

/**
 * Initialize filters with current values
 */
function initializeFilters() {
    // Get current filter values from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    const grade = urlParams.get('grade');
    const event = urlParams.get('event');
    const remark = urlParams.get('remark');
    const search = urlParams.get('search');
    
    if (grade) $('#gradeFilter').val(grade);
    if (event) $('#eventFilter').val(event);
    if (remark) $('#remarkFilter').val(remark);
    if (search) $('#attendanceSearch').val(search);
    
    // Apply initial filters after a short delay to ensure DOM is ready
    setTimeout(() => {
        filterAttendanceTable();
    }, 100);
}

/**
 * Filter attendance table based on current filter values
 */
function filterAttendanceTable() {
    const searchTerm = ($('#attendanceSearch').val() || '').toLowerCase();
    const gradeFilter = $('#gradeFilter').val();
    const eventFilter = $('#eventFilter').val();
    const remarkFilter = $('#remarkFilter').val();
    const classFilter = $('#classFilter').val();
    
    let visibleRows = 0;
    let totalRows = 0;
    
    // Check if table exists and has rows
    const $tableRows = $('#attendanceTable tbody tr');
    if ($tableRows.length === 0) {
        updateResultsInfo(0, 0);
        return;
    }
    
    $tableRows.each(function() {
        const $row = $(this);
        totalRows++;
        
        // Skip empty state row
        if ($row.find('td').length === 1) {
            return;
        }
        
        const studentName = ($row.find('td:eq(0)').text() || '').toLowerCase();
        const eventTitle = ($row.find('td:eq(1)').text() || '').toLowerCase();
        const remark = ($row.find('td:eq(5) .badge').text() || '').trim();
        const gradeSection = ($row.find('td:eq(1) small').text() || '').toLowerCase();
        const classInfo = ($row.find('td:eq(7)').text() || '').toLowerCase(); // Class info is in the last column
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !studentName.includes(searchTerm) && !eventTitle.includes(searchTerm)) {
            showRow = false;
        }
        
        // Grade filter
        if (gradeFilter && !gradeSection.includes('grade ' + gradeFilter.toLowerCase())) {
            showRow = false;
        }
        
        // Event filter
        if (eventFilter) {
            const eventId = $row.find('.view-attendance-btn').data('attendance-id');
            // Note: This is a simplified check. In a real implementation, you'd need to store event_id in data attributes
            showRow = true; // For now, we'll show all rows when event filter is applied
        }
        
        // Remark filter
        if (remarkFilter && remark !== remarkFilter) {
            showRow = false;
        }
        
        // Class filter
        if (classFilter) {
            const sectionId = $row.find('.view-attendance-btn').data('section-id');
            if (sectionId && sectionId !== classFilter) {
                showRow = false;
            }
        }
        
        if (showRow) {
            $row.show();
            visibleRows++;
        } else {
            $row.hide();
        }
    });
    
    // Update results info
    updateResultsInfo(visibleRows, totalRows);
    
    // Show/hide empty state
    if (visibleRows === 0) {
        showEmptyState();
    } else {
        hideEmptyState();
    }
}

/**
 * Clear all filters
 */
function clearAllFilters() {
    $('#attendanceSearch').val('');
    $('#gradeFilter').val('');
    $('#eventFilter').val('');
    $('#remarkFilter').val('');
    $('#classFilter').val('');
    
    // Clear URL parameters
    const url = new URL(window.location);
    url.searchParams.delete('search');
    url.searchParams.delete('grade');
    url.searchParams.delete('event');
    url.searchParams.delete('remark');
    url.searchParams.delete('class');
    window.history.replaceState({}, '', url);
    
    // Reset table
    filterAttendanceTable();
}

/**
 * Update results information
 */
function updateResultsInfo(visible, total) {
    const $info = $('#resultsInfo');
    if ($info.length) {
        $info.text(`Showing ${visible} of ${total} results`);
    }
}

/**
 * Show empty state
 */
function showEmptyState() {
    if ($('#attendanceTable tbody tr').length === 0) {
        return; // Empty state already exists
    }
    
    // Hide all rows and show empty state
    $('#attendanceTable tbody tr').hide();
    
    const emptyRow = `
        <tr id="emptyState">
            <td colspan="8" class="text-center py-3">
                <i class="bx bx-search-alt" style="font-size: 2.5rem; color: #6c757d; margin-bottom: 0.75rem;"></i>
                <p class="text-muted mb-0">No attendance records match your filters.</p>
            </td>
        </tr>
    `;
    
    $('#attendanceTable tbody').append(emptyRow);
}

/**
 * Hide empty state
 */
function hideEmptyState() {
    $('#emptyState').remove();
}

/**
 * View attendance details
 */
function viewAttendance(attendanceId) {
    console.log('Loading attendance details for ID:', attendanceId);
    
    // Show loading state
    $('#viewAttendanceContent').html(`
        <div class="text-center text-muted py-4">
            <i class="bx bx-loader-alt bx-spin" style="font-size: 2rem;"></i>
            <p class="mt-2">Loading attendance details...</p>
        </div>
    `);
    
    // Load attendance details
    $.post('../config/attendance_crud.php', {
        action: 'view_attendance',
        attendance_id: attendanceId
    })
    .done(function(response) {
        try {
            const data = JSON.parse(response);
            if (data.success) {
                // Load the modal content
                $('#viewAttendanceContent').load(`/eam_system_v0.1.1/includes/admin/modals/view_attendance.php?id=${attendanceId}`, function(response, status, xhr) {
                    if (status === "error") {
                        console.error('Error loading modal:', xhr.status, xhr.statusText);
                        $('#viewAttendanceContent').html(`
                            <div class="text-center text-danger py-4">
                                <i class="bx bx-error-circle" style="font-size: 2rem;"></i>
                                <p class="mt-2">Error loading attendance details</p>
                            </div>
                        `);
                    } else {
                        console.log('Attendance modal loaded successfully');
                    }
                });
            } else {
                throw new Error(data.message || 'Failed to load attendance details');
            }
        } catch (error) {
            console.error('Error parsing response:', error);
            $('#viewAttendanceContent').html(`
                <div class="text-center text-danger py-4">
                    <i class="bx bx-error-circle" style="font-size: 2rem;"></i>
                    <p class="mt-2">Error loading attendance details</p>
                </div>
            `);
        }
    })
    .fail(function(xhr, status, error) {
        console.error('AJAX error:', error);
        $('#viewAttendanceContent').html(`
            <div class="text-center text-danger py-4">
                <i class="bx bx-error-circle" style="font-size: 2rem;"></i>
                <p class="mt-2">Error loading attendance details</p>
            </div>
        `);
    });
}

/**
 * Show export modal
 */
function showExportModal() {
    // Set default date range (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    $('#exportDateTo').val(today.toISOString().split('T')[0]);
    $('#exportDateFrom').val(thirtyDaysAgo.toISOString().split('T')[0]);
    
    $('#exportModal').modal('show');
}

/**
 * Export attendance data
 */
function exportAttendance() {
    const format = $('#exportFormat').val();
    const dateFrom = $('#exportDateFrom').val();
    const dateTo = $('#exportDateTo').val();
    const grade = $('#exportGrade').val();
    
    if (!format) {
        showAlert('error', 'Please select an export format');
        return;
    }
    
    // Show confirmation
    const confirmMessage = `Export attendance data as ${format.toUpperCase()}?`;
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Show loading state
    const $exportBtn = $('#confirmExport');
    const originalText = $exportBtn.html();
    $exportBtn.html('<i class="bx bx-loader-alt bx-spin"></i> Exporting...').prop('disabled', true);
    
    // Create form data
    const formData = new FormData();
    formData.append('action', 'export_attendance');
    formData.append('format', format);
    formData.append('date_from', dateFrom);
    formData.append('date_to', dateTo);
    formData.append('grade', grade);
    
    // Submit export request
    $.ajax({
        url: '../config/attendance_crud.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhrFields: {
            responseType: 'blob'
        },
        success: function(data, status, xhr) {
            // Get filename from response headers
            const filename = xhr.getResponseHeader('Content-Disposition');
            let downloadFilename = 'attendance_export.' + format;
            
            if (filename) {
                const matches = filename.match(/filename="(.+)"/);
                if (matches) {
                    downloadFilename = matches[1];
                }
            }
            
            // Create download link
            const blob = new Blob([data], { type: xhr.getResponseHeader('Content-Type') });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = downloadFilename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            // Close modal and show success message
            $('#exportModal').modal('hide');
            showAlert('success', 'Attendance data exported successfully!');
        },
        error: function(xhr, status, error) {
            console.error('Export error:', error);
            showAlert('error', 'Failed to export attendance data');
        },
        complete: function() {
            // Restore button state
            $exportBtn.html(originalText).prop('disabled', false);
        }
    });
}

/**
 * Show alert message
 */
function showAlert(type, message) {
    // Remove existing alerts
    $('.alert').remove();
    
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'bx-check-circle' : 'bx-error-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="bx ${icon} mr-2"></i>
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    $('.card-body').prepend(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

/**
 * Utility function to format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Utility function to format time
 */
function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}
