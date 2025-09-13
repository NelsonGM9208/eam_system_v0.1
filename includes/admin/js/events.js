/**
 * Admin Events Management JavaScript
 * Handles event interactions including QR code generation
 */

$(document).ready(function() {
    console.log('Admin Events JavaScript loaded');
    
    // Initialize QR code generation functionality
    initializeQRCodeGeneration();
    
    // Auto-update event statuses on page load (silently)
    autoUpdateEventStatuses();
    
    // Set up periodic auto-updates every 5 minutes (300,000 ms)
    setInterval(function() {
        // Only run if the events page is still active/visible
        if (document.visibilityState === 'visible' && !document.hidden) {
            autoUpdateEventStatuses();
        }
    }, 300000); // 5 minutes
});

/**
 * Initialize QR code generation functionality
 */
function initializeQRCodeGeneration() {
    // Handle QR code generation button clicks
    $(document).on('click', '.generate-qr-btn', function() {
        const eventId = $(this).data('event-id');
        console.log('Generating QR code for event:', eventId);
        generateEventQR(eventId);
    });
    
    // Handle download buttons
    $(document).on('click', '#downloadPNGBtn', function() {
        const eventId = $(this).data('event-id');
        downloadQRCode(eventId, 'png');
    });
    
    $(document).on('click', '#downloadPDFBtn', function() {
        const eventId = $(this).data('event-id');
        downloadQRCode(eventId, 'pdf');
    });
    
    // Handle print button
    $(document).on('click', '#printQRBtn', function() {
        printQRCode();
    });
}

/**
 * Generate QR code for an event
 */
