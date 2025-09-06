const body = document.querySelector("body");
const darkLight = document.querySelector("#darkLight");
const sidebar = document.querySelector(".sidebar");
const submenuItems = document.querySelectorAll(".submenu_item");
const sidebarOpen = document.querySelector("#sidebarOpen");
const sidebarClose = document.querySelector(".collapse_sidebar");
const sidebarExpand = document.querySelector(".expand_sidebar");
const navLinks = document.querySelectorAll('.nav_link'); // Getting all sidebar links

// Sidebar open/close functionality
sidebarOpen.addEventListener("click", () => {
  sidebar.classList.toggle("close");
});
sidebarClose.addEventListener("click", () => {
  sidebar.classList.add("close", "hoverable");
});
sidebarExpand.addEventListener("click", () => {
  sidebar.classList.remove("close", "hoverable");
});

// Sidebar hoverable effect
sidebar.addEventListener("mouseenter", () => {
  if (sidebar.classList.contains("hoverable")) {
    sidebar.classList.remove("close");
  }
});
sidebar.addEventListener("mouseleave", () => {
  if (sidebar.classList.contains("hoverable")) {
    sidebar.classList.add("close");
  }
});

let currentTheme = localStorage.getItem("theme") || "light";

// Initialize on page load
if (currentTheme === "dark") {
  body.classList.add("dark");
  darkLight.classList.replace("bx-sun", "bx-moon");
} else {
  // Light theme is default, so no 'dark' class
  body.classList.remove("dark");
  darkLight.classList.replace("bx-moon", "bx-sun");
}

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


// Adjust layout based on screen size
if (window.innerWidth < 768) {
  sidebar.classList.add("close");
} else {
  sidebar.classList.remove("close");
}

// Adjust main content based on screen size
function adjustMainContent() {
  if (window.innerWidth < 768) {
    sidebar.classList.add("close");
  } else {
    sidebar.classList.remove("close");
  }
}

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

window.addEventListener('resize', adjustMainContent); // Listen for window resize

document.addEventListener('DOMContentLoaded', () => {
  setActiveLink();
});

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

    e.preventDefault();
    // if same page do nothing (or reload fragment)
    if (getPageFromUrl() === page) {
      // still ensure active classes and submenu visibility are correct
      setActiveNav(page);
      return;
    }
    loadFragment(page, true);

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
  const theme = localStorage.getItem("theme");
  if (theme === "dark") {
    body.classList.add("dark");
    darkLight.classList.replace("bx-sun", "bx-moon");
  }
});

