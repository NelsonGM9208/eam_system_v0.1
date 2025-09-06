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
                }
                
                #classesTable .form-check-input {
                    margin: 0 !important;
                    transform: scale(1.2);
                    cursor: pointer;
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
                
                /* Modal isolation styles */
                .dynamic-modal-container {
                    position: relative;
                    z-index: 1055;
                }
                
                .dynamic-modal-container .modal {
                    z-index: 1055;
                }
                
                .dynamic-modal-container .modal-backdrop {
                    z-index: 1050;
                }
            `)
            .appendTo('head');
    }
});

// Apply checkbox styling function
function applyCheckboxStyling() {
    // Ensure the first column has proper styling
    $('#classesTable th:first-child, #classesTable td:first-child').css({
        'width': '50px',
        'text-align': 'center',
        'vertical-align': 'middle',
        'padding': '8px 4px'
    });
    
    // Style the checkboxes
    $('#classesTable .form-check-input').css({
        'margin': '0',
        'transform': 'scale(1.2)',
        'cursor': 'pointer'
    });
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
        if (selectedIds.length > 0) {
            showBulkAssignTeacherModal(selectedIds);
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
    
    // Delete confirmation
    $('#confirmDelete').on('change', function() {
        $('#confirmDeleteClass').prop('disabled', !$(this).is(':checked'));
    });
    
    // Student management
    $('#studentSearch').on('input', function() {
        filterAvailableStudents();
    });
    
    // Select all enrolled students
    $('#selectAllEnrolled').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.enrolled-checkbox').prop('checked', isChecked);
        updateBulkUnenrollButton();
    });
    
    // Select all available students
    $('#selectAllAvailable').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.available-checkbox').prop('checked', isChecked);
        updateBulkEnrollButton();
    });
    
    // Individual student checkboxes
    $(document).on('change', '.enrolled-checkbox', function() {
        updateBulkUnenrollButton();
    });
    
    $(document).on('change', '.available-checkbox', function() {
        updateBulkEnrollButton();
    });
    
    // Bulk operations
    $('#bulkEnroll').on('click', function() {
        const selectedIds = getSelectedAvailableStudentIds();
        if (selectedIds.length > 0) {
            bulkEnrollStudents(selectedIds);
        }
    });
    
    $('#bulkUnenroll').on('click', function() {
        const selectedIds = getSelectedEnrolledStudentIds();
        if (selectedIds.length > 0) {
            bulkUnenrollStudents(selectedIds);
        }
    });
    
    // Mark as initialized
    classesInitialized = true;
    console.log('Classes management initialized successfully');
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
    $('#bulkAssignTeacher').prop('disabled', selectedCount === 0);
    
    // Update select all checkbox
    const totalCheckboxes = $('.class-checkbox').length;
    const checkedCheckboxes = $('.class-checkbox:checked').length;
    $('#selectAll').prop('checked', totalCheckboxes > 0 && checkedCheckboxes === totalCheckboxes);
    $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
}

// Get selected class IDs
function getSelectedClassIds() {
    const selectedIds = [];
    $('.class-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });
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
    
    console.log('Creating class with form data...');
    const formData = new FormData($('#addClassForm')[0]);
    formData.append('action', 'create');
    
    $.ajax({
        url: '../includes/admin/classes_crud.php',
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
        url: '../includes/admin/modals/edit_class.php',
        type: 'GET',
        data: { id: sectionId },
        success: function(response) {
            // Remove existing modal if present
            $('#editClassModal').remove();
            
            // Create a container for the modal to isolate it
            const modalContainer = $('<div class="dynamic-modal-container"></div>');
            modalContainer.append(response);
            
            // Add modal container to body
            $('body').append(modalContainer);
            
            // Show modal
            $('#editClassModal').modal('show');
            
            // Remove modal when hidden
            $('#editClassModal').on('hidden.bs.modal', function() {
                modalContainer.remove();
            });
        },
        error: function() {
            showAlert('danger', 'An error occurred while loading edit form');
        }
    });
}

// Update class
function updateClass() {
    const formData = new FormData($('#editClassForm')[0]);
    formData.append('action', 'update');
    
    $.ajax({
        url: '../includes/admin/classes_crud.php',
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
        url: '../includes/admin/modals/assign_teacher.php',
        type: 'GET',
        data: { id: sectionId },
        success: function(response) {
            // Remove existing modal if present
            $('#assignTeacherModal').remove();
            
            // Create a container for the modal to isolate it
            const modalContainer = $('<div class="dynamic-modal-container"></div>');
            modalContainer.append(response);
            
            // Add modal container to body
            $('body').append(modalContainer);
            
            // Show modal
            $('#assignTeacherModal').modal('show');
            
            // Remove modal when hidden
            $('#assignTeacherModal').on('hidden.bs.modal', function() {
                modalContainer.remove();
            });
        },
        error: function() {
            showAlert('danger', 'An error occurred while loading assign teacher form');
        }
    });
}

// Assign teacher form submission
function assignTeacherSubmit() {
    const formData = new FormData($('#assignTeacherForm')[0]);
    formData.append('action', 'assign_teacher');
    
    $.ajax({
        url: '../includes/admin/classes_crud.php',
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
    // Create a simple prompt for teacher selection
    const teacherSelect = $('#assignTeacherSelect').clone();
    teacherSelect.attr('id', 'bulkTeacherSelect');
    
    const modalHtml = `
        <div class="modal fade" id="bulkAssignTeacherModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Bulk Assign Teacher</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Assign teacher to <strong>${sectionIds.length}</strong> selected classes:</p>
                        <div class="form-group">
                            <label>Select Teacher:</label>
                            ${teacherSelect[0].outerHTML}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-info" onclick="performBulkAssignTeacher([${sectionIds.join(',')}])">
                            Assign Teacher
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modalHtml);
    $('#bulkAssignTeacherModal').modal('show');
    
    // Remove modal when hidden
    $('#bulkAssignTeacherModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// Perform bulk assign teacher
function performBulkAssignTeacher(sectionIds) {
    const teacherId = $('#bulkTeacherSelect').val();
    
    if (!teacherId) {
        showAlert('warning', 'Please select a teacher');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'bulk_assign_teacher');
    formData.append('teacher_id', teacherId);
    formData.append('section_ids', JSON.stringify(sectionIds));
    
    $.ajax({
        url: '../includes/admin/classes_crud.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $('#bulkAssignTeacherModal').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
            showAlert('danger', 'An error occurred while assigning teachers');
        }
    });
}

