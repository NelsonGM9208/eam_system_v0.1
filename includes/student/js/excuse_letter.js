/**
 * Excuse Letter Management JavaScript
 * Handles excuse letter submission, editing, and document management
 */

// Wait for jQuery to be available
function waitForJQuery() {
    if (typeof $ !== 'undefined') {
        $(document).ready(function() {
            initializeExcuseLetter();
        });
    } else {
        setTimeout(waitForJQuery, 100);
    }
}

waitForJQuery();

/**
 * Initialize excuse letter functionality
 */
function initializeExcuseLetter() {
    console.log('Initializing excuse letter management...');
    
    // Bind event handlers
    bindExcuseLetterEvents();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize file upload preview
    initializeFilePreview();
}

/**
 * Bind all event handlers for excuse letter management
 */
function bindExcuseLetterEvents() {
    // Submit excuse letter form
    $('#excuseLetterForm').on('submit', function(e) {
        e.preventDefault();
        submitExcuseLetter();
    });
    
    // Edit excuse form
    $('#editExcuseForm').on('submit', function(e) {
        e.preventDefault();
        updateExcuseLetter();
    });
    
    // File input change
    $('#excuseDocuments').on('change', function() {
        console.log('File input changed:', this.files);
        if (this.files && this.files.length > 0) {
            previewFiles(this.files);
        } else {
            $('#filePreview').empty();
        }
    });
    
    // Date validation
    $('#excuseStartDate, #excuseEndDate').on('change', function() {
        validateDateRange();
    });
    
    // Event selection change
    $('#excuseEvent').on('change', function() {
        updateEventDates();
    });
    
    // Modal cleanup
    $('#submitExcuseModal, #viewExcuseModal, #editExcuseModal').on('hidden.bs.modal', function() {
        resetForms();
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // Check if jQuery validation is available
    if (typeof $.validator === 'undefined') {
        console.warn('jQuery validation plugin not loaded, skipping form validation');
        return;
    }
    
    // Custom validation rules
    $.validator.addMethod("dateRange", function(value, element) {
        const startDate = $('#excuseStartDate').val();
        const endDate = $('#excuseEndDate').val();
        
        if (startDate && endDate) {
            return new Date(startDate) <= new Date(endDate);
        }
        return true;
    }, "End date must be after or equal to start date");
    
    $.validator.addMethod("fileSize", function(value, element, param) {
        if (element.files.length === 0) return true;
        
        let totalSize = 0;
        for (let i = 0; i < element.files.length; i++) {
            totalSize += element.files[i].size;
        }
        
        return totalSize <= param * 1024 * 1024; // Convert MB to bytes
    }, "Total file size must not exceed {0} MB");
    
    // Initialize validation
    $('#excuseLetterForm').validate({
        rules: {
            event_id: { required: true },
            excuse_type: { required: true },
            reason: { required: true, minlength: 20 },
            start_date: { required: true },
            end_date: { dateRange: true },
            'documents[]': { fileSize: 10 } // 10MB limit
        },
        messages: {
            event_id: "Please select an event",
            excuse_type: "Please select an excuse type",
            reason: {
                required: "Please provide a reason for your absence",
                minlength: "Reason must be at least 20 characters long"
            },
            start_date: "Please select a start date",
            end_date: "End date must be after or equal to start date"
        },
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).addClass('is-valid').removeClass('is-invalid');
        }
    });
}

/**
 * Initialize file preview functionality
 */
function initializeFilePreview() {
    // Custom file input label
    $('.custom-file-input').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Choose files...');
    });
}

/**
 * Preview uploaded files
 */
