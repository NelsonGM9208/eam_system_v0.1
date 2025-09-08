// Classes Management JavaScript
console.log('classes.js loaded successfully!');

let currentSectionId = null;
let enrolledStudents = [];
let availableStudents = [];
let classesInitialized = false;

// Function to reset initialization state (for navigation)
function resetClassesInit() {
    classesInitialized = false;
    console.log('Classes initialization state reset');
}

// Add CSS for proper checkbox alignment (only when on classes page)
$(document).ready(function() {
    // Only add CSS if we're on the classes page
    const currentPage = new URLSearchParams(window.location.search).get('page');
    if (currentPage === 'classes') {
        // Add custom CSS for checkbox alignment and modal isolation
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                #classesTable th:first-child,
                #classesTable td:first-child {
                    width: 50px !important;
                    text-align: center !important;
                    vertical-align: middle !important;
                    padding: 8px 4px !important;
                    display: flex !important;
                    justify-content: center !important;
                    align-items: center !important;
                    position: relative !important;
                }
                
                #classesTable .form-check-input {
                    margin: 0 auto !important;
                    transform: scale(1.2) !important;
                    cursor: pointer !important;
                    position: relative !important;
                    top: 0 !important;
                    left: 0 !important;
                    float: none !important;
                    display: block !important;
                }
                
                #classesTable thead th:first-child {
                    border-right: 1px solid #dee2e6;
                    background-color: #f8f9fa;
                }
                
                #classesTable tbody td:first-child {
                    border-right: 1px solid #dee2e6;
                }
                
                #classesTable tbody tr:hover td:first-child {
                    background-color: #f8f9fa;
                }
                
                /* Modal isolation styles for classes modals */
                .dynamic-modal-container {
                    position: relative;
                    z-index: 1220 !important;
                }
                
                .dynamic-modal-container .modal {
                    z-index: 1220 !important;
                }
                
                .dynamic-modal-container .modal-backdrop {
                    z-index: 1210 !important;
                }
                
                .dynamic-modal-container .modal-dialog {
                    z-index: 1230 !important;
                }
                
                /* Ensure proper modal cleanup */
                .modal-backdrop {
                    z-index: 1040 !important;
                }
                
                .modal {
                    z-index: 1050 !important;
                }
                
                /* Fix modal backdrop issues */
                body.modal-open {
                    overflow: hidden;
                }
                
                body.modal-open .modal-backdrop {
                    position: fixed;
                    top: 0;
                    left: 0;
                    z-index: 1040;
                    width: 100vw;
                    height: 100vh;
                    background-color: #000;
                    opacity: 0.5;
                }
            `)
            .appendTo('head');
    }
});

// Apply checkbox styling function
function applyCheckboxStyling() {
    // Remove any existing inline styles first
    $('#classesTable th:first-child, #classesTable td:first-child').removeAttr('style');
    $('#classesTable .form-check-input').removeAttr('style');
    
    // Apply new styling
    $('#classesTable th:first-child, #classesTable td:first-child').css({
        'width': '50px',
        'text-align': 'center',
        'vertical-align': 'middle',
        'padding': '8px 4px',
        'position': 'relative',
        'display': 'flex',
        'justify-content': 'center',
        'align-items': 'center'
    });
    
    // Style the checkboxes
    $('#classesTable .form-check-input').css({
        'margin': '0',
        'transform': 'scale(1.2)',
        'cursor': 'pointer',
        'position': 'relative',
        'top': '0',
        'left': '0',
        'float': 'none'
    });
    
    // Force checkbox alignment
    setTimeout(function() {
        $('#classesTable .form-check-input').each(function() {
            $(this).css({
                'margin': '0 auto',
                'display': 'block'
            });
        });
    }, 100);
}

// Force checkbox alignment function
function forceCheckboxAlignment() {
    console.log('Forcing checkbox alignment...');
    
    // Remove all existing styles from checkbox cells
    $('#classesTable th:first-child, #classesTable td:first-child').each(function() {
        $(this).removeAttr('style');
    });
    
    $('#classesTable .form-check-input').each(function() {
        $(this).removeAttr('style');
    });
    
    // Apply forced alignment
    $('#classesTable th:first-child, #classesTable td:first-child').css({
        'width': '50px',
        'text-align': 'center',
        'vertical-align': 'middle',
        'padding': '8px 4px',
        'display': 'flex',
        'justify-content': 'center',
        'align-items': 'center',
        'position': 'relative'
    });
    
    $('#classesTable .form-check-input').css({
        'margin': '0 auto',
        'transform': 'scale(1.2)',
        'cursor': 'pointer',
        'position': 'relative',
        'top': '0',
        'left': '0',
        'float': 'none',
        'display': 'block'
    });
    
    console.log('Checkbox alignment forced');
}

// Initialize classes management
function initClasses() {
    console.log('Initializing classes management...');
    
    // Check if already initialized
    if (classesInitialized) {
        console.log('Classes already initialized, skipping...');
        return;
    }
    
    // Only initialize if we're on the classes page
    const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
    console.log('Current page detected:', currentPage);
    if (currentPage !== 'classes') {
        console.log('Not on classes page, skipping initialization');
        return;
    }
    
    // Check if required elements exist
    if ($('#classesTable').length === 0) {
        console.log('Classes table not found, skipping initialization');
        return;
    }
    
    // Ensure checkbox styling is applied
    applyCheckboxStyling();
    
    // Force checkbox alignment after a short delay
    setTimeout(function() {
        forceCheckboxAlignment();
    }, 200);
    
    // Search functionality
    $('#classSearch').on('input', function() {
        filterClasses();
    });
    
    // Grade filter
    $('#gradeFilter').on('change', function() {
        filterClasses();
    });
    
    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#classSearch').val('');
        $('#gradeFilter').val('');
        filterClasses();
    });
    
    // Select all checkbox
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.class-checkbox').prop('checked', isChecked);
        updateBulkActions();
    });
    
    // Individual checkboxes
    $(document).on('change', '.class-checkbox', function() {
        updateBulkActions();
    });
    
    // Bulk assign teacher
    $('#bulkAssignTeacher').on('click', function() {
        const selectedIds = getSelectedClassIds();
        console.log('Bulk assign clicked, selected IDs:', selectedIds);
        if (selectedIds.length > 0) {
            showBulkAssignTeacherModal(selectedIds);
        } else {
            showAlert('warning', 'Please select at least one class to assign a teacher.');
        }
    });
    
    // Form submissions (using event delegation)
    $(document).on('submit', '#addClassForm', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Add class form submitted via AJAX');
        createClass();
        return false;
    });
    
    // Also handle submit button clicks
    $(document).on('click', '#addClassForm button[type="submit"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Add class submit button clicked');
        createClass();
        return false;
    });
    
    // Additional handler for the Create Class button specifically
    $(document).on('click', 'button:contains("Create Class")', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Create Class button clicked (specific handler)');
        createClass();
        return false;
    });
    
    // Debug: Check if form exists after initialization
    setTimeout(function() {
        if ($('#addClassForm').length > 0) {
            console.log('Add class form found in DOM');
        } else {
            console.log('Add class form NOT found in DOM');
        }
    }, 1000);
    
    $(document).on('submit', '#editClassForm', function(e) {
        e.preventDefault();
        console.log('Edit class form submitted via AJAX');
        updateClass();
    });
    
    $(document).on('submit', '#assignTeacherForm', function(e) {
        e.preventDefault();
        console.log('Assign teacher form submitted via AJAX');
        assignTeacherSubmit();
    });
    
    // Delete confirmation will be bound dynamically when modal is loaded
    
    // Student management events are now bound dynamically in bindManageStudentsEvents()
    
    // Mark as initialized
    classesInitialized = true;
    console.log('Classes management initialized successfully');
    
    // Initialize bulk actions state
    updateBulkActions();
    
    // Clean up any lingering modal backdrops
    cleanupModalBackdrops();
    
    // Debug checkbox detection (only in development)
    if (window.location.hostname === 'localhost' || window.location.hostname.includes('127.0.0.1')) {
        debugCheckboxDetection();
    }
}

// Filter classes based on search and grade
function filterClasses() {
    const searchTerm = $('#classSearch').val().toLowerCase();
    const gradeFilter = $('#gradeFilter').val();
    
    $('#classesTable tbody tr').each(function() {
        const row = $(this);
        const grade = row.find('td:nth-child(2)').text().toLowerCase();
        const section = row.find('td:nth-child(3)').text().toLowerCase();
        const description = row.find('td:nth-child(4)').text().toLowerCase();
        const teacher = row.find('td:nth-child(5)').text().toLowerCase();
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !grade.includes(searchTerm) && !section.includes(searchTerm) && 
            !description.includes(searchTerm) && !teacher.includes(searchTerm)) {
            showRow = false;
        }
        
        // Grade filter
        if (gradeFilter && !grade.includes(gradeFilter)) {
            showRow = false;
        }
        
        row.toggle(showRow);
    });
}

// Update bulk action buttons
function updateBulkActions() {
    const selectedCount = $('.class-checkbox:checked').length;
    const totalCheckboxes = $('.class-checkbox').length;
    console.log('updateBulkActions - Total checkboxes:', totalCheckboxes, 'Selected:', selectedCount);
    
    $('#bulkAssignTeacher').prop('disabled', selectedCount === 0);
    
    // Update select all checkbox
    const checkedCheckboxes = $('.class-checkbox:checked').length;
    $('#selectAll').prop('checked', totalCheckboxes > 0 && checkedCheckboxes === totalCheckboxes);
    $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
}

// Get selected class IDs
function getSelectedClassIds() {
    const selectedIds = [];
    const checkedBoxes = $('.class-checkbox:checked');
    console.log('Found checked checkboxes:', checkedBoxes.length);
    
    checkedBoxes.each(function() {
        const value = $(this).val();
        console.log('Checkbox value:', value);
        selectedIds.push(value);
    });
    
    console.log('Selected IDs array:', selectedIds);
    return selectedIds;
}

// Create new class
function createClass() {
    // Check if form exists
    if ($('#addClassForm').length === 0) {
        console.error('Add class form not found');
        showAlert('danger', 'Form not found. Please try again.');
        return;
    }
    
    // Get form data for confirmation
    const grade = $('#addClassForm input[name="grade"]').val();
    const section = $('#addClassForm input[name="section"]').val();
    
    // Show confirmation dialog
    const confirmMessage = `Create class ${grade}-${section}?`;
    
    if (!confirm(confirmMessage)) {
        return; // User cancelled
    }
    
    console.log('Creating class with form data...');
    const formData = new FormData($('#addClassForm')[0]);
    formData.append('action', 'create');
    
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $('#addClassModal').modal('hide');
                $('#addClassForm')[0].reset();
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            showAlert('danger', 'An error occurred while creating the class: ' + error);
        }
    });
}

// Edit class
function editClass(sectionId) {
    // Load edit modal dynamically
    $.ajax({
        url: '/eam_system_v0.1.1/includes/admin/modals/edit_class.php',
        type: 'GET',
        data: { id: sectionId },
        success: function(response) {
            // Remove existing modal and backdrop if present
            $('#editClassModal').remove();
            $('.modal-backdrop').remove();
            
            // Create a container for the modal to isolate it
            const modalContainer = $('<div class="dynamic-modal-container"></div>');
            modalContainer.append(response);
            
            // Add modal container to body
            $('body').append(modalContainer);
            
            // Show modal using enhanced function
            showClassesModal('#editClassModal', {
                backdrop: true,
                keyboard: true,
                focus: true,
                show: true
            });
            
            // Remove modal when hidden and clean up backdrop
            $('#editClassModal').on('hidden.bs.modal', function() {
                $('.modal-backdrop').remove();
                modalContainer.remove();
                $('body').removeClass('modal-open');
            });
        },
        error: function() {
            showAlert('danger', 'An error occurred while loading edit form');
        }
    });
}

// Update class
function updateClass() {
    // Get form data for confirmation
    const grade = $('#editClassForm input[name="grade"]').val();
    const section = $('#editClassForm input[name="section"]').val();
    
    // Show confirmation dialog
    const confirmMessage = `Update class ${grade}-${section}?`;
    
    if (!confirm(confirmMessage)) {
        return; // User cancelled
    }
    
    const formData = new FormData($('#editClassForm')[0]);
    formData.append('action', 'update');
    
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $('#editClassModal').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
            showAlert('danger', 'An error occurred while updating the class');
        }
    });
}

// Assign teacher
function assignTeacher(sectionId) {
    currentSectionId = sectionId;
    
    // Load assign teacher modal dynamically
    $.ajax({
        url: '/eam_system_v0.1.1/includes/admin/modals/assign_teacher.php',
        type: 'GET',
        data: { id: sectionId },
        success: function(response) {
            // Remove existing modal and backdrop if present
            $('#assignTeacherModal').remove();
            $('.modal-backdrop').remove();
            
            // Create a container for the modal to isolate it
            const modalContainer = $('<div class="dynamic-modal-container"></div>');
            modalContainer.append(response);
            
            // Add modal container to body
            $('body').append(modalContainer);
            
            // Show modal using enhanced function
            showClassesModal('#assignTeacherModal', {
                backdrop: true,
                keyboard: true,
                focus: true,
                show: true
            });
            
            // Remove modal when hidden and clean up backdrop
            $('#assignTeacherModal').on('hidden.bs.modal', function() {
                $('.modal-backdrop').remove();
                modalContainer.remove();
                $('body').removeClass('modal-open');
            });
        },
        error: function() {
            showAlert('danger', 'An error occurred while loading assign teacher form');
        }
    });
}

// Assign teacher form submission
function assignTeacherSubmit() {
    // Get teacher name for confirmation
    const teacherName = $('#assignTeacherSelect option:selected').text();
    const sectionId = $('#assignSectionId').val();
    
    // Show confirmation dialog
    const confirmMessage = `Assign "${teacherName}" to this class?`;
    
    if (!confirm(confirmMessage)) {
        return; // User cancelled
    }
    
    const formData = new FormData($('#assignTeacherForm')[0]);
    formData.append('action', 'assign_teacher');
    
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $('#assignTeacherModal').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
            showAlert('danger', 'An error occurred while assigning teacher');
        }
    });
}

// Show bulk assign teacher modal
function showBulkAssignTeacherModal(sectionIds) {
    // Fetch teachers list first
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'GET',
        data: { action: 'get_teachers' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let teacherOptions = '<option value="">Select Teacher</option>';
                response.data.forEach(teacher => {
                    teacherOptions += `<option value="${teacher.user_id}">${teacher.name} (${teacher.email})</option>`;
                });
                
                const modalHtml = `
                    <div class="modal fade" id="bulkAssignTeacherModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title">
                                        <i class="bx bx-user-plus"></i> Bulk Assign Teacher
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle"></i>
                                        Assign teacher to <strong>${sectionIds.length}</strong> selected classes
                                    </div>
                                    <div class="form-group">
                                        <label for="bulkTeacherSelect" class="form-label">Select Teacher <span class="text-danger">*</span></label>
                                        <select class="form-control" id="bulkTeacherSelect" name="teacher_id" required>
                                            ${teacherOptions}
                                        </select>
                                    </div>
                                    <div class="alert alert-warning">
                                        <i class="bx bx-info-circle"></i>
                                        <strong>Note:</strong> This will assign the selected teacher to all ${sectionIds.length} classes. Classes that already have a teacher will be updated.
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-info" onclick="performBulkAssignTeacher([${sectionIds.join(',')}])">
                                        <i class="bx bx-user-plus"></i> Assign Teacher
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('body').append(modalHtml);
                showClassesModal('#bulkAssignTeacherModal', {
                    backdrop: true,
                    keyboard: true,
                    focus: true,
                    show: true
                });
                
                // Remove modal when hidden
                $('#bulkAssignTeacherModal').on('hidden.bs.modal', function() {
                    $(this).remove();
                });
            } else {
                showAlert('danger', 'Failed to load teachers list: ' + response.message);
            }
        },
        error: function() {
            showAlert('danger', 'An error occurred while loading teachers list');
        }
    });
}