// Manage students
function manageStudents(sectionId) {
    currentSectionId = sectionId;
    
    // Load manage students modal dynamically
    $.ajax({
        url: '../includes/admin/modals/manage_students.php',
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
            
            // Show modal
            $('#manageStudentsModal').modal('show');
            
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
    $.ajax({
        url: '../includes/admin/classes_crud.php',
        type: 'GET',
        data: { action: 'get_enrolled_students', section_id: sectionId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                enrolledStudents = response.data;
                displayEnrolledStudents();
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
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
                    <input type="checkbox" class="form-check-input enrolled-checkbox" value="${student.user_id}">
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
    $.ajax({
        url: '../includes/admin/classes_crud.php',
        type: 'GET',
        data: { action: 'get_available_students', section_id: sectionId, search: search },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                availableStudents = response.data;
                displayAvailableStudents();
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
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
                    <input type="checkbox" class="form-check-input available-checkbox" value="${student.user_id}">
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
    const formData = new FormData();
    formData.append('action', 'enroll_student');
    formData.append('section_id', currentSectionId);
    formData.append('student_id', studentId);
    
    $.ajax({
        url: '../includes/admin/classes_crud.php',
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
    if (!confirm('Are you sure you want to unenroll this student?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'unenroll_student');
    formData.append('section_id', currentSectionId);
    formData.append('student_id', studentId);
    
    $.ajax({
        url: '../includes/admin/classes_crud.php',
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
    const formData = new FormData();
    formData.append('action', 'bulk_enroll');
    formData.append('section_id', currentSectionId);
    formData.append('student_ids', JSON.stringify(studentIds));
    
    $.ajax({
        url: '../includes/admin/classes_crud.php',
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
    if (!confirm(`Are you sure you want to unenroll ${studentIds.length} students?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'bulk_unenroll');
    formData.append('section_id', currentSectionId);
    formData.append('student_ids', JSON.stringify(studentIds));
    
    $.ajax({
        url: '../includes/admin/classes_crud.php',
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

// View class details
function viewClass(sectionId) {
    // Load view class modal dynamically
    $.ajax({
        url: '../includes/admin/modals/view_class.php',
        type: 'GET',
        data: { id: sectionId },
        success: function(response) {
            // Remove existing modal if present
            $('#viewClassModal').remove();
            
            // Create a container for the modal to isolate it
            const modalContainer = $('<div class="dynamic-modal-container"></div>');
            modalContainer.append(response);
            
            // Add modal container to body
            $('body').append(modalContainer);
            
            // Show modal
            $('#viewClassModal').modal('show');
            
            // Remove modal when hidden
            $('#viewClassModal').on('hidden.bs.modal', function() {
                modalContainer.remove();
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
        url: '../includes/admin/modals/delete_class.php',
        type: 'GET',
        data: { id: sectionId },
        success: function(response) {
            // Remove existing modal if present
            $('#deleteClassModal').remove();
            
            // Create a container for the modal to isolate it
            const modalContainer = $('<div class="dynamic-modal-container"></div>');
            modalContainer.append(response);
            
            // Add modal container to body
            $('body').append(modalContainer);
            
            // Show modal
            $('#deleteClassModal').modal('show');
            
            // Remove modal when hidden
            $('#deleteClassModal').on('hidden.bs.modal', function() {
                modalContainer.remove();
            });
        },
        error: function() {
            showAlert('danger', 'An error occurred while loading delete confirmation');
        }
    });
}

// Confirm delete class
$('#confirmDeleteClass').on('click', function() {
    const sectionId = $('#deleteClassModal').data('section-id');
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('section_id', sectionId);
    
    $.ajax({
        url: '../includes/admin/classes_crud.php',
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
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert
    $('.card-body').prepend(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
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
