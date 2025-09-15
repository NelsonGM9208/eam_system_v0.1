<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
require_once __DIR__ . "/../../utils/index.php";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="bx bx-calendar"></i> Events
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i>
                        <strong>Events Management</strong> - This feature is coming soon!
                    </div>
                    <p>Here you will be able to view events for your classes, monitor event attendance, and access event-related tools.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bx bx-calendar-event text-primary"></i>
                                        Upcoming Events
                                    </h5>
                                    <p class="card-text">View events scheduled for your classes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bx bx-qr-scan text-success"></i>
                                        Event QR Codes
                                    </h5>
                                    <p class="card-text">Access QR codes for event attendance</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bx bx-time text-warning"></i>
                                        Live Attendance
                                    </h5>
                                    <p class="card-text">Monitor real-time attendance during events</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bx bx-bar-chart text-info"></i>
                                        Event Reports
                                    </h5>
                                    <p class="card-text">Generate attendance reports for events</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