function generateEventQR(eventId) {
    console.log('Generating QR code for event ID:', eventId);
    
    // Show modal
    $('#eventQRCodeModal').modal('show');
    
    // Show loading state
    $('#eventQRCodeContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Generating QR code...</p>
        </div>
    `);
    
    // Hide action buttons initially
    $('#downloadPNGBtn, #downloadPDFBtn, #printQRBtn').hide();
    
    // Fetch QR code data
    $.ajax({
        url: '../includes/admin/ajax/generate_event_qr.php',
        method: 'GET',
        data: { event_id: eventId },
        dataType: 'json',
        success: function(response) {
            console.log('QR generation response:', response);
            if (response.success) {
                displayQRCode(response);
                
                // Store event ID for download buttons
                $('#downloadPNGBtn, #downloadPDFBtn').data('event-id', eventId);
                
                // Show action buttons
                $('#downloadPNGBtn, #downloadPDFBtn, #printQRBtn').show();
            } else {
                console.error('QR generation failed:', response.message);
                showError('Failed to generate QR code: ' + response.message);
                $('#eventQRCodeModal').modal('hide');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error generating QR code:', {
                status: status,
                error: error,
                responseText: xhr.responseText,
                statusCode: xhr.status
            });
            showError('Error generating QR code. Please try again.');
            $('#eventQRCodeModal').modal('hide');
        }
    });
}

/**
 * Display QR code in modal
 */
function displayQRCode(data) {
    const event = data.event;
    const qrCode = data.qr_code;
    
    const content = `
        <div class="row">
            <div class="col-md-6">
                <div class="text-center">
                    <h5 class="mb-3">QR Code</h5>
                    <div class="qr-code-container mb-3">
                        <img src="${qrCode.image}" alt="Event QR Code" class="img-fluid" style="max-width: 300px;">
                    </div>
                    <div class="qr-content-info">
                        <small class="text-muted">QR Code Content:</small>
                        <div class="alert alert-light mt-2">
                            <code>${qrCode.content}</code>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="event-details">
                    <h5 class="mb-3">Event Details</h5>
                    <div class="event-info">
                        <div class="info-item mb-2">
                            <strong>Title:</strong>
                            <div>${event.title}</div>
                        </div>
                        <div class="info-item mb-2">
                            <strong>Date:</strong>
                            <div>${formatDate(event.event_date)}</div>
                        </div>
                        <div class="info-item mb-2">
                            <strong>Time:</strong>
                            <div>${formatTime(event.start_time)} - ${formatTime(event.end_time)}</div>
                        </div>
                        <div class="info-item mb-2">
                            <strong>Location:</strong>
                            <div>${event.location}</div>
                        </div>
                        <div class="info-item mb-2">
                            <strong>Type:</strong>
                            <div>
                                <span class="badge ${event.event_type === 'Exclusive' ? 'badge-danger' : 'badge-primary'}">
                                    ${event.event_type}
                                </span>
                            </div>
                        </div>
                        <div class="info-item mb-2">
                            <strong>Status:</strong>
                            <div>
                                <span class="badge badge-info">${event.event_status}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="validity-info mt-4">
                        <h6 class="text-primary">QR Code Validity</h6>
                        <div class="validity-details">
                            <div class="validity-item">
                                <i class="bx bx-time text-warning me-2"></i>
                                <strong>Valid From:</strong> ${formatDateTime(qrCode.valid_from)}
                            </div>
                            <div class="validity-item">
                                <i class="bx bx-time-five text-danger me-2"></i>
                                <strong>Valid Until:</strong> ${formatDateTime(qrCode.valid_until)}
                            </div>
                            <div class="validity-item">
                                <i class="bx bx-calendar text-info me-2"></i>
                                <strong>Generated:</strong> ${formatDateTime(qrCode.generated_at)}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong>Instructions:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Display this QR code at the event location</li>
                        <li>Students can scan it to register for the event</li>
                        <li>QR code is valid 1 hour before event start until event end</li>
                        <li>Download or print the QR code for event display</li>
                    </ul>
                </div>
            </div>
        </div>
    `;
    
    $('#eventQRCodeContent').html(content);
}

/**
 * Download QR code in specified format
 */
function downloadQRCode(eventId, format) {
    console.log(`Downloading QR code for event ${eventId} in ${format} format`);
    
    // Create download URL
    const downloadUrl = `../includes/admin/ajax/generate_event_qr.php?event_id=${eventId}&format=${format}`;
    
    // Create temporary link and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = `event_${eventId}_qr.${format}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showSuccess(`QR code downloaded as ${format.toUpperCase()}`);
}

/**
 * Print QR code
 */
function printQRCode() {
    console.log('Printing QR code');
    
    // Create print-friendly content
    const printContent = `
        <div class="qr-print-container">
            <h3>Event QR Code</h3>
            <div class="qr-code">
                ${$('.qr-code-container').html()}
            </div>
            <div class="event-details">
                <h5>Event Information</h5>
                ${$('.event-info').html()}
                <div class="validity-info mt-3">
                    <h6>QR Code Validity</h6>
                    ${$('.validity-details').html()}
                </div>
            </div>
        </div>
    `;
    
    // Open print window
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Event QR Code</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .qr-print-container { text-align: center; }
                    .qr-code img { max-width: 300px; }
                    .event-details { text-align: left; margin-top: 20px; }
                    .validity-info { margin-top: 20px; }
                    .info-item { margin: 10px 0; }
                    .validity-item { margin: 5px 0; }
                </style>
            </head>
            <body>
                ${printContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

/**
 * Utility functions
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
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
 * Auto-update event statuses
 */
function autoUpdateEventStatuses() {
    $.post('../utils/event_status_updater.php', function(response) {
        console.log('Auto-update response:', response, 'Type:', typeof response);
        
        // Response is already parsed by jQuery, no need for JSON.parse()
        if (response.updated > 0) {
            console.log(`Auto-updated ${response.updated} event statuses`);
            // Show a subtle toast notification
            showStatusUpdateNotification(response.updated);
        }
    }).fail(function(xhr, status, error) {
        console.error('Failed to auto-update event statuses:', {
            status: status,
            error: error,
            responseText: xhr.responseText
        });
    });
}

/**
 * Show subtle notification for status updates
 */
function showStatusUpdateNotification(updatedCount) {
    // Create a subtle notification
    const notification = $(`
        <div class="alert alert-info alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bx bx-info-circle"></i> 
            Auto-updated ${updatedCount} event status${updatedCount > 1 ? 'es' : ''}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(function() {
        notification.alert('close');
    }, 3000);
}

/**
 * Notification functions
 */
function showError(message) {
    showNotification(message, 'error');
}

function showSuccess(message) {
    showNotification(message, 'success');
}

function showNotification(message, type = 'info') {
    // Remove any existing notifications
    $('.admin-notification').remove();
    
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const icon = {
        'success': 'bx-check-circle',
        'error': 'bx-error-circle',
        'warning': 'bx-error',
        'info': 'bx-info-circle'
    }[type] || 'bx-info-circle';
    
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show admin-notification" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bx ${icon} mr-2"></i>
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        notification.alert('close');
    }, 5000);
}
