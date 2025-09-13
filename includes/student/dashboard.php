<?php
/**
 * Student Dashboard Main File
 * This file is included when students access the dashboard page
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Include the dashboard
include __DIR__ . "/dashboard/dashboard.php";
?>

<!-- View Event Modal -->
<div class="modal fade" id="viewEventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- Modal content will be loaded dynamically -->
        </div>
    </div>
</div>

