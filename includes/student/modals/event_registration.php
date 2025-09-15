<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Event Registration Modal -->
<div class="modal fade" id="eventRegistrationModal" tabindex="-1" aria-labelledby="eventRegistrationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="eventRegistrationModalLabel">
                    <i class="bx bx-user-plus me-2"></i>
                    Register for Event
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Event Info -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-2" id="registrationEventTitle">Event Title</h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="bx bx-calendar mr-1"></i>
                                            <span id="registrationEventDate">Date</span>
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="bx bx-time mr-1"></i>
                                            <span id="registrationEventTime">Time</span>
                                        </small>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted">
                                            <i class="bx bx-map mr-1"></i>
                                            <span id="registrationEventLocation">Location</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registration Steps -->
                <div class="registration-steps">
                    <!-- Step 1: Student Info -->
                    <div class="step" id="step1">
                        <h6 class="mb-3">
                            <i class="bx bx-user me-2"></i>
                            Step 1: Verify Your Information
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="studentName" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Student ID</label>
                                <input type="text" class="form-control" id="studentId" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">LRN</label>
                                <input type="text" class="form-control" id="studentLRN" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Section</label>
                                <input type="text" class="form-control" id="studentSection" readonly>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                                Next <i class="bx bx-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: QR Code Scan -->
                    <div class="step" id="step2" style="display: none;">
                        <h6 class="mb-3">
                            <i class="bx bx-qr-scan me-2"></i>
                            Step 2: Scan QR Code
                        </h6>
                        <div class="alert alert-info" id="qrTimingInfo">
                            <i class="bx bx-info-circle me-2"></i>
                            <span id="qrTimingMessage">QR codes are only valid 1 hour before the event starts. Please scan the QR code displayed at the event location to complete your registration.</span>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="camera-container">
                                    <video id="cameraPreview" autoplay playsinline style="width: 100%; height: 300px; background: #000; border-radius: 8px;"></video>
                                    <div id="cameraError" class="alert alert-danger mt-2" style="display: none;">
                                        <i class="bx bx-error-circle me-2"></i>
                                        <span id="cameraErrorMessage">Camera access denied or not available.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="scan-instructions">
                                    <h6>Scan Instructions:</h6>
                                    <ol class="small">
                                        <li>Point your camera at the QR code</li>
                                        <li>Ensure the QR code is clearly visible</li>
                                        <li>Wait for automatic detection</li>
                                        <li>Registration will complete automatically</li>
                                    </ol>
                                    
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="startCamera()">
                                            <i class="bx bx-camera me-1"></i>Start Camera
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="stopCamera()">
                                            <i class="bx bx-stop me-1"></i>Stop Camera
                                        </button>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <label class="form-label small">Or enter QR code manually:</label>
                                        <input type="text" class="form-control form-control-sm" id="manualQRCode" placeholder="Enter QR code...">
                                        <button type="button" class="btn btn-success btn-sm mt-2" onclick="processManualQR()">
                                            <i class="bx bx-check me-1"></i>Submit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="prevStep(1)">
                                <i class="bx bx-arrow-left me-1"></i>Previous
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Confirmation -->
                    <div class="step" id="step3" style="display: none;">
                        <h6 class="mb-3">
                            <i class="bx bx-check-circle me-2"></i>
                            Step 3: Registration Confirmation
                        </h6>
                        <div class="alert alert-success">
                            <i class="bx bx-check-circle me-2"></i>
                            <strong>Registration Successful!</strong>
                            <p class="mb-0 mt-2">You have successfully registered for this event. You will receive a confirmation email shortly.</p>
                        </div>
                        
                        <div class="registration-summary">
                            <h6>Registration Summary:</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <small class="text-muted">Event:</small>
                                    <div id="confirmEventTitle">Event Title</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Registration Time:</small>
                                    <div id="confirmRegistrationTime">Current Time</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Student:</small>
                                    <div id="confirmStudentName">Student Name</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Status:</small>
                                    <div><span class="badge badge-success">Registered</span></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                                <i class="bx bx-check me-1"></i>Complete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
