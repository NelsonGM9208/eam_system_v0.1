/**
 * Student Events Management JavaScript
 * Handles event interactions, registration, and camera access
 */

// Global variables
let currentEventId = null;
let cameraStream = null;
let qrCodeScanner = null;

/**
 * Initialize events page
 */
$(document).ready(function() {
    console.log('Student Events page loaded');
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize real-time filtering
    initializeRealTimeFiltering();
    
    // Initialize camera when modal is shown
    $('#eventRegistrationModal').on('shown.bs.modal', function() {
        console.log('Registration modal opened');
    });
    
    // Clean up camera when modal is hidden
    $('#eventRegistrationModal').on('hidden.bs.modal', function() {
        console.log('Registration modal closed');
        stopCamera();
        resetRegistrationSteps();
    });
});

/**
 * View event details
 */
function viewEventDetails(eventId) {
    console.log('Viewing event details for ID:', eventId);
    
    // Show modal
    $('#eventDetailsModal').modal('show');
    
    // Load event details via AJAX
    $.ajax({
        url: '/eam_system_v0.1.1/includes/student/ajax/get_event_details.php',
        method: 'GET',
        data: { event_id: eventId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayEventDetails(response.event);
            } else {
                showError('Failed to load event details: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading event details:', error);
            showError('Error loading event details. Please try again.');
        }
    });
}

/**
 * Display event details in modal
 */
function displayEventDetails(event) {
    const content = `
        <div class="row">
            <div class="col-md-8">
                <h5 class="mb-3">${event.title}</h5>
                <p class="text-muted mb-4">${event.event_description}</p>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-calendar text-primary mr-2"></i>
                            <div>
                                <small class="text-muted d-block">Date</small>
                                <strong>${formatDate(event.event_date)}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-time text-warning mr-2"></i>
                            <div>
                                <small class="text-muted d-block">Time</small>
                                <strong>${formatTime(event.start_time)} - ${formatTime(event.end_time)}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-map text-success mr-2"></i>
                            <div>
                                <small class="text-muted d-block">Location</small>
                                <strong>${event.location}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Event Information</h6>
                        <div class="mb-2">
                            <span class="badge ${event.event_type === 'Exclusive' ? 'badge-danger' : 'badge-primary'}">
                                ${event.event_type}
                            </span>
                        </div>
                        <div class="mb-2">
                            <span class="badge badge-info">${event.event_status}</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Created:</small><br>
                            <small>${formatDateTime(event.created_at)}</small>
                        </div>
                        ${event.registration_status === 'Registered' ? 
                            '<div class="alert alert-success mt-3"><i class="bx bx-check-circle mr-1"></i>You are registered for this event</div>' : 
                            ''
                        }
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#eventDetailsContent').html(content);
    
    // Show/hide register button
    if (event.registration_status === 'Not Registered' && event.event_status === 'Upcoming') {
        $('#registerFromDetailsBtn').show().off('click').on('click', function() {
            $('#eventDetailsModal').modal('hide');
            registerForEvent(event.event_id);
        });
    } else {
        $('#registerFromDetailsBtn').hide();
    }
}

/**
 * Register for event
 */
function registerForEvent(eventId) {
    console.log('Registering for event ID:', eventId);
    currentEventId = eventId;
    
    // Load event info for registration
    $.ajax({
        url: '/eam_system_v0.1.1/includes/student/ajax/get_event_details.php',
        method: 'GET',
        data: { event_id: eventId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Check if student is already registered
                if (response.event.registration_status === 'Registered') {
                    showAlreadyRegisteredMessage(response.event);
                } else {
                    setupRegistrationModal(response.event);
                    $('#eventRegistrationModal').modal('show');
                }
            } else {
                showError('Failed to load event information: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading event info:', error);
            showError('Error loading event information. Please try again.');
        }
    });
}

/**
 * Show friendly message for already registered events
 */
function showAlreadyRegisteredMessage(event) {
    const modalHtml = `
        <div class="modal fade" id="alreadyRegisteredModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="bx bx-check-circle mr-2"></i>
                            Registration Status
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-4">
                            <i class="bx bx-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="text-success mb-3">Already Registered!</h4>
                        <p class="text-muted mb-3">
                            You are already registered for <strong>"${event.title}"</strong>
                        </p>
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle mr-2"></i>
                            <strong>Registration Unavailable:</strong> Can only register once per event.
                        </div>
                        <div class="row text-center mt-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Event Date</small>
                                <strong>${formatDate(event.event_date)}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Event Time</small>
                                <strong>${formatTime(event.start_time)} - ${formatTime(event.end_time)}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">
                            <i class="bx bx-check mr-1"></i>
                            Got it!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#alreadyRegisteredModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#alreadyRegisteredModal').modal('show');
    
    // Clean up modal when hidden
    $('#alreadyRegisteredModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

/**
 * Setup registration modal with event info
 */
function setupRegistrationModal(event) {
    // Set event info
    $('#registrationEventTitle').text(event.title);
    $('#registrationEventDate').text(formatDate(event.event_date));
    $('#registrationEventTime').text(`${formatTime(event.start_time)} - ${formatTime(event.end_time)}`);
    $('#registrationEventLocation').text(event.location);
    
    // Update timing information
    updateQRTimingInfo(event);
    
    // Set student info (you'll need to get this from session or AJAX)
    loadStudentInfo();
    
    // Reset to step 1
    showStep(1);
}

/**
 * Update QR timing information based on current time
 */
function updateQRTimingInfo(event) {
    const now = new Date();
    const eventDate = new Date(event.event_date + ' ' + event.start_time);
    const timeDiffHours = (eventDate - now) / (1000 * 60 * 60);
    
    let timingMessage = '';
    let alertClass = 'alert-info';
    
    if (timeDiffHours > 1) {
        timingMessage = `QR codes will become valid in ${Math.ceil(timeDiffHours)} hours (1 hour before the event starts).`;
        alertClass = 'alert-warning';
    } else if (timeDiffHours <= 1 && timeDiffHours >= 0) {
        timingMessage = 'QR codes are now valid! You can scan the QR code to register for this event.';
        alertClass = 'alert-success';
    } else {
        timingMessage = 'Registration is no longer available. The event has already started or passed.';
        alertClass = 'alert-danger';
    }
    
    $('#qrTimingMessage').text(timingMessage);
    $('#qrTimingInfo').removeClass('alert-info alert-warning alert-success alert-danger').addClass(alertClass);
}

/**
 * Load student information
 */
function loadStudentInfo() {
    // Load student data via AJAX
    $.ajax({
        url: '/eam_system_v0.1.1/includes/student/ajax/get_student_info.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#studentName').val(response.student.firstname + ' ' + response.student.lastname);
                $('#studentId').val(response.student.mis_id || 'N/A');
                $('#studentLRN').val(response.student.lrn || 'N/A');
                $('#studentSection').val(response.student.section || 'N/A');
            } else {
                showError('Failed to load student information: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading student info:', error);
            showError('Error loading student information. Please try again.');
            $('#studentName').val('Error loading');
            $('#studentId').val('Error loading');
            $('#studentLRN').val('Error loading');
            $('#studentSection').val('Error loading');
        }
    });
}

