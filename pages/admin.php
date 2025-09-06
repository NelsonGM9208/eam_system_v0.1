<?php
define('IN_APP', true);
include '../config/auth.php';
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
  <title>Admin Page</title>
  <link rel="stylesheet" href="../assets/css/includes.css" />
</head>

<body data-theme="light">
  <!-- navbar -->
  <?php include "../includes/admin/header.php"; ?>

  <!-- sidebar -->
  <?php include "../includes/admin/sidenav.php"; ?>

  <!-- Main Content -->
  <main class="main-content" id="mainContent">
    <div class="container-fluid">
      <!-- The main content will be loaded here dynamically -->
      <?php
      // Whitelist of allowed pages for security
      $allowed = ['dashboard', 'pending_users', 'users', 'pending_events', 'events', 'classes', 'attendance', 
                  'notifications', 'logs', 'logout'];
      $page = $_GET['page'] ?? 'dashboard';  // Default page to 'overview'
      if (!in_array($page, $allowed)) {
        $page = 'dashboard';  // Prevent directory traversal attacks
      }

      $partial = __DIR__ . "/../includes/admin/{$page}.php";
      if (file_exists($partial)) {
        include $partial;  // Include the requested page
      } else {
        echo '<div class="p-4">Content not found.</div>';
      }
      ?>
    </div>

    <!-- Logout Confirmation Modal -->
    <?php include "../includes/confirm-logout.php"; ?>

    <?php include "../includes/footer.php"; ?>
  </main>

  <!-- Bootstrap JS + jQuery -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
  
    <!-- JavaScript -->
  <script src="../assets/js/script.js?v=<?php echo time(); ?>"></script>
  <script src="../assets/js/dashboard.js?v=<?php echo time(); ?>"></script>
  <script src="../assets/js/pending_users.js?v=<?php echo time(); ?>"></script>
  <script src="../assets/js/users.js?v=<?php echo time(); ?>"></script>
</body>

</html>