// Perform bulk assign teacher
function performBulkAssignTeacher(sectionIds) {
    const teacherId = $('#bulkTeacherSelect').val();
    
    if (!teacherId) {
        showAlert('warning', 'Please select a teacher');
        return;
    }
    
    // Get teacher name for confirmation
    const teacherName = $('#bulkTeacherSelect option:selected').text();
    
    // Show confirmation dialog
    const confirmMessage = `Assign "${teacherName}" to ${sectionIds.length} classes?`;
    
    if (!confirm(confirmMessage)) {
        return; // User cancelled
    }
    
    // Disable the assign button and show loading state
    const assignBtn = $('#bulkAssignTeacherModal .btn-info');
    const originalBtnText = assignBtn.html();
    assignBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Assigning...');
    
    const formData = new FormData();
    formData.append('action', 'bulk_assign_teacher');
    formData.append('teacher_id', teacherId);
    formData.append('section_ids', JSON.stringify(sectionIds));
    
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            console.log('Bulk assign response:', response);
            if (response.success) {
                showAlert('success', response.message);
                $('#bulkAssignTeacherModal').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                console.log('Bulk assign failed:', response.message);
                showAlert('danger', response.message);
                // Re-enable button on error
                assignBtn.prop('disabled', false).html(originalBtnText);
            }
        },
        error: function(xhr, status, error) {
            console.log('Bulk assign AJAX error:', xhr.responseText, status, error);
            
            // Try to parse the response as JSON to get a better error message
            let errorMessage = 'An error occurred while assigning teachers';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    errorMessage = response.message;
                }
            } catch (e) {
                // If not JSON, use the raw response or default message
                if (xhr.responseText) {
                    errorMessage = xhr.responseText;
                }
            }
            
            showAlert('danger', errorMessage);
            // Re-enable button on error
            assignBtn.prop('disabled', false).html(originalBtnText);
        }
    });
}

