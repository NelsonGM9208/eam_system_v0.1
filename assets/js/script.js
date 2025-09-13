const body = document.querySelector("body");
const darkLight = document.querySelector("#darkLight");
const sidebar = document.querySelector(".sidebar");
const submenuItems = document.querySelectorAll(".submenu_item");
const sidebarOpen = document.querySelector("#sidebarOpen");
const sidebarClose = document.querySelector(".collapse_sidebar");
const sidebarExpand = document.querySelector(".expand_sidebar");
const navLinks = document.querySelectorAll('.nav_link'); // Getting all sidebar links
const navbar = document.querySelector('.navbar'); // Get navbar for fade effect

// Debug: Check if elements are found
console.log('Sidebar element:', sidebar);
console.log('SidebarOpen element:', sidebarOpen);
console.log('Navbar element:', navbar);

// Initialize sidebar state for mobile
function initializeSidebar() {
  // Check if user has a saved preference for sidebar state
  const savedSidebarState = localStorage.getItem('sidebarCollapsed');
  
  if (window.innerWidth <= 768) {
    // On mobile, always start with sidebar closed
    if (sidebar && !sidebar.classList.contains('close')) {
      sidebar.classList.add('close');
      console.log('Added close class to sidebar for mobile');
    }
  } else {
    // On desktop, respect user's saved preference or default to open
    if (savedSidebarState === 'true') {
      // User previously collapsed the sidebar, keep it collapsed
      if (sidebar && !sidebar.classList.contains('close')) {
        sidebar.classList.add('close', 'hoverable');
        console.log('Restored collapsed sidebar state from localStorage');
      }
    } else {
      // Default to open (or user never collapsed it)
      if (sidebar && sidebar.classList.contains('close')) {
        sidebar.classList.remove('close', 'hoverable');
        console.log('Restored expanded sidebar state from localStorage');
      }
    }
  }
}