function previewFiles(files) {
    console.log('previewFiles called with:', files);
    const preview = $('#filePreview');
    preview.empty();
    
    // Check if files is valid and has length
    if (!files || !files.length || files.length === 0) {
        console.log('No files to preview');
        return;
    }
    
    console.log('Processing', files.length, 'files');
    
    // Convert FileList to Array to ensure forEach works
    const filesArray = Array.from(files);
    
    const fileTypes = {
        'image': ['jpg', 'jpeg', 'png', 'gif'],
        'video': ['mp4', 'avi', 'mov'],
        'document': ['pdf', 'doc', 'docx']
    };
    
    filesArray.forEach((file, index) => {
        const fileExtension = file.name.split('.').pop().toLowerCase();
        let fileType = 'other';
        
        for (const [type, extensions] of Object.entries(fileTypes)) {
            if (extensions.includes(fileExtension)) {
                fileType = type;
                break;
            }
        }
        
        const filePreview = $(`
            <div class="file-preview-item d-flex align-items-center p-2 border rounded mb-2">
                <div class="file-icon mr-3">
                    ${getFileIcon(fileType, fileExtension)}
                </div>
                <div class="file-info flex-grow-1">
                    <div class="file-name">${file.name}</div>
                    <div class="file-size text-muted">${formatFileSize(file.size)}</div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(${index})">
                    <i class="bx bx-x"></i>
                </button>
            </div>
        `);
        
        preview.append(filePreview);
    });
}

/**
 * Get file icon based on type
 */
function getFileIcon(type, extension) {
    const icons = {
        'image': '<i class="bx bx-image text-success" style="font-size: 1.5rem;"></i>',
        'video': '<i class="bx bx-video text-info" style="font-size: 1.5rem;"></i>',
        'document': '<i class="bx bx-file-blank text-primary" style="font-size: 1.5rem;"></i>',
        'other': '<i class="bx bx-file text-secondary" style="font-size: 1.5rem;"></i>'
    };
    
    return icons[type] || icons['other'];
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Remove file from preview
 */
function removeFile(index) {
    // This would require more complex file handling
    // For now, just remove the preview item
    $(`.file-preview-item`).eq(index).remove();
}

/**
 * Validate date range
 */
function validateDateRange() {
    const startDate = $('#excuseStartDate').val();
    const endDate = $('#excuseEndDate').val();
    
    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
        $('#excuseEndDate')[0].setCustomValidity('End date must be after or equal to start date');
    } else {
        $('#excuseEndDate')[0].setCustomValidity('');
    }
}

/**
 * Update event dates when event is selected
 */
function updateEventDates() {
    const eventId = $('#excuseEvent').val();
    if (!eventId) return;
    
    // This would typically fetch event details via AJAX
    // For now, we'll just clear the dates
    $('#excuseStartDate, #excuseEndDate').val('');
}

/**
 * Submit excuse letter
 */