// Manage students
function manageStudents(sectionId) {
    currentSectionId = sectionId;
    
    // Load manage students modal dynamically
    $.ajax({
        url: '/eam_system_v0.1.1/includes/admin/modals/manage_students.php',
        type: 'GET',
        data: { id: sectionId },
        success: function(response) {
            // Remove existing modal if present
            $('#manageStudentsModal').remove();
            
            // Create a container for the modal to isolate it
            const modalContainer = $('<div class="dynamic-modal-container"></div>');
            modalContainer.append(response);
            
            // Add modal container to body
            $('body').append(modalContainer);
            
            // Load student data
            loadEnrolledStudents(sectionId);
            loadAvailableStudents(sectionId);
            
            // Show modal using enhanced function
            showClassesModal('#manageStudentsModal', {
                backdrop: true,
                keyboard: true,
                focus: true,
                show: true
            });
            
            // Bind event handlers after modal is shown (with delay to ensure DOM is ready)
            setTimeout(() => {
                bindManageStudentsEvents();
            }, 100);
            
            // Remove modal when hidden
            $('#manageStudentsModal').on('hidden.bs.modal', function() {
                modalContainer.remove();
            });
        },
        error: function() {
            showAlert('danger', 'An error occurred while loading manage students form');
        }
    });
}