// Initialize modal header dimming
function initializeModalHeaderDimming() {
  console.log('Initializing modal header dimming...');
  
  // Configure all modals to prevent scrollbar removal and page bounce
  $('.modal').each(function() {
    $(this).modal({
      backdrop: true,
      keyboard: true,
      focus: true,
      show: false
    });
  });
  
  // OVERRIDE Bootstrap's modal behavior completely
  // Prevent Bootstrap from removing scrollbar
  const originalModal = $.fn.modal;
  $.fn.modal = function(options) {
    const result = originalModal.call(this, options);
    
    // Force scrollbar to stay visible
    $('html').css('overflow-y', 'scroll');
    $('body').css({
      'overflow-y': 'scroll',
      'padding-right': '0px',
      'margin-right': '0px'
    });
    
    return result;
  };
  
  // Use Bootstrap's own modal-open class management
  // Listen for modal show events
  $(document).on('show.bs.modal', '.modal', function() {
    console.log('Modal opening - Bootstrap will add modal-open class');
    
    // AGGRESSIVE: Force scrollbar to stay visible
    $('html').css('overflow-y', 'scroll');
    $('body').css({
      'overflow-y': 'scroll',
      'padding-right': '0px',
      'margin-right': '0px'
    });
    
    // IMMEDIATE header dimming when button is clicked (before modal loads)
    $('.navbar').css({
      'opacity': '0.5',
      'filter': 'brightness(0.7)'
    });
    console.log('Applied header dimming immediately on modal show');
    
    // Bootstrap automatically adds modal-open class, we just need to ensure our CSS works
    
    // FORCE modal z-index immediately
    $('.modal.show').css({
      'z-index': '9999',
      'position': 'fixed'
    });
    $('.modal-dialog').css({
      'z-index': '10000',
      'position': 'relative'
    });
    $('.modal-backdrop').css({
      'z-index': '9998',
      'position': 'fixed'
    });
    console.log('FORCED modal z-index with JavaScript');
  });
  
  // Listen for modal hide events
  $(document).on('hide.bs.modal', '.modal', function() {
    console.log('Modal closing - Bootstrap will remove modal-open class');
    
    // AGGRESSIVE: Keep scrollbar visible even after modal closes
    $('html').css('overflow-y', 'scroll');
    $('body').css({
      'overflow-y': 'scroll',
      'padding-right': '0px',
      'margin-right': '0px'
    });
    
    // Bootstrap automatically removes modal-open class
  });
  
  // Also handle when modal is shown/hidden
  $(document).on('shown.bs.modal', '.modal', function() {
    console.log('Modal shown - checking for modal-open class');
    console.log('Body has modal-open class:', $('body').hasClass('modal-open'));
    
    // FORCE modal z-index again when shown
    $('.modal.show').css({
      'z-index': '9999',
      'position': 'fixed'
    });
    $('.modal-dialog').css({
      'z-index': '10000',
      'position': 'relative'
    });
    $('.modal-backdrop').css({
      'z-index': '9998',
      'position': 'fixed'
    });
    console.log('FORCED modal z-index again when shown');
    
    // Header dimming is now handled immediately in show.bs.modal event
    console.log('Modal fully shown - header already dimmed');
  });
  
  $(document).on('hidden.bs.modal', '.modal', function() {
    console.log('Modal hidden - checking for modal-open class');
    console.log('Body has modal-open class:', $('body').hasClass('modal-open'));
    
    // AGGRESSIVE: Keep scrollbar visible even after modal is completely hidden
    $('html').css('overflow-y', 'scroll');
    $('body').css({
      'overflow-y': 'scroll',
      'padding-right': '0px',
      'margin-right': '0px'
    });
    
    // Restore header immediately when modal is hidden
    $('.navbar').css({
      'opacity': '1',
      'filter': 'brightness(1)'
    });
    console.log('Restored header brightness when modal hidden');
  });
  
  // Force check every 100ms for modal state - SCROLLBAR ONLY
  setInterval(function() {
    // ALWAYS force scrollbar to be visible
    $('html').css('overflow-y', 'scroll');
    $('body').css({
      'overflow-y': 'scroll',
      'padding-right': '0px',
      'margin-right': '0px'
    });
    
    if ($('.modal.show').length > 0) {
      // Modal is showing - ensure body has modal-open class and add custom class
      if (!$('body').hasClass('modal-open')) {
        console.log('Modal is showing but body missing modal-open class - adding it');
        $('body').addClass('modal-open');
      }
      // Add custom class for header dimming
      if (!$('body').hasClass('modal-active')) {
        console.log('Adding modal-active class for header dimming');
        $('body').addClass('modal-active');
      }
      
      // REMOVED: Header dimming from continuous monitoring to prevent flashing
      // Header dimming is now handled only by event handlers
    } else {
      // No modals showing - remove classes and restore header
      if ($('body').hasClass('modal-open')) {
        console.log('No modals showing but body has modal-open class - removing it');
        $('body').removeClass('modal-open');
      }
      if ($('body').hasClass('modal-active')) {
        console.log('Removing modal-active class');
        $('body').removeClass('modal-active');
      }
      
      // REMOVED: Header restoration from continuous monitoring to prevent flashing
      // Header restoration is now handled only by event handlers
    }
  }, 100);
}

// Re-initialize on window resize
window.addEventListener('resize', initializeSidebar);

// Header fade on scroll functionality
let lastScrollTop = 0;
let scrollTimeout;

function handleScroll() {
  const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
  
  // Clear existing timeout
  clearTimeout(scrollTimeout);
  
  // If at the top, always show header
  if (scrollTop <= 10) {
    navbar.classList.remove('fade-out');
    return;
  }
  
  // If scrolling down, fade out header
  if (scrollTop > lastScrollTop && scrollTop > 100) {
    navbar.classList.add('fade-out');
  } 
  // If scrolling up, show header
  else if (scrollTop < lastScrollTop) {
    navbar.classList.remove('fade-out');
  }
  
  lastScrollTop = scrollTop;
  
  // Set timeout to show header when idle
  scrollTimeout = setTimeout(() => {
    navbar.classList.remove('fade-out');
  }, 1500); // Show header after 1.5 seconds of no scrolling
}

// Add scroll event listener - DISABLED: Header should not fade on scroll
// window.addEventListener('scroll', handleScroll, { passive: true });

