/**
 * Student Attendance Management JavaScript
 * Handles attendance filtering, export, and interactions
 */

/**
 * Initialize attendance page when loaded via AJAX
 */
function initializeAttendancePage() {
    console.log('Attendance page content loaded, initializing...');
    
    // Initialize real-time filtering
    initializeAttendanceFiltering();
    
    // Initialize tooltips for the new content
    $('[data-toggle="tooltip"]').tooltip();
    
    console.log('Attendance page initialization complete');
}

/**
 * Initialize real-time filtering for attendance records
 */
function initializeAttendanceFiltering() {
    console.log('Initializing attendance filtering');
    
    // Check if required elements exist
    const eventFilter = $('#event_filter');
    const statusFilter = $('#status_filter');
    const dateFrom = $('#date_from');
    const dateTo = $('#date_to');
    const clearBtn = $('#clearAttendanceFilters');
    const exportBtn = $('#exportAttendance');
    
    console.log('Element check:', {
        eventFilter: eventFilter.length,
        statusFilter: statusFilter.length,
        dateFrom: dateFrom.length,
        dateTo: dateTo.length,
        clearBtn: clearBtn.length,
        exportBtn: exportBtn.length
    });
    
    if (eventFilter.length === 0) {
        console.error('event_filter element not found!');
        return;
    }
    
    let filterTimeout;
    
    // Real-time filtering function
    function filterAttendance() {
        const eventFilter = $('#event_filter').val();
        const statusFilter = $('#status_filter').val();
        const dateFrom = $('#date_from').val();
        const dateTo = $('#date_to').val();
        
        console.log('Filtering attendance:', { eventFilter, statusFilter, dateFrom, dateTo });
        
        // Show loading indicator
        $('#attendanceContainer').html('<div class="card shadow-sm"><div class="card-body text-center py-4"><i class="bx bx-loader-alt bx-spin text-primary" style="font-size: 2rem;"></i><p class="mt-2 text-muted">Loading attendance records...</p></div></div>');
        
        // Make AJAX request
        $.ajax({
            url: '/eam_system_v0.1.1/includes/student/ajax/filter_attendance.php',
            method: 'GET',
            data: {
                event: eventFilter,
                status: statusFilter,
                date_from: dateFrom,
                date_to: dateTo
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#attendanceContainer').html(response.html);
                    
                    // Update statistics if provided
                    if (response.stats) {
                        updateAttendanceStats(response.stats);
                    }
                    
                    console.log('Attendance filtered successfully. Count:', response.count);
                } else {
                    $('#attendanceContainer').html('<div class="card shadow-sm"><div class="card-body text-center py-4"><div class="alert alert-danger">Error filtering attendance: ' + response.message + '</div></div></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error filtering attendance:', error);
                $('#attendanceContainer').html('<div class="card shadow-sm"><div class="card-body text-center py-4"><div class="alert alert-danger">Error loading attendance records. Please try again.</div></div></div>');
            }
        });
    }
    
    // Remove any existing event handlers to prevent duplicates
    $('#event_filter, #status_filter, #date_from, #date_to').off('change');
    $('#clearAttendanceFilters').off('click');
    $('#exportAttendance').off('click');
    
    // Debounced filtering for dropdowns and date inputs
    $('#event_filter, #status_filter, #date_from, #date_to').on('change', function() {
        console.log('Filter changed:', $(this).attr('id'), $(this).val());
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(filterAttendance, 300);
    });
    
    // Clear filters
    $('#clearAttendanceFilters').on('click', function(e) {
        e.preventDefault();
        console.log('Clear filters clicked - button found and clickable');
        $('#event_filter').val('');
        $('#status_filter').val('');
        $('#date_from').val('');
        $('#date_to').val('');
        filterAttendance();
    });
    
    // Export functionality
    $('#exportAttendance').on('click', function(e) {
        e.preventDefault();
        console.log('Export clicked - button found and clickable');
        exportAttendanceRecords();
    });
    
    // Test if buttons are actually clickable (without triggering)
    console.log('Buttons should now be clickable');
    
    console.log('Attendance filtering initialized');
}

/**
 * Update attendance statistics
 */
function updateAttendanceStats(stats) {
    // Update total events
    $('.card-body .text-primary').first().text(stats.total_attendance);
    
    // Update present count
    $('.card-body .text-success').first().text(stats.present_count);
    
    // Update late count
    $('.card-body .text-warning').first().text(stats.late_count);
    
    // Update absent count
    $('.card-body .text-danger').first().text(stats.absent_count);
    
    // Update attendance rate
    const attendanceRate = stats.total_attendance > 0 ? 
        Math.round((stats.present_count + stats.late_count) / stats.total_attendance * 100) : 0;
    $('.h4.text-info.mb-0.font-weight-bold').text(attendanceRate + '%');
}

/**
 * Export attendance records
 */
