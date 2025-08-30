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
  adjustMainContent(); // Adjust main content when sidebar toggles
});
sidebarClose.addEventListener("click", () => {
  sidebar.classList.add("close", "hoverable");
  adjustMainContent(); // Adjust main content when sidebar collapses
});
sidebarExpand.addEventListener("click", () => {
  sidebar.classList.remove("close", "hoverable");
  adjustMainContent(); // Adjust main content when sidebar expands
});

// Sidebar hoverable effect
sidebar.addEventListener("mouseenter", () => {
  if (sidebar.classList.contains("hoverable")) {
    sidebar.classList.remove("close");
    adjustMainContent(); // Adjust main content when sidebar expands on hover
  }
});
sidebar.addEventListener("mouseleave", () => {
  if (sidebar.classList.contains("hoverable")) {
    sidebar.classList.add("close");
    adjustMainContent(); // Adjust main content when sidebar collapses on hover out
  }
});

// Dark mode toggle
darkLight.addEventListener("click", () => {
  body.classList.toggle("dark");
  if (body.classList.contains("dark")) {
    darkLight.classList.replace("bx-sun", "bx-moon");
  } else {
    darkLight.classList.replace("bx-moon", "bx-sun");
  }
});

// Submenu toggle functionality
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

// Function to update active link
function setActiveLink() {
  const currentPage = new URLSearchParams(window.location.search).get('page');  // Get current page from query parameter

  navLinks.forEach(link => {
    const linkPage = new URLSearchParams(link.getAttribute('href')).get('page');  // Extract page from link href

    if (currentPage === linkPage) {
      link.classList.add('active');  // Add active class to matching link
    } else {
      link.classList.remove('active');  // Remove active class if not matching
    }
  });
}

// Add click event for nav links to toggle 'active' class
navLinks.forEach(link => {
  link.addEventListener('click', (e) => {
    // Prevent default action (to avoid reloading the page)
    e.preventDefault();

    // Update the URL with the new query parameter
    const page = new URLSearchParams(link.getAttribute('href')).get('page');  // Extract page parameter
    window.history.pushState({}, '', `?page=${page}`);  // Update the URL

    // Set the active class after the URL change
    setActiveLink();  // Call function to update the active class
  });
});


// Adjust layout based on screen size
if (window.innerWidth < 768) {
  sidebar.classList.add("close");
} else {
  sidebar.classList.remove("close");
}

// Adjust the active class based on the current URL on page load (for when page is refreshed)
document.addEventListener('DOMContentLoaded', () => {
  setActiveLink(); // Call the function to set the active class based on the query parameter
});

// Function to adjust the main content layout
function adjustMainContent() {
  const mainContent = document.querySelector('.main-content');
  if (sidebar.classList.contains("close")) {
    mainContent.style.marginLeft = "80px"; // Smaller margin for collapsed sidebar
  } else {
    mainContent.style.marginLeft = "260px"; // Default margin for expanded sidebar
  }
}

window.addEventListener('resize', adjustMainContent); // Listen for window resize