// Sidebar open/close functionality
sidebarOpen.addEventListener("click", () => {
  console.log('Hamburger menu clicked!');
  console.log('Current sidebar classes:', sidebar.className);
  sidebar.classList.toggle("close");
  console.log('After toggle - sidebar classes:', sidebar.className);
  console.log('Is mobile?', window.innerWidth <= 768);
  
  // Add/remove sidebar-open class to body for CSS targeting
  if (sidebar.classList.contains('close')) {
    document.body.classList.remove('sidebar-open');
    // Save collapsed state to localStorage (only on desktop)
    if (window.innerWidth > 768) {
      localStorage.setItem('sidebarCollapsed', 'true');
      console.log('Sidebar collapsed via hamburger - state saved to localStorage');
    }
  } else {
    document.body.classList.add('sidebar-open');
    // Save expanded state to localStorage (only on desktop)
    if (window.innerWidth > 768) {
      localStorage.setItem('sidebarCollapsed', 'false');
      console.log('Sidebar expanded via hamburger - state saved to localStorage');
    }
  }
  
  // Force sidebar visibility after toggle
  setTimeout(forceSidebarVisibility, 10);
});

// Close sidebar when clicking on backdrop (mobile)
document.addEventListener('click', (e) => {
  if (window.innerWidth <= 768 && !sidebar.classList.contains('close')) {
    if (!sidebar.contains(e.target) && !sidebarOpen.contains(e.target)) {
      sidebar.classList.add('close');
      document.body.classList.remove('sidebar-open');
    }
  }
});

sidebarClose.addEventListener("click", () => {
  sidebar.classList.add("close", "hoverable");
  document.body.classList.remove('sidebar-open');
  // Save collapsed state to localStorage
  localStorage.setItem('sidebarCollapsed', 'true');
  console.log('Sidebar collapsed - state saved to localStorage');
});
sidebarExpand.addEventListener("click", () => {
  sidebar.classList.remove("close", "hoverable");
  document.body.classList.add('sidebar-open');
  // Save expanded state to localStorage
  localStorage.setItem('sidebarCollapsed', 'false');
  console.log('Sidebar expanded - state saved to localStorage');
});

// Sidebar hoverable effect
sidebar.addEventListener("mouseenter", () => {
  if (sidebar.classList.contains("hoverable")) {
    sidebar.classList.remove("close");
    document.body.classList.add('sidebar-open');
  }
});
sidebar.addEventListener("mouseleave", () => {
  if (sidebar.classList.contains("hoverable")) {
    sidebar.classList.add("close");
    document.body.classList.remove('sidebar-open');
  }
});

let currentTheme = localStorage.getItem("theme") || "light";

// Initialize on page load
if (currentTheme === "dark") {
  body.classList.add("dark");
  darkLight.classList.replace("bx-sun", "bx-moon");
} else {
  // Light theme is default, so no 'dark' class
}

// Initialize sidebar-open class based on current sidebar state
if (!sidebar.classList.contains('close')) {
  document.body.classList.add('sidebar-open');
} else {
  document.body.classList.remove('sidebar-open');
}

// Force sidebar to be visible and clickable - AGGRESSIVE
function forceSidebarVisibility() {
  if (sidebar) {
    sidebar.style.opacity = '1';
    sidebar.style.filter = 'none';
    sidebar.style.pointerEvents = 'auto';
    sidebar.style.backgroundColor = 'var(--white-color)';
    
    // Force all sidebar elements to be visible
    const sidebarElements = sidebar.querySelectorAll('*');
    sidebarElements.forEach(element => {
      element.style.opacity = '1';
      element.style.filter = 'none';
      element.style.pointerEvents = 'auto';
    });
  }
}

// Run immediately and on interval to ensure sidebar stays visible
forceSidebarVisibility();
setInterval(forceSidebarVisibility, 100);

// Toggle theme
darkLight.addEventListener("click", () => {
  if (currentTheme === "light") {
    body.classList.add("dark");
    darkLight.classList.replace("bx-sun", "bx-moon");
    currentTheme = "dark";
  } else {
    body.classList.remove("dark");
    darkLight.classList.replace("bx-moon", "bx-sun");
    currentTheme = "light";
  }
  localStorage.setItem("theme", currentTheme);
});


// Submenu toggle
submenuItems.forEach((item, index) => {
  item.addEventListener("click", () => {
    item.classList.toggle("show_submenu");
    submenuItems.forEach((item2, index2) => {
      if (index !== index2) {
        item2.classList.remove("show_submenu");
      }
    });
  });
});

// Highlight active link on page load
  const currentPage = new URLSearchParams(window.location.search).get("page") || "dashboard";
  navLinks.forEach(link => {
    const linkPage = new URLSearchParams(link.getAttribute("href")).get("page");
    if (currentPage === linkPage) {
      link.classList.add("active");

      // also expand its parent submenu
      let parentItem = link.closest("ul.submenu");
      if (parentItem) {
        let submenuItem = parentItem.previousElementSibling;
        if (submenuItem && submenuItem.classList.contains("submenu_item")) {
          submenuItem.classList.add("show_submenu");
        }
      }
    }
  });


