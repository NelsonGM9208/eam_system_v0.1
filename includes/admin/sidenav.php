<!-- includes/sidebar.php -->
<nav class="sidebar">
  <div class="menu_content">
    <ul class="menu_items">
      <div class="menu_title menu_dahsboard"></div>

      <!-- Dashboard -->
      <li class="item">
        <a href="?page=dashboards" class="nav_link submenu_item" data-page="dashboard">
          <div>
            <span class="navlink_icon">
              <i class="bx bx-tachometer"></i>
            </span>
            <span class="navlink">Dashboard</span>
          </div>
        </a>
      </li>

      <!-- Users Submenu -->
      <li class="item">
        <div class="nav_link submenu_item">
          <span class="navlink_icon">
            <i class="bx bx-group"></i>
          </span>
          <span class="navlink">Users</span>
          <i class="bx bx-chevron-right arrow-left"></i>
        </div>

        <ul class="menu_items submenu">
          <li><a href="?page=pending_users" class="nav_link sublink" data-page="pending_users">Pending Users</a></li>
          <li><a href="?page=users" class="nav_link sublink" data-page="users">View Users</a></li>
        </ul>
      </li>

      <!-- Events Submenu -->
      <li class="item">
        <div class="nav_link submenu_item">
          <span class="navlink_icon">
            <i class="bx bx-calendar"></i>
          </span>
          <span class="navlink">Events</span>
          <i class="bx bx-chevron-right arrow-left"></i>
        </div>

        <ul class="menu_items submenu">
          <li><a href="?page=pending_events" class="nav_link sublink" data-page="pending_events">Pending Events</a></li>
          <li>
            <a href="?page=events" class="nav_link sublink" data-page="events">View Events</a>
          </li>
        </ul>
      </li>

      <!-- Classes -->
      <li class="item">
        <a href="?page=classes" class="nav_link submenu_item" data-page="classes">
          <div>
            <span class="navlink_icon">
              <i class="bx bx-book-reader"></i>
            </span>
            <span class="navlink">Classes</span>
          </div>
        </a>
      </li>

      <!-- Attendance -->
      <li class="item">
        <a href="?page=attendance" class="nav_link" data-page="attendance">
          <span class="navlink_icon">
            <i class="bx bx-check-square"></i>
          </span>
          <span class="navlink">Attendance</span>
        </a>
      </li>

      <!-- Notifications -->
      <li class="item">
        <a href="?page=notifications" class="nav_link" data-page="notifications">
          <span class="navlink_icon">
            <i class="bx bx-bell"></i>
          </span>
          <span class="navlink">Notifications</span>
        </a>
      </li>

      <!-- Activity Logs -->
      <li class="item">
        <a href="?page=logs" class="nav_link" data-page="logs">
          <span class="navlink_icon">
            <i class="bx bx-history"></i>
          </span>
          <span class="navlink">Activity Logs</span>
        </a>
      </li>

      <!-- Settings -->
      <li class="item">
        <div class="nav_link submenu_item">
          <span class="navlink_icon">
            <i class="bx bx-cog"></i>
          </span>
          <span class="navlink">Settings</span>
          <i class="bx bx-chevron-right arrow-left"></i>
        </div>


        <ul class="menu_items submenu">
          <li><a href="?page=logout" class="nav_link sublink" data-toggle="modal" data-target="#logoutModal">Logout</a>
          </li>
        </ul>
      </li>
    </ul>

    <!-- Sidebar Expand / Collapse Buttons -->
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