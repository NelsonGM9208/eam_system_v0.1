<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="eventDetailsModalLabel">
                    <i class="bx bx-info-circle me-2"></i>
                    Event Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="eventDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading event details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bx bx-x mr-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary" id="registerFromDetailsBtn" style="display: none;">
                    <i class="bx bx-user-plus mr-1"></i>Register for Event
                </button>
            </div>
        </div>
    </div>
</div>
