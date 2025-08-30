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

  <body>
    <!-- navbar -->
    <nav class="navbar">
      <div class="logo_item">
        <i class="bx bx-menu" id="sidebarOpen"></i>
        <img src="../assets/images/logo.png" alt=""></i>Events Attendance Management System
      </div>

      <div class="search_bar">
        <input type="text" placeholder="Search" />
      </div>

      <div class="navbar_content">
        <i class="bi bi-grid"></i>
        <i class='bx bx-sun' id="darkLight"></i>
        <i class='bx bx-bell'></i>
        <img src="../assets/images/profile.jpg" alt="" class="profile" />
      </div>
    </nav>

    <!-- sidebar -->
    <nav class="sidebar">
      <div class="menu_content">
        <ul class="menu_items">
          <div class="menu_title menu_dahsboard"></div>
          <!-- duplicate or remove this li tag if you want to add or remove navlink with submenu -->
          <!-- start -->
          <li class="item">
            <a href="?page=dashboard" class="nav_link submenu_item">
              <div>
                <span class="navlink_icon">
                  <i class="bx bx-tachometer"></i>
                </span>
                <span class="navlink">Dashboard</span>
              </div>
            </a>
          </li>
          <!-- end -->

          <!-- duplicate this li tag if you want to add or remove  navlink with submenu -->
          <!-- start -->
          <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="bx bx-group"></i>
              </span>
              <span class="navlink">Users</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>

            <ul class="menu_items submenu">
              <a href="?page=pending_users" class="nav_link sublink">Pending Users</a>
              <a href="?page=admins" class="nav_link sublink">Admins</a>
              <a href="?page=sslg" class="nav_link sublink">SSLG</a>
              <a href="?page=teachers" class="nav_link sublink">Teachers</a>
              <a href="?page=students" class="nav_link sublink">Students</a>
            </ul>
          </li>

          <li class="item">
            <div class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="bx bx-calendar"></i>
              </span>
              <span class="navlink">Events</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>

            <ul class="menu_items submenu">
              <a href="?page=pending_events" class="nav_link sublink">Pending Events</a>
              <a href="?page=events" class="nav_link sublink">View Events</a>
            </ul>
          </li>

          <li class="item">
            <a href="?page=classes" class="nav_link submenu_item">
              <div>
                <span class="navlink_icon">
                  <i class="bx bx-book-reader"></i>
                </span>
                <span class="navlink">Classes</span>
              </div>
            </a>
          </li>
          <!-- End -->

          <li class="item">
            <a href="?page=attendance" class="nav_link">
              <span class="navlink_icon">
                <i class="bx bx-check-square"></i>
              </span>
              <span class="navlink">Attendance</span>
            </a>
          </li>
          <li class="item">
            <a href="?page=notifications" class="nav_link">
              <span class="navlink_icon">
                <i class="bx bx-bell"></i>
              </span>
              <span class="navlink">Notifications</span>
            </a>
          </li>

          <li class="item">
            <a href="?page=logs" class="nav_link">
              <span class="navlink_icon">
                <i class="bx bx-history"></i>
              </span>
              <span class="navlink">Activity Logs</span>
            </a>
          </li>
          <li class="item">
            <div class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="bx bx-cog"></i>
              </span>
              <span class="navlink">Setting</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>

            <ul class="menu_items submenu">
              <a href="?page=logout" class="nav_link sublink" data-toggle="modal" data-target="#logoutModal">Logout</a>
            </ul>
          </li>

          <!-- Sidebar Open / Close -->
          <div class="bottom_content">
            <div class="bottom expand_sidebar">
              <span> Expand</span>
              <i class='bx bx-log-in'></i>
            </div>
            <div class="bottom collapse_sidebar">
              <span> Collapse</span>
              <i class='bx bx-log-out'></i>
            </div>
          </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
      <div class="container-fluid">
        <!-- The main content will be loaded here dynamically -->
        <?php
        // Whitelist of allowed pages for security
        $allowed = ['dashboard', 'users', 'events', 'logs', 'student', 'teacher'];
        $page = $_GET['page'] ?? 'dashboard';  // Default page to 'overview'
        if (!in_array($page, $allowed)) {
          $page = 'dashboard';  // Prevent directory traversal attacks
        }

        $partial = __DIR__ . "/partials/admin/{$page}.php";
        if (file_exists($partial)) {
          include $partial;  // Include the requested page
        } else {
          echo '<div class="p-4">Content not found.</div>';
        }
        ?>
      </div>
    </main>


    <!-- JavaScript -->
    <script src="../assets/js/script.js"></script>

    <?php include "../includes/confirm-logout.php"; ?>

    <!-- Bootstrap JS + jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

  </body>

  </html>