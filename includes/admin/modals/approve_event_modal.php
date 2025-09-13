<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Approve Event Modal -->
<div class="modal fade" id="approveEventModal" tabindex="-1" role="dialog" aria-labelledby="approveEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveEventModalLabel">
                    <i class="bx bx-check-circle me-2"></i>Approve Event
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong>Event Approval</strong> - This event will be approved and made visible to all users.
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="text-primary"><i class="bx bx-title me-1"></i>Event Title:</h6>
                        <p id="approveEventTitle" class="mb-3"></p>
                        
                        <h6 class="text-primary"><i class="bx bx-user me-1"></i>Created By:</h6>
                        <p id="approveEventCreator" class="mb-3"></p>
                        
                        <h6 class="text-primary"><i class="bx bx-calendar me-1"></i>Event Date:</h6>
                        <p id="approveEventDate" class="mb-3"></p>
                        
                        <h6 class="text-primary"><i class="bx bx-map-pin me-1"></i>Location:</h6>
                        <p id="approveEventLocation" class="mb-3"></p>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="approval-icon-container mb-3">
                            <div class="approval-icon rounded-circle border d-flex align-items-center justify-content-center mx-auto"
                                 style="width: 120px; height: 120px; background-color: #d4edda; color: #155724;">
                                <i class="bx bx-check-circle" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="approvalNotes" class="text-primary">
                        <i class="bx bx-message-square-detail me-1"></i>Approval Notes (Optional)
                    </label>
                    <textarea class="form-control" id="approvalNotes" rows="3" 
                              placeholder="Add any notes about this approval..."></textarea>
                </div>
                
                <div class="alert alert-info">
                    <i class="bx bx-envelope me-2"></i>
                    <strong>Email Notification:</strong> An approval email will be sent to the event creator.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirmApproveEvent">
                    <i class="bx bx-check me-1"></i>Approve Event
                </button>
            </div>
        </div>
    </div>
</div>