/**
 * Check in for event (for already registered events)
 */
function checkInEvent(eventId) {
    console.log('Checking in for event ID:', eventId);
    
    // This would open a check-in modal with camera access
    // Similar to registration but for check-in process
    showInfo('Check-in functionality will be implemented in the next phase.');
}

/**
 * Navigation between registration steps
 */
function nextStep(stepNumber) {
    showStep(stepNumber);
    
    if (stepNumber === 2) {
        // Start camera when moving to QR scan step
        setTimeout(() => {
            startCamera();
        }, 500);
    }
}

function prevStep(stepNumber) {
    showStep(stepNumber);
}

function showStep(stepNumber) {
    // Hide all steps
    $('.step').hide();
    
    // Show current step
    $(`#step${stepNumber}`).show();
}

/**
 * Camera functions
 */
function startCamera() {
    console.log('Starting camera...');
    
    const video = document.getElementById('cameraPreview');
    const errorDiv = document.getElementById('cameraError');
    const errorMessage = document.getElementById('cameraErrorMessage');
    
    // Hide error message
    errorDiv.style.display = 'none';
    
    // Request camera access
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            facingMode: 'environment', // Use back camera if available
            width: { ideal: 1280 },
            height: { ideal: 720 }
        } 
    })
    .then(function(stream) {
        cameraStream = stream;
        video.srcObject = stream;
        video.play();
        
        // Initialize QR code scanner
        initializeQRScanner();
        
        console.log('Camera started successfully');
    })
    .catch(function(error) {
        console.error('Camera error:', error);
        
        let errorMsg = 'Camera access denied or not available.';
        if (error.name === 'NotAllowedError') {
            errorMsg = 'Camera access denied. Please allow camera access and try again.';
        } else if (error.name === 'NotFoundError') {
            errorMsg = 'No camera found on this device.';
        } else if (error.name === 'NotSupportedError') {
            errorMsg = 'Camera not supported on this device.';
        }
        
        errorMessage.textContent = errorMsg;
        errorDiv.style.display = 'block';
    });
}

