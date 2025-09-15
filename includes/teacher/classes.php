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
                        <i class="bx bx-book-reader"></i> My Classes
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i>
                        <strong>Classes Management</strong> - This feature is coming soon!
                    </div>
                    <p>Here you will be able to view and manage your assigned classes, view student rosters, and track class performance.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="bx bx-group text-primary" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Class Roster</h5>
                                    <p class="card-text">View and manage student lists for your classes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="bx bx-chart text-success" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Class Analytics</h5>
                                    <p class="card-text">View attendance trends and performance metrics</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="bx bx-file-blank text-warning" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Class Reports</h5>
                                    <p class="card-text">Generate and export class attendance reports</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