// Load enrolled students
function loadEnrolledStudents(sectionId) {
    console.log('Loading enrolled students for section ID:', sectionId);
    
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'GET',
        data: { action: 'get_enrolled_students', section_id: sectionId },
        dataType: 'json',
        success: function(response) {
            console.log('Enrolled students response:', response);
            
            if (response.success) {
                enrolledStudents = response.data;
                console.log('Enrolled students data:', enrolledStudents);
                displayEnrolledStudents();
            } else {
                console.error('Error loading enrolled students:', response.message);
                showAlert('danger', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error loading enrolled students:', error);
            console.error('Response:', xhr.responseText);
            showAlert('danger', 'An error occurred while loading enrolled students');
        }
    });
}

// Display enrolled students
function displayEnrolledStudents() {
    const tbody = $('#enrolledStudentsBody');
    tbody.empty();
    
    if (enrolledStudents.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="5" class="text-center text-muted py-3">
                    <i class="bx bx-user-x"></i> No students enrolled
                </td>
            </tr>
        `);
        return;
    }
    
    enrolledStudents.forEach(student => {
        const row = `
            <tr>
                <td>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input enrolled-checkbox" value="${student.user_id}">
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center mr-2">
                            ${student.firstname.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="font-weight-bold">${student.firstname} ${student.lastname}</div>
                            <small class="text-muted">${student.gender}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <small>${student.email}</small>
                </td>
                <td>
                    <small class="text-muted">${formatDate(student.enrollment_date)}</small>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-danger" onclick="unenrollStudent(${student.user_id})" title="Unenroll">
                        <i class="bx bx-user-minus"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

// Load available students
function loadAvailableStudents(sectionId, search = '') {
    console.log('Loading available students for section ID:', sectionId, 'search:', search);
    
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'GET',
        data: { action: 'get_available_students', section_id: sectionId, search: search },
        dataType: 'json',
        success: function(response) {
            console.log('Available students response:', response);
            
            if (response.success) {
                availableStudents = response.data;
                console.log('Available students data:', availableStudents);
                displayAvailableStudents();
            } else {
                console.error('Error loading available students:', response.message);
                showAlert('danger', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error loading available students:', error);
            console.error('Response:', xhr.responseText);
            showAlert('danger', 'An error occurred while loading available students');
        }
    });
}

// Display available students
function displayAvailableStudents() {
    const tbody = $('#availableStudentsBody');
    tbody.empty();
    
    if (availableStudents.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="5" class="text-center text-muted py-3">
                    <i class="bx bx-user-plus"></i> No available students
                </td>
            </tr>
        `);
        return;
    }
    
    availableStudents.forEach(student => {
        const statusBadge = student.status === 'Approved' ? 
            '<span class="badge badge-success">Approved</span>' : 
            '<span class="badge badge-warning">Pending</span>';
        
        const row = `
            <tr>
                <td>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input available-checkbox" value="${student.user_id}">
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2">
                            ${student.firstname.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="font-weight-bold">${student.firstname} ${student.lastname}</div>
                            <small class="text-muted">${student.gender}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <small>${student.email}</small>
                </td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn btn-sm btn-outline-success" onclick="enrollStudent(${student.user_id})" title="Enroll">
                        <i class="bx bx-user-plus"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

// Filter available students
function filterAvailableStudents() {
    const search = $('#studentSearch').val();
    loadAvailableStudents(currentSectionId, search);
}

// Enroll student
function enrollStudent(studentId) {
    // Show confirmation dialog
    const confirmMessage = `Enroll this student?`;
    
    if (!confirm(confirmMessage)) {
        return; // User cancelled
    }
    
    const formData = new FormData();
    formData.append('action', 'enroll_student');
    formData.append('section_id', currentSectionId);
    formData.append('student_id', studentId);
    
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                loadEnrolledStudents(currentSectionId);
                loadAvailableStudents(currentSectionId, $('#studentSearch').val());
                // Close modal after successful enrollment
                $('#manageStudentsModal').modal('hide');
                // Refresh the classes table to show updated student counts
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
            showAlert('danger', 'An error occurred while enrolling student');
        }
    });
}

// Unenroll student
function unenrollStudent(studentId) {
    // Show confirmation dialog
    const confirmMessage = `Unenroll this student?`;
    
    if (!confirm(confirmMessage)) {
        return; // User cancelled
    }
    
    const formData = new FormData();
    formData.append('action', 'unenroll_student');
    formData.append('section_id', currentSectionId);
    formData.append('student_id', studentId);
    
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                loadEnrolledStudents(currentSectionId);
                loadAvailableStudents(currentSectionId, $('#studentSearch').val());
                // Close modal after successful unenrollment
                $('#manageStudentsModal').modal('hide');
                // Refresh the classes table to show updated student counts
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
            showAlert('danger', 'An error occurred while unenrolling student');
        }
    });
}