function stopCamera() {
    console.log('Stopping camera...');
    
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
    
    const video = document.getElementById('cameraPreview');
    video.srcObject = null;
    
    // Stop QR scanner
    if (qrCodeScanner) {
        qrCodeScanner.stop();
        qrCodeScanner = null;
    }
}

/**
 * Initialize QR code scanner
 */
function initializeQRScanner() {
    console.log('QR Scanner initialized');
    
    const video = document.getElementById('cameraPreview');
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    
    // Set canvas dimensions to match video
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    
    // Start scanning loop
    function scanQRCode() {
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            // Draw video frame to canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Get image data
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            
            // Try to decode QR code
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            
            if (code) {
                console.log('QR Code detected:', code.data);
                processQRCode(code.data);
                return; // Stop scanning once QR code is found
            }
        }
        
        // Continue scanning if no QR code found
        if (cameraStream && !cameraStream.getVideoTracks()[0].muted) {
            requestAnimationFrame(scanQRCode);
        }
    }
    
    // Start scanning when video is ready
    video.addEventListener('loadedmetadata', () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        scanQRCode();
    });
    
    // Also start scanning immediately in case video is already loaded
    if (video.readyState >= video.HAVE_METADATA) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        scanQRCode();
    }
}

/**
 * Process QR code
 */
function processQRCode(qrData) {
    console.log('Processing QR code:', qrData);
    
    // Stop camera scanning once QR code is detected
    stopCamera();
    
    // Validate QR code format: EVENT_{EVENT_ID}_{TIMESTAMP}
    if (qrData.startsWith('EVENT_')) {
        const parts = qrData.split('_');
        
        if (parts.length >= 3) {
            const eventIdFromQR = parts[1];
            const timestamp = parts[2];
            
            // Validate event ID matches current event
            if (eventIdFromQR == currentEventId) {
                // Check if QR code is not too old (optional - could add timestamp validation)
                const qrTimestamp = parseInt(timestamp);
                const currentTimestamp = Math.floor(Date.now() / 1000);
                const maxAge = 24 * 60 * 60; // 24 hours in seconds
                
                if (currentTimestamp - qrTimestamp > maxAge) {
                    showError('QR code has expired. Please get a fresh QR code from the event location.');
                    return;
                }
                
                // Check if registration is allowed at this time
                validateRegistrationTiming(eventIdFromQR, () => {
                    // Valid QR code and timing, proceed with registration
                    completeRegistration();
                });
            } else {
                showError('Invalid QR code for this event. This QR code is for a different event.');
            }
        } else {
            showError('Invalid QR code format. Expected format: EVENT_[ID]_[TIMESTAMP]');
        }
    } else {
        showError('Invalid QR code format. QR code must start with "EVENT_".');
    }
}

/**
 * Validate registration timing - check if QR code is valid within 1 hour before event start
 */
function validateRegistrationTiming(eventId, callback) {
    // Get event details to check timing
    $.ajax({
        url: '/eam_system_v0.1.1/includes/student/ajax/get_event_details.php',
        method: 'GET',
        data: { event_id: eventId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const event = response.event;
                const now = new Date();
                const eventDate = new Date(event.event_date + ' ' + event.start_time);
                
                // Calculate time difference in hours
                const timeDiffHours = (eventDate - now) / (1000 * 60 * 60);
                
                // Check if we're within 1 hour before event start
                if (timeDiffHours <= 1 && timeDiffHours >= 0) {
                    // Valid timing - within 1 hour before event
                    callback();
                } else if (timeDiffHours > 1) {
                    // Too early - more than 1 hour before event
                    showError(`Registration is not yet available. QR codes become valid 1 hour before the event starts. Event starts in ${Math.ceil(timeDiffHours)} hours.`);
                } else {
                    // Too late - event has already started or passed
                    showError('Registration is no longer available. The event has already started or passed.');
                }
            } else {
                showError('Failed to validate event timing: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error validating event timing:', error);
            showError('Error validating event timing. Please try again.');
        }
    });
}

