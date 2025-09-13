<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bx bx-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- User Management -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="?page=users" class="btn btn-primary btn-block h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bx bx-user-plus mb-2" style="font-size: 2rem;"></i>
                            <span>Manage Users</span>
                        </a>
                    </div>
                    
                    <!-- Event Management -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="?page=events" class="btn btn-success btn-block h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bx bx-calendar-event mb-2" style="font-size: 2rem;"></i>
                            <span>Manage Events</span>
                        </a>
                    </div>
                    
                    <!-- Pending Approvals -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="?page=pending_users" class="btn btn-warning btn-block h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bx bx-time mb-2" style="font-size: 2rem;"></i>
                            <span>Pending Approvals</span>
                        </a>
                    </div>
                    
                    <!-- System Logs -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="?page=logs" class="btn btn-info btn-block h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bx bx-receipt mb-2" style="font-size: 2rem;"></i>
                            <span>System Logs</span>
                        </a>
                    </div>
                </div>
                
                <!-- Secondary Actions Row -->
                <div class="row mt-3">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="?page=classes" class="btn btn-outline-primary btn-block h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bx bx-book mb-2" style="font-size: 1.5rem;"></i>
                            <span>Manage Classes</span>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="?page=attendance" class="btn btn-outline-success btn-block h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bx bx-check-square mb-2" style="font-size: 1.5rem;"></i>
                            <span>Attendance</span>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="?page=pending_events" class="btn btn-outline-warning btn-block h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bx bx-calendar-check mb-2" style="font-size: 1.5rem;"></i>
                            <span>Pending Events</span>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="?page=notifications" class="btn btn-outline-info btn-block h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="bx bx-bell mb-2" style="font-size: 1.5rem;"></i>
                            <span>Notifications</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