// Adjust layout based on screen size - REMOVED
// This was forcing sidebar state and overriding user preferences
// Now handled by initializeSidebar() which respects localStorage

// Set active link based on current page
function setActiveLink() {
  const currentPage = new URLSearchParams(window.location.search).get("page") || "dashboard";
  navLinks.forEach(link => {
    const linkPage = new URLSearchParams(link.getAttribute("href")).get("page");
    if (currentPage === linkPage) {
      link.classList.add("active");
    }
  });
}

// Removed adjustMainContent - now handled by initializeSidebar on resize

// setActiveLink will be called in the main DOMContentLoaded listener below

/**
 * AJAX loader + nav state management.
 * - Delegated click handler for .nav_link anchors with data-page or href ?page=
 * - Uses fetch to load includes/admin/<page>.php and inject into #mainContent .container-fluid
 * - Updates history and active classes. Works with initial PHP server-render too.
 */

(function () {
  const mainWrapper = document.querySelector('#mainContent .container-fluid');
  if (!mainWrapper) return;

  function getPageFromUrl() {
    const p = new URLSearchParams(location.search).get('page');
    return p ? p : 'dashboard';
  }

  function setActiveNav(page) {
    document.querySelectorAll('.nav_link').forEach(a => a.classList.remove('active'));
    // match by data-page first, fallback to href query
    let active = document.querySelector(`.nav_link[data-page="${CSS.escape(page)}"]`);
    if (!active) {
      active = Array.from(document.querySelectorAll('.nav_link')).find(a => {
        try {
          const u = new URL(a.getAttribute('href') || '', location.href);
          return u.searchParams.get('page') === page;
        } catch (e) {
          return false;
        }
      });
    }
    if (active) active.classList.add('active');
    // open parent submenu if needed
    const parentSub = active && active.closest('.submenu');
    if (parentSub) {
      let submenuItem = parentSub.previousElementSibling;
      if (submenuItem && submenuItem.classList.contains('submenu_item')) {
        submenuItem.classList.add('show_submenu');
      }
    }
  }

  async function loadFragment(page, push = true) {
    const fragmentUrl = new URL(`../includes/admin/${page}.php`, location.href).toString();
    try {
      const res = await fetch(fragmentUrl, { cache: 'no-store' });
      if (!res.ok) throw new Error('Not found');
      const html = await res.text();
      mainWrapper.innerHTML = html;
      setActiveNav(page);
      if (push) history.pushState({ page }, '', '?page=' + page);
      // call optional hook if fragments need JS init
      if (typeof window.onFragmentLoaded === 'function') window.onFragmentLoaded(page);
      
      // Reinitialize modals after content is loaded
      if (page === 'users') {
        console.log('Users page loaded, checking for modals...');
        // The modals should work automatically with event delegation now
      }
    } catch (err) {
      mainWrapper.innerHTML = '<div class="p-4">Content could not be loaded.</div>';
      console.error(err);
    }
  }

  // Delegated click handler for nav links
  document.addEventListener('click', function (e) {
    const a = e.target.closest('a.nav_link');
    if (!a) return;

    // allow normal behavior for links without ?page and without data-page (e.g. logout or external)
    const dataPage = a.getAttribute('data-page');
    const href = a.getAttribute('href') || '';
    const hrefPage = (() => {
      try {
        const u = new URL(href, location.href);
        return u.searchParams.get('page');
      } catch (e) {
        return null;
      }
    })();

    const page = dataPage || hrefPage;
    if (!page) return; // not a page-navigation link

    // Don't prevent default - let the browser handle navigation normally
    // e.preventDefault();
    // if same page do nothing (or reload fragment)
    if (getPageFromUrl() === page) {
      // still ensure active classes and submenu visibility are correct
      setActiveNav(page);
      return;
    }
    // Use normal navigation instead of AJAX loading
    // loadFragment(page, true);

    // close mobile overlay sidebar if your UI uses it
    document.body.classList.remove('sidebar-open');
  });

  // handle submenus (toggle) using delegated clicks on .has_submenu or .submenu_item
  document.addEventListener('click', function (e) {
    const toggle = e.target.closest('.has_submenu, .submenu_item > .nav_link');
    if (!toggle) return;
    // prevent hijacking real page links inside the submenu toggle
    if (toggle.matches('a') && toggle.getAttribute('data-page')) return;
    e.preventDefault();
    const parent = toggle.closest('.submenu_item');
    if (!parent) return;
    
    // Use CSS classes instead of direct style manipulation
    const isShown = parent.classList.contains('show_submenu');
    if (isShown) {
      parent.classList.remove('show_submenu');
    } else {
      // Close other open submenus first
      document.querySelectorAll('.submenu_item.show_submenu').forEach(item => {
        if (item !== parent) {
          item.classList.remove('show_submenu');
        }
      });
      parent.classList.add('show_submenu');
    }
  });

  // handle back/forward
  window.addEventListener('popstate', function (ev) {
    const page = (ev.state && ev.state.page) || getPageFromUrl();
    loadFragment(page, false);
  });

  // initial set active based on current URL (when page loaded server-side)
  document.addEventListener('DOMContentLoaded', function () {
    const initial = getPageFromUrl();
    setActiveNav(initial);
  });

})();

