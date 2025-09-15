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
                        <i class="bx bx-tachometer"></i> Teacher Dashboard
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i>
                        <strong>Dashboard</strong> - This feature is coming soon!
                    </div>
                    <p>The teacher dashboard will provide you with an overview of your classes, student attendance, and upcoming events.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">My Classes</h5>
                                    <p class="card-text">Manage your assigned classes and sections</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Student Attendance</h5>
                                    <p class="card-text">Track and monitor student attendance</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