// Bulk enroll students
function bulkEnrollStudents(studentIds) {
    // Show confirmation dialog
    const confirmMessage = `Enroll ${studentIds.length} students?`;
    
    if (!confirm(confirmMessage)) {
        return; // User cancelled
    }
    
    const formData = new FormData();
    formData.append('action', 'bulk_enroll');
    formData.append('section_id', currentSectionId);
    formData.append('student_ids', JSON.stringify(studentIds));
    
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                loadEnrolledStudents(currentSectionId);
                loadAvailableStudents(currentSectionId, $('#studentSearch').val());
                $('.available-checkbox').prop('checked', false);
                updateBulkEnrollButton();
                // Refresh the classes table to show updated student counts
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
            showAlert('danger', 'An error occurred while enrolling students');
        }
    });
}

// Bulk unenroll students
function bulkUnenrollStudents(studentIds) {
    // Show confirmation dialog
    const confirmMessage = `Unenroll ${studentIds.length} students?`;
    
    if (!confirm(confirmMessage)) {
        return; // User cancelled
    }
    
    const formData = new FormData();
    formData.append('action', 'bulk_unenroll');
    formData.append('section_id', currentSectionId);
    formData.append('student_ids', JSON.stringify(studentIds));
    
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                loadEnrolledStudents(currentSectionId);
                loadAvailableStudents(currentSectionId, $('#studentSearch').val());
                $('.enrolled-checkbox').prop('checked', false);
                updateBulkUnenrollButton();
                // Refresh the classes table to show updated student counts
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
            showAlert('danger', 'An error occurred while unenrolling students');
        }
    });
}

