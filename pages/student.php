<?php
define('IN_APP', true);
require_once '../utils/auth.php';

// Require student role access
requireRole('student');
?>
<!DOCTYPE html>
<!-- Coding by CodingNepal || www.codingnepalweb.com -->
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Boxicons CSS -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <title>SANHS EAMS - Student Page</title>
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="https://mis.sanagustinnhs.net/assets/images/sanhs_logo.png">
  <link rel="stylesheet" href="../assets/css/includes.css" />
</head>

<body>
  <!-- navbar -->
  <?php include "../includes/pages_header.php"; ?>

  <!-- sidebar -->
  <?php include "../includes/student/sidenav.php"; ?>

  <!-- Main Content -->
  <main class="main-content" id="mainContent">
    <div class="container-fluid">
      <!-- The main content will be loaded here dynamically -->
      <?php
      // Whitelist of allowed pages for security
      $allowed = [
        'dashboard',
        'events',
        'attendance',
        'excuse_letter',
        'logout'
      ];
      $page = $_GET['page'] ?? 'dashboard';  // Default page to 'dashboard'
      if (!in_array($page, $allowed)) {
        $page = 'dashboard';  // Prevent directory traversal attacks
      }

      // Try SSLG-specific pages first, then fall back to admin pages
      $student = __DIR__ . "/../includes/student/{$page}.php";

      if (file_exists($student)) {
        include $student;  // Include SSLG-specific page
      } else {
        echo '<div class="p-4">Content not found.</div>';
      }
      ?>
    </div>

    <!-- footer -->
    <?php include "../includes/footer.php"; ?>

    <!-- Logout Confirmation Modal -->
    <?php include "../includes/confirm-logout.php"; ?>

    <!-- Event Details Modal -->
    <?php include "../includes/student/modals/event_details.php"; ?>

    <!-- Bootstrap JS + jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery Validation Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

    <!-- QR Code Scanning Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

    <!-- JavaScript -->
    <script src="../assets/js/script.js?v=<?php echo time(); ?>"></script>
    <script src="../includes/student/dashboard/assets/student_dashboard.js?v=<?php echo time(); ?>"></script>
    <script src="../includes/student/js/events.js?v=<?php echo time(); ?>"></script>
    <script src="../includes/student/js/attendance.js?v=<?php echo time(); ?>"></script>
    <script src="../includes/student/js/excuse_letter.js?v=<?php echo time(); ?>"></script>

</body>

</html>