function exportAttendanceRecords() {
    const eventFilter = $('#event_filter').val();
    const statusFilter = $('#status_filter').val();
    const dateFrom = $('#date_from').val();
    const dateTo = $('#date_to').val();
    
    console.log('Exporting attendance records...');
    
    // Show loading state
    const exportBtn = $('#exportAttendance');
    const originalText = exportBtn.html();
    exportBtn.html('<i class="bx bx-loader-alt bx-spin"></i> Exporting...');
    exportBtn.prop('disabled', true);
    
    // Create download link
    const params = new URLSearchParams({
        event: eventFilter || '',
        status: statusFilter || '',
        date_from: dateFrom || '',
        date_to: dateTo || ''
    });
    
    const downloadUrl = `/eam_system_v0.1.1/includes/student/ajax/export_attendance.php?${params.toString()}`;
    
    // Use fetch to handle the download properly
    fetch(downloadUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.blob();
        })
        .then(blob => {
            // Create download link
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `attendance_records_${new Date().toISOString().split('T')[0]}.html`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            
            // Reset button state and show success
            exportBtn.html(originalText);
            exportBtn.prop('disabled', false);
            showSuccess('Attendance records exported successfully!');
        })
        .catch(error => {
            console.error('Export error:', error);
            // Reset button state and show error
            exportBtn.html(originalText);
            exportBtn.prop('disabled', false);
            showError('Failed to export attendance records. Please try again.');
        });
}

/**
 * View attendance details
 */
function viewAttendanceDetails(attendanceId) {
    console.log('Viewing attendance details for ID:', attendanceId);
    
    // Load attendance details via AJAX
    $.ajax({
        url: '/eam_system_v0.1.1/includes/student/ajax/get_attendance_details.php',
        method: 'GET',
        data: { attendance_id: attendanceId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAttendanceDetailsModal(response.attendance);
            } else {
                showError('Failed to load attendance details: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading attendance details:', error);
            showError('Error loading attendance details. Please try again.');
        }
    });
}

/**
 * Show attendance details modal
 */
function showAttendanceDetailsModal(attendance) {
    const modalHtml = `
        <div class="modal fade" id="attendanceDetailsModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="bx bx-clipboard mr-2"></i>
                            Attendance Details
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Event Information</h6>
                                <div class="mb-3">
                                    <strong>Event:</strong> ${attendance.event_title}
                                </div>
                                <div class="mb-3">
                                    <strong>Date:</strong> ${formatDate(attendance.event_date)}
                                </div>
                                <div class="mb-3">
                                    <strong>Time:</strong> ${formatTime(attendance.start_time)} - ${formatTime(attendance.end_time)}
                                </div>
                                <div class="mb-3">
                                    <strong>Location:</strong> ${attendance.location}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Attendance Information</h6>
                                <div class="mb-3">
                                    <strong>Status:</strong> 
                                    <span class="badge ${getStatusBadgeClass(attendance.remark)}">
                                        <i class="bx ${getStatusIcon(attendance.remark)} mr-1"></i>
                                        ${attendance.remark}
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <strong>Check-in Time:</strong> 
                                    ${attendance.check_in_time ? formatDateTime(attendance.check_in_time) : 'Not checked in'}
                                </div>
                                <div class="mb-3">
                                    <strong>Check-out Time:</strong> 
                                    ${attendance.check_out_time ? formatDateTime(attendance.check_out_time) : 'Not checked out'}
                                </div>
                                ${attendance.notes ? `
                                <div class="mb-3">
                                    <strong>Notes:</strong> ${attendance.notes}
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-info" onclick="printAttendanceRecord(${attendance.attendance_id})">
                            <i class="bx bx-printer mr-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#attendanceDetailsModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#attendanceDetailsModal').modal('show');
    
    // Clean up modal when hidden
    $('#attendanceDetailsModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

/**
 * Print attendance record
 */
function printAttendanceRecord(attendanceId) {
    console.log('Printing attendance record:', attendanceId);
    
    // Open print window
    const printUrl = `/eam_system_v0.1.1/includes/student/ajax/print_attendance.php?attendance_id=${attendanceId}`;
    window.open(printUrl, '_blank', 'width=800,height=600');
}

/**
 * Get status badge class
 */
function getStatusBadgeClass(status) {
    switch (status) {
        case 'Present': return 'badge-success';
        case 'Late': return 'badge-warning';
        case 'Absent': return 'badge-danger';
        case 'Excused': return 'badge-info';
        default: return 'badge-secondary';
    }
}

/**
 * Get status icon
 */
function getStatusIcon(status) {
    switch (status) {
        case 'Present': return 'bx-check';
        case 'Late': return 'bx-time';
        case 'Absent': return 'bx-x';
        case 'Excused': return 'bx-info-circle';
        default: return 'bx-help-circle';
    }
}

/**
 * Utility functions
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    const time = new Date('2000-01-01T' + timeString);
    return time.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

/**
 * Show success notification
 */
function showSuccess(message) {
    const notification = $(`
        <div class="alert alert-success alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            <i class="bx bx-check-circle mr-2"></i>
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        notification.alert('close');
    }, 3000);
}

/**
 * Show error notification
 */
function showError(message) {
    const notification = $(`
        <div class="alert alert-danger alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            <i class="bx bx-error-circle mr-2"></i>
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        notification.alert('close');
    }, 5000);
}
