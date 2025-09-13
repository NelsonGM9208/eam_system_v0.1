<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Event Creation Confirmation Modal -->
<div class="modal fade" id="confirmEventModal" tabindex="-1" role="dialog" aria-labelledby="confirmEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="confirmEventModalLabel">
                    <i class="bx bx-calendar-check me-2"></i>Confirm Event Creation
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong>Please review your event details before creating:</strong>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="bx bx-title me-1"></i>Event Title:</h6>
                        <p id="confirmTitle" class="mb-3"></p>
                        
                        <h6 class="text-primary"><i class="bx bx-category me-1"></i>Event Type:</h6>
                        <p id="confirmType" class="mb-3"></p>
                        
                        <h6 class="text-primary"><i class="bx bx-map-pin me-1"></i>Location:</h6>
                        <p id="confirmLocation" class="mb-3"></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="bx bx-calendar me-1"></i>Date & Time:</h6>
                        <p id="confirmDateTime" class="mb-3"></p>
                        
                        <h6 class="text-primary"><i class="bx bx-money me-1"></i>Absence Penalty:</h6>
                        <p id="confirmPenalty" class="mb-3"></p>
                        
                        <h6 class="text-primary"><i class="bx bx-group me-1"></i>Selected Sections:</h6>
                        <p id="confirmSections" class="mb-3"></p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary"><i class="bx bx-detail me-1"></i>Description:</h6>
                        <p id="confirmDescription" class="mb-3"></p>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Note:</strong> Once created, this event will be submitted for approval and cannot be edited until approved.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmCreateEvent">
                    <i class="bx bx-check me-1"></i>Create Event
                </button>
            </div>
        </div>
    </div>
</div>
