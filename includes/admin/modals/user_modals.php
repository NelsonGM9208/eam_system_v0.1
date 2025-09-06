<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" id="viewModalContent"></div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" id="editModalContent"></div>
  </div>
</div>

<!-- Delete modal is now handled by usersTBL.php -->

<script>
// Ensure jQuery is loaded before running this code
if (typeof jQuery !== 'undefined') {
$(document).ready(function(){
    // View - Using event delegation for dynamically loaded content
    $(document).on('click', '.view-btn', function(){
        const userId = $(this).data('id');
        const role = $(this).data('role');
        // Use the new modal loader
        console.log('Loading modal for user ID:', userId, 'role:', role);
        $('#viewModalContent').load('/eam_system_v0.1.1/includes/admin/load_modal.php?modal=user_details&id='+userId, function(response, status, xhr){
            if (status == "error") {
                console.error('Error loading modal:', xhr.status, xhr.statusText);
                alert('Error loading modal content. Please try again.');
            } else {
                console.log('Modal loaded successfully');
                $('#viewModal').modal('show');
            }
        });
    });

    // Edit - Using event delegation for dynamically loaded content
    $(document).on('click', '.edit-btn', function(){
        const userId = $(this).data('id');
        console.log('Loading edit modal for user ID:', userId);
        $('#editModalContent').load('../includes/admin/modals/edit_user.php?id='+userId, function(response, status, xhr){
            if (status == "error") {
                console.error('Error loading edit modal:', xhr.status, xhr.statusText);
                alert('Error loading edit modal content. Please try again.');
            } else {
                console.log('Edit modal loaded successfully');
                $('#editModal').modal('show');
            }
        });
    });

    // Delete handlers are now handled by page-specific JavaScript files
    // (users.js for users page, dashboard.js for dashboard page)
});
} else {
    console.error('jQuery is not loaded. Cannot initialize user modals.');
}
</script>