function submitExcuseLetter() {
    // Check if validation is available and form is valid
    if (typeof $.validator !== 'undefined' && !$('#excuseLetterForm').valid()) {
        return;
    }
    
    const submitBtn = $('#submitExcuseBtn');
    const originalText = submitBtn.html();
    
    // Show loading state
    submitBtn.html('<i class="bx bx-loader-alt bx-spin"></i> Submitting...').prop('disabled', true);
    
    // Create form data
    const formData = new FormData($('#excuseLetterForm')[0]);
    
    // Submit via AJAX
    $.ajax({
        url: '/eam_system_v0.1.1/includes/student/ajax/submit_excuse.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showSuccess('Excuse letter submitted successfully!');
                $('#submitExcuseModal').modal('hide');
                // Reload the page to show updated data
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showError(response.message || 'Failed to submit excuse letter');
            }
        },
        error: function(xhr, status, error) {
            console.error('Submit excuse error:', error);
            showError('An error occurred while submitting the excuse letter');
        },
        complete: function() {
            // Reset button state
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
}

/**
 * View excuse details
 */
function viewExcuseDetails(excuseId) {
    $('#viewExcuseModal').modal('show');
    
    // Load excuse details via AJAX
    $.ajax({
        url: '/eam_system_v0.1.1/includes/student/ajax/get_excuse_details.php',
        type: 'GET',
        data: { excuse_id: excuseId },
        success: function(response) {
            if (response.success) {
                $('#excuseDetailsContent').html(response.html);
            } else {
                $('#excuseDetailsContent').html('<div class="alert alert-danger">Failed to load excuse details</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Get excuse details error:', error);
            $('#excuseDetailsContent').html('<div class="alert alert-danger">An error occurred while loading excuse details</div>');
        }
    });
}

/**
 * Edit excuse letter
 */
function editExcuse(excuseId) {
    $('#editExcuseModal').modal('show');
    $('#editExcuseId').val(excuseId);
    
    // Load excuse data for editing
    $.ajax({
        url: '/eam_system_v0.1.1/includes/student/ajax/get_excuse_edit.php',
        type: 'GET',
        data: { excuse_id: excuseId },
        success: function(response) {
            if (response.success) {
                $('#editExcuseContent').html(response.html);
            } else {
                $('#editExcuseContent').html('<div class="alert alert-danger">Failed to load excuse data</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Get excuse edit error:', error);
            $('#editExcuseContent').html('<div class="alert alert-danger">An error occurred while loading excuse data</div>');
        }
    });
}

/**
 * Update excuse letter
 */
function updateExcuseLetter() {
    const updateBtn = $('#updateExcuseBtn');
    const originalText = updateBtn.html();
    
    // Show loading state
    updateBtn.html('<i class="bx bx-loader-alt bx-spin"></i> Updating...').prop('disabled', true);
    
    // Create form data
    const formData = new FormData($('#editExcuseForm')[0]);
    
    // Submit via AJAX
    $.ajax({
        url: '/eam_system_v0.1.1/includes/student/ajax/update_excuse.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showSuccess('Excuse letter updated successfully!');
                $('#editExcuseModal').modal('hide');
                // Reload the page to show updated data
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showError(response.message || 'Failed to update excuse letter');
            }
        },
        error: function(xhr, status, error) {
            console.error('Update excuse error:', error);
            showError('An error occurred while updating the excuse letter');
        },
        complete: function() {
            // Reset button state
            updateBtn.html(originalText).prop('disabled', false);
        }
    });
}

/**
 * Preview document
 */
function previewDocument(documentPath, documentName) {
    $('#documentPreviewModal').modal('show');
    $('#documentPreviewModalLabel').text(documentName);
    $('#downloadDocumentBtn').attr('href', documentPath);
    
    // Load document preview
    const fileExtension = documentPath.split('.').pop().toLowerCase();
    
    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
        $('#documentPreviewContent').html(`<img src="${documentPath}" class="img-fluid" alt="${documentName}">`);
    } else if (['mp4', 'avi', 'mov'].includes(fileExtension)) {
        $('#documentPreviewContent').html(`
            <video controls class="w-100">
                <source src="${documentPath}" type="video/${fileExtension}">
                Your browser does not support the video tag.
            </video>
        `);
    } else {
        $('#documentPreviewContent').html(`
            <div class="text-center py-5">
                <i class="bx bx-file-blank" style="font-size: 4rem; color: #ccc;"></i>
                <h5 class="mt-3 text-muted">Document Preview Not Available</h5>
                <p class="text-muted">This file type cannot be previewed in the browser.</p>
                <a href="${documentPath}" class="btn btn-primary" download>
                    <i class="bx bx-download"></i> Download Document
                </a>
            </div>
        `);
    }
}

/**
 * Reset forms when modals are closed
 */
function resetForms() {
    // Reset excuse letter form
    $('#excuseLetterForm')[0].reset();
    $('#excuseLetterForm').removeClass('was-validated');
    $('#filePreview').empty();
    $('.custom-file-label').text('Choose files...');
    
    // Clear validation states
    $('.form-control').removeClass('is-valid is-invalid');
    $('.invalid-feedback').remove();
}

/**
 * Show success message
 */
function showSuccess(message) {
    // You can implement your own notification system here
    alert('Success: ' + message);
}

/**
 * Show error message
 */
function showError(message) {
    // You can implement your own notification system here
    alert('Error: ' + message);
}
