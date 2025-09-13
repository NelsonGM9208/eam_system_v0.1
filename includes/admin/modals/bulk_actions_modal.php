<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1" role="dialog" aria-labelledby="bulkActionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="bulkActionsModalLabel">
                    <i class="bx bx-layer me-2"></i>Bulk Actions
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong>Bulk Operation</strong> - You are about to perform a bulk action on multiple events.
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="bx bx-list-check me-1"></i>Selected Events:</h6>
                        <p id="bulkSelectedCount" class="mb-3"></p>
                        
                        <h6 class="text-primary"><i class="bx bx-cog me-1"></i>Action:</h6>
                        <p id="bulkActionType" class="mb-3"></p>
                    </div>
                    <div class="col-md-6 text-center">
                        <div class="bulk-icon-container mb-3">
                            <div class="bulk-icon rounded-circle border d-flex align-items-center justify-content-center mx-auto"
                                 style="width: 120px; height: 120px; background-color: #e3f2fd; color: #1976d2;">
                                <i class="bx bx-layer" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group" id="bulkNotesGroup">
                    <label for="bulkNotes" class="text-primary">
                        <i class="bx bx-message-square-detail me-1"></i>Notes (Optional)
                    </label>
                    <textarea class="form-control" id="bulkNotes" rows="3" 
                              placeholder="Add any notes for this bulk operation..."></textarea>
                </div>
                
                <div class="form-group" id="bulkReasonGroup" style="display: none;">
                    <label for="bulkReason" class="text-primary">
                        <i class="bx bx-message-square-error me-1"></i>Rejection Reason *
                    </label>
                    <textarea class="form-control" id="bulkReason" rows="4" 
                              placeholder="Please provide a reason for rejecting these events..." required></textarea>
                    <small class="form-text text-muted">This reason will be included in the email notifications to event creators.</small>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bx bx-envelope me-2"></i>
                    <strong>Email Notifications:</strong> Email notifications will be sent to all affected event creators.
                </div>
                
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. Please review your selection carefully.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmBulkAction">
                    <i class="bx bx-check me-1"></i>Confirm Action
                </button>
            </div>
        </div>
    </div>
</div>