// Get selected enrolled student IDs
function getSelectedEnrolledStudentIds() {
    const selectedIds = [];
    $('.enrolled-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });
    return selectedIds;
}

// Get selected available student IDs
function getSelectedAvailableStudentIds() {
    const selectedIds = [];
    $('.available-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });
    return selectedIds;
}

// Update bulk unenroll button
function updateBulkUnenrollButton() {
    const selectedCount = $('.enrolled-checkbox:checked').length;
    $('#bulkUnenroll').prop('disabled', selectedCount === 0);
    
    // Update select all checkbox
    const totalCheckboxes = $('.enrolled-checkbox').length;
    const checkedCheckboxes = $('.enrolled-checkbox:checked').length;
    $('#selectAllEnrolled').prop('checked', totalCheckboxes > 0 && checkedCheckboxes === totalCheckboxes);
    $('#selectAllEnrolled').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
}

// Update bulk enroll button
function updateBulkEnrollButton() {
    const selectedCount = $('.available-checkbox:checked').length;
    $('#bulkEnroll').prop('disabled', selectedCount === 0);
    
    // Update select all checkbox
    const totalCheckboxes = $('.available-checkbox').length;
    const checkedCheckboxes = $('.available-checkbox:checked').length;
    $('#selectAllAvailable').prop('checked', totalCheckboxes > 0 && checkedCheckboxes === totalCheckboxes);
    $('#selectAllAvailable').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
}

// Bind manage students modal events
function bindManageStudentsEvents() {
    console.log('Binding manage students events');
    
    // Search functionality - use event delegation to ensure it works
    $(document).off('input', '#studentSearch').on('input', '#studentSearch', function() {
        const search = $(this).val();
        console.log('Search input:', search);
        loadAvailableStudents(currentSectionId, search);
    });
    
    // Select all enrolled students
    $('#selectAllEnrolled').off('change').on('change', function() {
        const isChecked = $(this).is(':checked');
        console.log('Select all enrolled:', isChecked);
        $('.enrolled-checkbox').prop('checked', isChecked);
        updateBulkUnenrollButton();
    });
    
    // Select all available students
    $('#selectAllAvailable').off('change').on('change', function() {
        const isChecked = $(this).is(':checked');
        console.log('Select all available:', isChecked);
        $('.available-checkbox').prop('checked', isChecked);
        updateBulkEnrollButton();
    });
    
    // Individual student checkboxes
    $(document).off('change', '.enrolled-checkbox').on('change', '.enrolled-checkbox', function() {
        console.log('Enrolled checkbox changed');
        updateBulkUnenrollButton();
    });
    
    $(document).off('change', '.available-checkbox').on('change', '.available-checkbox', function() {
        console.log('Available checkbox changed');
        updateBulkEnrollButton();
    });
    
    // Bulk operations
    $('#bulkEnroll').off('click').on('click', function() {
        const selectedIds = getSelectedAvailableStudentIds();
        console.log('Bulk enroll clicked, selected IDs:', selectedIds);
        if (selectedIds.length > 0) {
            bulkEnrollStudents(selectedIds);
        } else {
            showAlert('warning', 'Please select students to enroll');
        }
    });
    
    $('#bulkUnenroll').off('click').on('click', function() {
        const selectedIds = getSelectedEnrolledStudentIds();
        console.log('Bulk unenroll clicked, selected IDs:', selectedIds);
        if (selectedIds.length > 0) {
            bulkUnenrollStudents(selectedIds);
        } else {
            showAlert('warning', 'Please select students to unenroll');
        }
    });
    
    console.log('Manage students events bound successfully');
    
    // Debug: Test if search input exists and is working
    const searchInput = $('#studentSearch');
    console.log('Search input found:', searchInput.length > 0);
    if (searchInput.length > 0) {
        console.log('Search input value:', searchInput.val());
        console.log('Search input events bound:', $._data(searchInput[0], 'events'));
    }
}

// Debug function for student data
function debugStudentData() {
    console.log('=== DEBUG STUDENT DATA ===');
    console.log('Current Section ID:', currentSectionId);
    console.log('Enrolled Students:', enrolledStudents);
    console.log('Available Students:', availableStudents);
    
    // Test the backend directly
    console.log('Testing backend calls...');
    
    // Test enrolled students
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'GET',
        data: { action: 'get_enrolled_students', section_id: currentSectionId },
        dataType: 'json',
        success: function(response) {
            console.log('Direct enrolled students call:', response);
        },
        error: function(xhr, status, error) {
            console.error('Direct enrolled students error:', error, xhr.responseText);
        }
    });
    
    // Test available students
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'GET',
        data: { action: 'get_available_students', section_id: currentSectionId, search: '' },
        dataType: 'json',
        success: function(response) {
            console.log('Direct available students call:', response);
        },
        error: function(xhr, status, error) {
            console.error('Direct available students error:', error, xhr.responseText);
        }
    });
    
    // Test if there are any students in the database at all
    $.ajax({
        url: '/eam_system_v0.1.1/config/classes_crud.php',
        type: 'GET',
        data: { action: 'debug_all_students' },
        dataType: 'json',
        success: function(response) {
            console.log('All students in database:', response);
        },
        error: function(xhr, status, error) {
            console.error('All students error:', error, xhr.responseText);
        }
    });
}

