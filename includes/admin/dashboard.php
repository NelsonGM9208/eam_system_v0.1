<?php
// Define IN_APP to allow access to utilities
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Include utilities
require_once __DIR__ . "/../../utils/index.php";
// include database connection
require_once __DIR__ . "/../../config/database.php";

// Query statistics
$totalUsers = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM users"))['total'] ?? 0;
$totalEvents = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM events"))['total'] ?? 0;
$totalAttendance = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM attendance"))['total'] ?? 0;
$totalClasses = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM section"))['total'] ?? 0;
$totalLogs = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM logs"))['total'] ?? 0;
?>

<div class="dashboard p-4">
  <h2 class="mb-4">Admin Dashboard</h2>

  <?php // Display statistics cards 
    include __DIR__."/stats_cards.php";?>

  <!-- Tables -->
  <?php include __DIR__ . "/usersTBL.php"; ?>

  <?php include __DIR__ . "/eventsTBL.php"; ?>

</div>
</div>
</div>