document.addEventListener("DOMContentLoaded", () => {
  // Initialize theme
    const theme = localStorage.getItem("theme");
  if (theme === "dark") {
    body.classList.add("dark");
    darkLight.classList.replace("bx-sun", "bx-moon");
  }
  
  // Initialize sidebar (already called above, but ensure it runs after DOM is ready)
  initializeSidebar();
  
  // Set active navigation link
  setActiveLink();
  
  // Initialize modal header dimming
  initializeModalHeaderDimming();
  
  // Initialize page-specific functionality on initial load
  initializePageSpecificJS();
  
  // Auto-update event statuses globally (runs on all admin pages)
  initializeEventStatusAutoUpdate();
});

// Global function to initialize automatic event status updates
function initializeEventStatusAutoUpdate() {
  // Only run on admin pages
  if (window.location.pathname.includes('admin.php') || window.location.pathname.includes('pages/admin.php')) {
    console.log('Initializing automatic event status updates...');
    
    // Auto-update event statuses on page load (silently)
    function autoUpdateEventStatuses() {
      $.post('/eam_system_v0.1.1/utils/event_status_updater.php', function(response) {
        try {
          // Handle both string and object responses
          let results;
          if (typeof response === 'string') {
            results = JSON.parse(response);
          } else {
            results = response;
          }
          
          if (results && results.updated > 0) {
            console.log(`Auto-updated ${results.updated} event statuses`);
          }
        } catch (e) {
          console.error('Error parsing auto-update response:', e, 'Response:', response);
        }
      }).fail(function(xhr, status, error) {
        console.error('Failed to auto-update event statuses:', error);
      });
    }
    
    // Run auto-update on page load
    autoUpdateEventStatuses();
    
    // Set up periodic auto-updates every 10 minutes (600,000 ms) for global updates
    setInterval(function() {
      // Only run if the page is still active/visible
      if (document.visibilityState === 'visible' && !document.hidden) {
        autoUpdateEventStatuses();
      }
    }, 600000); // 10 minutes
  }
}

// Global function to initialize page-specific JavaScript
function initializePageSpecificJS() {
  console.log('Initializing page-specific JavaScript...');
  const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
  console.log('Current page detected:', currentPage);
  
  // Initialize based on current page
  switch(currentPage) {
    case 'dashboard':
    case 'dashboards':
      console.log('Initializing dashboard functionality...');
      if (typeof initDashboard === 'function') {
        initDashboard();
      }
      if (typeof initUsers === 'function') {
        initUsers();
      }
      break;
    case 'users':
      console.log('Initializing users functionality...');
      if (typeof initUsers === 'function') {
        initUsers();
      }
      break;
    case 'pending_users':
      console.log('Initializing pending users functionality...');
      if (typeof initPendingUsers === 'function') {
        initPendingUsers();
      }
      break;
    case 'classes':
      console.log('Initializing classes functionality...');
      if (typeof initClasses === 'function') {
        initClasses();
      }
      break;
    case 'events':
      console.log('Initializing events functionality...');
      if (typeof initDashboard === 'function') {
        initDashboard();
      }
      break;
    default:
      console.log('No specific initialization needed for page:', currentPage);
  }
}

