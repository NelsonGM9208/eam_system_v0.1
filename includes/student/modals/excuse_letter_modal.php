<?php
/**
 * Excuse Letter Modal Components
 * Contains all modals for excuse letter management
 */
?>

<!-- Submit Excuse Letter Modal -->
<div class="modal fade" id="submitExcuseModal" tabindex="-1" role="dialog" aria-labelledby="submitExcuseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="submitExcuseModalLabel">
                    <i class="bx bx-file-blank"></i> Submit Excuse Letter
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="excuseLetterForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="excuseEvent">Event <span class="text-danger">*</span></label>
                                <select class="form-control" id="excuseEvent" name="event_id" required>
                                    <option value="">Select an event</option>
                                    <?php foreach ($available_events as $event): ?>
                                        <option value="<?php echo $event['event_id']; ?>">
                                            <?php echo htmlspecialchars($event['title']); ?> - 
                                            <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Select the event you need to be excused from</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="excuseType">Excuse Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="excuseType" name="excuse_type" required>
                                    <option value="">Select excuse type</option>
                                    <option value="Medical">Medical</option>
                                    <option value="Family Emergency">Family Emergency</option>
                                    <option value="Academic">Academic</option>
                                    <option value="Personal">Personal</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="excuseReason">Reason for Absence <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="excuseReason" name="reason" rows="4" required 
                                  placeholder="Please provide a detailed explanation for your absence..."></textarea>
                        <small class="form-text text-muted">Be specific and provide relevant details</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="excuseStartDate">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="excuseStartDate" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="excuseEndDate">End Date</label>
                                <input type="date" class="form-control" id="excuseEndDate" name="end_date">
                                <small class="form-text text-muted">Leave blank if it's a single day absence</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="excuseDocuments">Supporting Documents</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="excuseDocuments" name="documents[]" multiple 
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.mp4,.avi,.mov">
                            <label class="custom-file-label" for="excuseDocuments">Choose files...</label>
                        </div>
                        <small class="form-text text-muted">
                            Supported formats: PDF, Word documents, Images (JPG, PNG, GIF), Videos (MP4, AVI, MOV)
                        </small>
                        <div id="filePreview" class="mt-2"></div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="excuseUrgent" name="is_urgent">
                            <label class="form-check-label" for="excuseUrgent">
                                Mark as urgent
                            </label>
                        </div>
                        <small class="form-text text-muted">Check this if your excuse requires immediate attention</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitExcuseBtn">
                        <i class="bx bx-send"></i> Submit Excuse Letter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Excuse Details Modal -->
<div class="modal fade" id="viewExcuseModal" tabindex="-1" role="dialog" aria-labelledby="viewExcuseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewExcuseModalLabel">
                    <i class="bx bx-show"></i> Excuse Letter Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="excuseDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Excuse Modal -->
<div class="modal fade" id="editExcuseModal" tabindex="-1" role="dialog" aria-labelledby="editExcuseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editExcuseModalLabel">
                    <i class="bx bx-edit"></i> Edit Excuse Letter
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editExcuseForm" enctype="multipart/form-data">
                <input type="hidden" id="editExcuseId" name="excuse_id">
                <div class="modal-body" id="editExcuseContent">
                    <!-- Content will be loaded via AJAX -->
                    <div class="text-center">
                        <div class="spinner-border text-warning" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="updateExcuseBtn">
                        <i class="bx bx-save"></i> Update Excuse Letter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div class="modal fade" id="documentPreviewModal" tabindex="-1" role="dialog" aria-labelledby="documentPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="documentPreviewModalLabel">
                    <i class="bx bx-file"></i> Document Preview
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="documentPreviewContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="#" id="downloadDocumentBtn" class="btn btn-primary" download>
                    <i class="bx bx-download"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>
