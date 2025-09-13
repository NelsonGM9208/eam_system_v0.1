 <!-- sidebar -->
 <nav class="sidebar">
      <div class="menu_content">
        <ul class="menu_items">
          <div class="menu_title menu_dahsboard"></div>
          <!-- duplicate or remove this li tag if you want to add or remove navlink with submenu -->
          <!-- start -->
          <li class="item">
            <a href="?page=dashboard" class="nav_link">
              <span class="navlink_icon">
                <i class="bx bx-tachometer"></i>
              </span>
              <span class="navlink">Dashboard</span>
            </a>
          </li>
          <!-- end -->

          <!-- duplicate this li tag if you want to add or remove  navlink with submenu -->
          <!-- start -->
          <li class="item">
            <a href="?page=events" class="nav_link">
              <span class="navlink_icon">
                <i class="bx bx-calendar"></i>
              </span>
              <span class="navlink">Events</span>
            </a>
          </li>
          <!-- end -->

            <li class="item">
            <a href="?page=attendance" class="nav_link">
              <span class="navlink_icon">
                <i class="bx bx-check-square"></i>
              </span>
              <span class="navlink">Attendance</span>
            </a>
          </li>

          <li class="item">
            <a href="?page=excuse_letter" class="nav_link">
              <span class="navlink_icon">
                <i class="bx bx-file"></i>
              </span>
              <span class="navlink">Excuse Letter</span>
            </a>
          </li>

            <li class="item">
            <div href="#" class="nav_link submenu_item">
              <span class="navlink_icon">
                <i class="bx bx-cog"></i>
              </span>
              <span class="navlink">Settings</span>
              <i class="bx bx-chevron-right arrow-left"></i>
            </div>

            <ul class="menu_items submenu">
               <a class="nav_link sublink" data-toggle="modal" data-target="#logoutModal">Logout</a>
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