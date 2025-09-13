<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Student Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bx bx-bolt mr-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- View Events -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="?page=events" class="btn btn-primary btn-block h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bx bx-calendar-event mb-2" style="font-size: 2rem;"></i>
                            <span>View Events</span>
                        </a>
                    </div>
                    
                    <!-- View Attendance -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="?page=attendance" class="btn btn-success btn-block h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bx bx-check-square mb-2" style="font-size: 2rem;"></i>
                            <span>My Attendance</span>
                        </a>
                    </div>
                    
                    <!-- Submit Excuse -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <button type="button" class="btn btn-warning btn-block h-100 d-flex flex-column align-items-center justify-content-center text-white" data-bs-toggle="modal" data-bs-target="#submitExcuseModal">
                            <i class="bx bx-file mb-2" style="font-size: 2rem;"></i>
                            <span>Submit Excuse</span>
                        </button>
                    </div>
                    
                    <!-- View Profile -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <button type="button" class="btn btn-info btn-block h-100 d-flex flex-column align-items-center justify-content-center" onclick="scrollToPersonalInfo()">
                            <i class="bx bx-user mb-2" style="font-size: 2rem;"></i>
                            <span>My Profile</span>
                        </button>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
