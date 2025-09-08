<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="bx bx-tachometer me-2" style="font-size: 1.5rem;"></i>
                    <h4 class="mb-0">SSLG Dashboard</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h5 class="alert-heading">Welcome to SSLG Portal</h5>
                                <p>This is the SSLG (Supreme Student Leadership Government) dashboard where you can manage student activities and events.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success">
                                <h5 class="alert-heading">Quick Actions</h5>
                                <ul class="mb-0">
                                    <li><a href="?page=pending_events" class="alert-link">Review Pending Events</a></li>
                                    <li><a href="?page=events" class="alert-link">View All Events</a></li>
                                    <li><a href="?page=attendance" class="alert-link">Check Attendance</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Recent Activities</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Activity</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo date('M j, Y'); ?></td>
                                            <td>SSLG Dashboard Access</td>
                                            <td><span class="badge badge-success">Active</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