/**
 * Process manual QR code entry
 */
function processManualQR() {
    const manualCode = $('#manualQRCode').val().trim();
    
    if (manualCode) {
        // Stop camera if running
        stopCamera();
        processQRCode(manualCode);
    } else {
        showError('Please enter a QR code.');
    }
}

/**
 * Complete registration process
 */
function completeRegistration() {
    console.log('Completing registration for event:', currentEventId);
    
    // Stop camera
    stopCamera();
    
    // Submit registration via AJAX
    $.ajax({
        url: '/eam_system_v0.1.1/includes/student/ajax/register_for_event.php',
        method: 'POST',
        data: { 
            event_id: currentEventId,
            registration_method: 'qr_scan'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Show confirmation step
                showStep(3);
                
                // Update confirmation details
                $('#confirmEventTitle').text($('#registrationEventTitle').text());
                $('#confirmStudentName').text($('#studentName').val());
                $('#confirmRegistrationTime').text(new Date().toLocaleString());
                
                // Refresh events page after modal closes
                $('#eventRegistrationModal').on('hidden.bs.modal', function() {
                    location.reload();
                });
                
            } else {
                showError('Registration failed: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Registration error:', error);
            showError('Registration failed. Please try again.');
        }
    });
}

/**
 * Reset registration steps
 */
function resetRegistrationSteps() {
    showStep(1);
    $('#manualQRCode').val('');
    currentEventId = null;
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
 * Notification functions
 */
function showError(message) {
    showNotification(message, 'error');
}

function showInfo(message) {
    showNotification(message, 'info');
}

function showSuccess(message) {
    showNotification(message, 'success');
}

function showNotification(message, type = 'info') {
    // Remove any existing notifications
    $('.event-notification').remove();
    
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
        <div class="alert ${alertClass} alert-dismissible fade show event-notification" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
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

/**
 * Initialize real-time filtering for events
 */
function initializeRealTimeFiltering() {
    console.log('Initializing real-time filtering');
    
    let filterTimeout;
    
    // Real-time filtering function
    function filterEvents() {
        const search = $('#search').val();
        const type = $('#type').val();
        const status = $('#status').val();
        
        console.log('Filtering events:', { search, type, status });
        
        // Show loading indicator
        $('#eventsContainer').html('<div class="card shadow-sm"><div class="card-body text-center py-4"><i class="bx bx-loader-alt bx-spin text-primary" style="font-size: 2rem;"></i><p class="mt-2 text-muted">Loading events...</p></div></div>');
        
        // Make AJAX request
        $.ajax({
            url: '/eam_system_v0.1.1/includes/student/ajax/filter_events.php',
            method: 'GET',
            data: {
                search: search,
                type: type,
                status: status
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#eventsContainer').html(response.html);
                    
                    // Update the header count
                    if (response.updateHeader && response.count !== undefined) {
                        $('.h4.text-info.mb-0.font-weight-bold').text(response.count);
                    }
                    
                    console.log('Events filtered successfully. Count:', response.count);
                } else {
                    $('#eventsContainer').html('<div class="card shadow-sm"><div class="card-body text-center py-4"><div class="alert alert-danger">Error filtering events: ' + response.message + '</div></div></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error filtering events:', error);
                $('#eventsContainer').html('<div class="card shadow-sm"><div class="card-body text-center py-4"><div class="alert alert-danger">Error loading events. Please try again.</div></div></div>');
            }
        });
    }
    
    // Debounced search input
    $('#search').on('input', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(filterEvents, 300); // 300ms delay
    });
    
    // Immediate filtering for dropdowns
    $('#type, #status').on('change', function() {
        clearTimeout(filterTimeout);
        filterEvents();
    });
    
    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#search').val('');
        $('#type').val('');
        $('#status').val('Upcoming');
        filterEvents();
    });
    
    console.log('Real-time filtering initialized');
}

/**
 * Initialize events page when loaded via AJAX
 * This function should be called when the events page content is loaded
 */
function initializeEventsPage() {
    console.log('Events page content loaded, initializing...');
    
    // Initialize real-time filtering
    initializeRealTimeFiltering();
    
    // Initialize tooltips for the new content
    $('[data-toggle="tooltip"]').tooltip();
    
    console.log('Events page initialization complete');
}
