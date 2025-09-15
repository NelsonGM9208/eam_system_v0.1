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
                        <i class="bx bx-check-square"></i> Student Attendance
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i>
                        <strong>Attendance Management</strong> - This feature is coming soon!
                    </div>
                    <p>Here you will be able to track and manage student attendance, view attendance patterns, and generate attendance reports.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="bx bx-user-check" style="font-size: 2.5rem;"></i>
                                    <h5 class="card-title mt-2">Present</h5>
                                    <p class="card-text">Track present students</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <i class="bx bx-time" style="font-size: 2.5rem;"></i>
                                    <h5 class="card-title mt-2">Late</h5>
                                    <p class="card-text">Monitor late arrivals</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <i class="bx bx-user-x" style="font-size: 2.5rem;"></i>
                                    <h5 class="card-title mt-2">Absent</h5>
                                    <p class="card-text">Track absent students</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="bx bx-file" style="font-size: 2.5rem;"></i>
                                    <h5 class="card-title mt-2">Excused</h5>
                                    <p class="card-text">Review excuse letters</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bx bx-chart-line text-primary"></i>
                                        Attendance Trends
                                    </h5>
                                    <p class="card-text">View attendance patterns and trends for your classes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bx bx-download text-success"></i>
                                        Export Reports
                                    </h5>
                                    <p class="card-text">Generate and download attendance reports in PDF format</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
