<!-- sidebar -->
<nav class="sidebar">
  <div class="menu_content">
    <ul class="menu_items">
      <div class="menu_title menu_dahsboard"></div>

      <!-- Dashboard -->
      <li class="item">
        <a href="?page=dashboard" class="nav_link submenu_item" data-page="dashboard">
          <div>
            <span class="navlink_icon">
              <i class="bx bx-tachometer"></i>
            </span>
            <span class="navlink">Dashboard</span>
          </div>
        </a>
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
          <li><a href="?page=add_events" class="nav_link sublink" data-page="add_events">Add Events</a></li>
          <li><a href="?page=events" class="nav_link sublink" data-page="events">All Events</a></li>
        </ul>
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

      <!-- Settings Submenu -->
      <li class="item">
        <div class="nav_link submenu_item">
          <span class="navlink_icon">
            <i class="bx bx-cog"></i>
          </span>
          <span class="navlink">Settings</span>
          <i class="bx bx-chevron-right arrow-left"></i>
        </div>

        <ul class="menu_items submenu">
          <li><a class="nav_link sublink" data-toggle="modal" data-target="#logoutModal">Logout</a></li>
        </ul>
      </li>

        <!-- Sidebar Open / Close -->
        <div class="bottom_content">
          <div class="bottom expand_sidebar">
            <span> Expand</span>
            <i class='bx bx-log-in' ></i>
          </div>
          <div class="bottom collapse_sidebar">
            <span> Collapse</span>
            <i class='bx bx-log-out'></i>
          </div>
        </div>
      </div>
    </nav>