// View class details
function viewClass(sectionId) {
    // Load view class modal dynamically
    $.ajax({
        url: '/eam_system_v0.1.1/includes/admin/modals/view_class.php',
        type: 'GET',
        data: { id: sectionId },
        success: function(response) {
            // Remove existing modal and backdrop if present
            $('#viewClassModal').remove();
            $('.modal-backdrop').remove();
            
            // Create a container for the modal to isolate it
            const modalContainer = $('<div class="dynamic-modal-container"></div>');
            modalContainer.append(response);
            
            // Add modal container to body
            $('body').append(modalContainer);
            
            // Show modal using enhanced function
            showClassesModal('#viewClassModal', {
                backdrop: true,
                keyboard: true,
                focus: true,
                show: true
            });
            
            // Remove modal when hidden and clean up backdrop
            $('#viewClassModal').on('hidden.bs.modal', function() {
                // Remove backdrop if it exists
                $('.modal-backdrop').remove();
                // Remove modal container
                modalContainer.remove();
                // Reset body class
                $('body').removeClass('modal-open');
            });
            
            // Handle escape key and backdrop click
            $('#viewClassModal').on('click', function(e) {
                if (e.target === this) {
                    $('#viewClassModal').modal('hide');
                }
            });
        },
        error: function() {
            showAlert('danger', 'An error occurred while loading class details');
        }
    });
}

// Delete class
function deleteClass(sectionId) {
    // Load delete confirmation modal dynamically
    $.ajax({
        url: '/eam_system_v0.1.1/includes/admin/modals/delete_class.php',
        type: 'GET',
        data: { id: sectionId },
        success: function(response) {
            // Remove existing modal and backdrop if present
            $('#deleteClassModal').remove();
            $('.modal-backdrop').remove();
            
            // Create a container for the modal to isolate it
            const modalContainer = $('<div class="dynamic-modal-container"></div>');
            modalContainer.append(response);
            
            // Add modal container to body
            $('body').append(modalContainer);
            
            // Store section ID in modal data
            $('#deleteClassModal').data('section-id', sectionId);
            
            // Show modal using enhanced function
            showClassesModal('#deleteClassModal', {
                backdrop: true,
                keyboard: true,
                focus: true,
                show: true
            });
            
            // Bind delete confirmation checkbox event after modal is loaded
            setTimeout(() => {
                const confirmCheckbox = $('#confirmDelete');
                const deleteButton = $('#confirmDeleteClass');
                
                console.log('Delete modal elements found:', {
                    checkbox: confirmCheckbox.length > 0,
                    button: deleteButton.length > 0
                });
                
                confirmCheckbox.off('change').on('change', function() {
                    const isChecked = $(this).is(':checked');
                    console.log('Delete confirmation checkbox changed:', isChecked);
                    deleteButton.prop('disabled', !isChecked);
                });
                
                // Bind delete button click event
                deleteButton.off('click').on('click', function() {
                    const sectionId = $('#deleteClassModal').data('section-id');
                    console.log('Delete button clicked, section ID:', sectionId);
                    
                    // Show confirmation dialog
                    const confirmMessage = `Delete this class?`;
                    
                    if (!confirm(confirmMessage)) {
                        return; // User cancelled
                    }
                    
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('section_id', sectionId);
                    
                    $.ajax({
                        url: '/eam_system_v0.1.1/config/classes_crud.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showAlert('success', response.message);
                                $('#deleteClassModal').modal('hide');
                                setTimeout(() => {
                                    location.reload();
                                }, 1000);
                            } else {
                                showAlert('danger', response.message);
                            }
                        },
                        error: function() {
                            showAlert('danger', 'An error occurred while deleting the class');
                        }
                    });
                });
            }, 100);
            
            // Remove modal when hidden and clean up backdrop
            $('#deleteClassModal').on('hidden.bs.modal', function() {
                $('.modal-backdrop').remove();
                modalContainer.remove();
                $('body').removeClass('modal-open');
            });
        },
        error: function() {
            showAlert('danger', 'An error occurred while loading delete confirmation');
        }
    });
}

// Delete class confirmation is now handled dynamically in the deleteClass() function

// Utility function to format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Utility function to show alerts
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    // Remove existing alerts only from the main content area, not from modals
    $('.card-body .alert').remove();
    
    // Add new alert to main content area
    $('.card-body').prepend(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        $('.card-body .alert').fadeOut();
    }, 5000);
}

// Clean up modal backdrops and reset body state
function cleanupModalBackdrops() {
    // Remove any lingering modal backdrops
    $('.modal-backdrop').remove();
    
    // Remove modal-open class from body
    $('body').removeClass('modal-open');
    
    // Reset body padding if it was modified
    $('body').css('padding-right', '');
    
    console.log('Modal backdrops cleaned up');
}

