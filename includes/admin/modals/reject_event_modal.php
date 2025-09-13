<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Reject Event Modal -->
<div class="modal fade" id="rejectEventModal" tabindex="-1" role="dialog" aria-labelledby="rejectEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectEventModalLabel">
                    <i class="bx bx-x-circle me-2"></i>Reject Event
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Event Rejection</strong> - This event will be rejected and will not be visible to users.
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="text-primary"><i class="bx bx-title me-1"></i>Event Title:</h6>
                        <p id="rejectEventTitle" class="mb-3"></p>
                        
                        <h6 class="text-primary"><i class="bx bx-user me-1"></i>Created By:</h6>
                        <p id="rejectEventCreator" class="mb-3"></p>
                        
                        <h6 class="text-primary"><i class="bx bx-calendar me-1"></i>Event Date:</h6>
                        <p id="rejectEventDate" class="mb-3"></p>
                        
                        <h6 class="text-primary"><i class="bx bx-map-pin me-1"></i>Location:</h6>
                        <p id="rejectEventLocation" class="mb-3"></p>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="rejection-icon-container mb-3">
                            <div class="rejection-icon rounded-circle border d-flex align-items-center justify-content-center mx-auto"
                                 style="width: 120px; height: 120px; background-color: #f8d7da; color: #721c24;">
                                <i class="bx bx-x-circle" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="rejectionReason" class="text-primary">
                        <i class="bx bx-message-square-error me-1"></i>Rejection Reason *
                    </label>
                    <textarea class="form-control" id="rejectionReason" rows="4" 
                              placeholder="Please provide a reason for rejecting this event..." required></textarea>
                    <small class="form-text text-muted">This reason will be included in the email notification to the event creator.</small>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bx bx-envelope me-2"></i>
                    <strong>Email Notification:</strong> A rejection email with the reason will be sent to the event creator.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmRejectEvent">
                    <i class="bx bx-x me-1"></i>Reject Event
                </button>
            </div>
        </div>
    </div>
</div>