// Debug checkbox detection
function debugCheckboxDetection() {
    console.log('=== CHECKBOX DEBUG ===');
    
    // Check if classes table exists
    const classesTable = $('#classesTable');
    console.log('Classes table found:', classesTable.length > 0);
    
    // Check for select all checkbox
    const selectAll = $('#selectAll');
    console.log('Select all checkbox found:', selectAll.length > 0);
    if (selectAll.length > 0) {
        console.log('Select all checkbox checked:', selectAll.is(':checked'));
    }
    
    // Check for individual checkboxes
    const classCheckboxes = $('.class-checkbox');
    console.log('Class checkboxes found:', classCheckboxes.length);
    
    // Check each checkbox individually
    classCheckboxes.each(function(index) {
        const checkbox = $(this);
        console.log(`Checkbox ${index + 1}:`, {
            value: checkbox.val(),
            checked: checkbox.is(':checked'),
            classes: checkbox.attr('class'),
            visible: checkbox.is(':visible')
        });
    });
    
    // Check bulk assign button
    const bulkAssignBtn = $('#bulkAssignTeacher');
    console.log('Bulk assign button found:', bulkAssignBtn.length > 0);
    if (bulkAssignBtn.length > 0) {
        console.log('Bulk assign button disabled:', bulkAssignBtn.prop('disabled'));
    }
    
    // Force update bulk actions
    console.log('Forcing updateBulkActions...');
    updateBulkActions();
    
    console.log('=== END CHECKBOX DEBUG ===');
}

// Manual trigger function for debugging (can be called from console)
window.debugClassesCheckboxes = function() {
    console.log('Manual checkbox debug triggered');
    debugCheckboxDetection();
    
    // Also try to manually check the first checkbox
    const firstCheckbox = $('.class-checkbox').first();
    if (firstCheckbox.length > 0) {
        console.log('Manually checking first checkbox...');
        firstCheckbox.prop('checked', true);
        updateBulkActions();
        console.log('After manual check - selected count:', $('.class-checkbox:checked').length);
    }
};

// Disable global modal handler temporarily for classes modals
function disableGlobalModalHandler() {
    // Temporarily disable the global modal interval
    if (window.globalModalInterval) {
        clearInterval(window.globalModalInterval);
        console.log('Disabled global modal handler for classes');
    }
    
    // Also disable the global modal event handlers temporarily
    $(document).off('show.bs.modal.classes');
    $(document).off('shown.bs.modal.classes');
    $(document).off('hide.bs.modal.classes');
    $(document).off('hidden.bs.modal.classes');
}

// Re-enable global modal handler
function enableGlobalModalHandler() {
    // Re-enable the global modal interval if it exists
    if (typeof initializeModalHeaderDimming === 'function') {
        // The global handler will re-initialize itself
        console.log('Re-enabled global modal handler');
    }
}

// Enhanced modal show function for classes
function showClassesModal(modalId, options = {}) {
    // Disable global handler temporarily
    disableGlobalModalHandler();
    
    // Show the modal with proper options
    $(modalId).modal({
        backdrop: true,
        keyboard: true,
        focus: true,
        show: true
    });
    
    // Override z-index after a short delay
    setTimeout(function() {
        $(modalId).css('z-index', '9999');
        $(modalId + ' .modal-dialog').css('z-index', '10000');
        $('.modal-backdrop').css('z-index', '9998');
        
        // Ensure body has modal-open class
        $('body').addClass('modal-open');
        
        // Remove aria-hidden to prevent accessibility warnings
        $(modalId).removeAttr('aria-hidden');
    }, 10);
    
    // Re-enable global handler when modal is hidden
    $(modalId).off('hidden.bs.modal.classes').on('hidden.bs.modal.classes', function() {
        enableGlobalModalHandler();
        cleanupModalBackdrops();
    });
}

// Initialize when document is ready
// Initialization is handled through the onFragmentLoaded hook system

// Initialize for AJAX navigation
// Hook into the global page loading system
console.log('Setting up classes.js hook system...');

if (typeof window.onFragmentLoaded === 'function') {
    console.log('Extending existing onFragmentLoaded hook for classes');
    const originalHook = window.onFragmentLoaded;
    window.onFragmentLoaded = function(page) {
        console.log('onFragmentLoaded called with page:', page);
        // Call the original hook first
        originalHook(page);
        // Then handle classes initialization
        if (page === 'classes') {
            console.log('Classes page loaded, initializing...');
            initClasses();
        } else {
            // Reset classes initialization when leaving classes page
            resetClassesInit();
        }
    };
} else {
    console.log('Creating new onFragmentLoaded hook for classes');
    window.onFragmentLoaded = function(page) {
        console.log('onFragmentLoaded called with page:', page);
        if (page === 'classes') {
            console.log('Classes page loaded, initializing...');
            initClasses();
        } else {
            // Reset classes initialization when leaving classes page
            resetClassesInit();
        }
    };
}

// Note: Initialization is now handled by the global initializePageSpecificJS() function in script.js
// This prevents duplicate initialization and ensures proper loading after login redirects
