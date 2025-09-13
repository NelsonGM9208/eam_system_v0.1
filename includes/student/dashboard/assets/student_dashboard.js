/**
 * Student Dashboard JavaScript
 * Handles dashboard interactions and real-time updates for students
 */

// Wait for jQuery to be available
function initStudentDashboard() {
    if (typeof $ === 'undefined') {
        console.log('jQuery not available, retrying in 100ms...');
        setTimeout(initStudentDashboard, 100);
        return;
    }

    // Check if we're on the student dashboard page
    if (!$('.container-fluid').length || !$('h2:contains("Student Dashboard")').length) {
        console.log('Student Dashboard not found, skipping initialization...');
        return;
    }

    console.log('jQuery available and Student Dashboard found, initializing...');

    $(document).ready(function () {
        // Add subtle hover effects to cards
        $('.card').hover(
            function() {
                $(this).addClass('shadow').removeClass('shadow-sm');
            },
            function() {
                $(this).addClass('shadow-sm').removeClass('shadow');
            }
        );

        // Add subtle click effects to quick action buttons
        $('.btn-block').on('click', function() {
            $(this).addClass('btn-pressed');
            setTimeout(() => {
                $(this).removeClass('btn-pressed');
            }, 100);
        });

 // 30 seconds

        // Add loading animation to statistics cards
        animateStatistics();

        // Initialize real-time updates
        initializeRealTimeUpdates();

        // Add click handlers for events and attendance
        initializeStudentHandlers();
    });
}

/**
 * Animate statistics cards on load
 */
function animateStatistics() {
    $('.card.border-left-primary, .card.border-left-warning, .card.border-left-success, .card.border-left-info').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(20px)'
        }).delay(index * 100).animate({
            'opacity': '1'
        }, 500).css('transform', 'translateY(0)');
    });
}

/**
 * Initialize real-time updates for dashboard
 */
function initializeRealTimeUpdates() {
    // Update time display every minute
    setInterval(function() {
        updateTimeDisplay();
    }, 60000);
    
    // Check for new events every 5 minutes
    setInterval(function() {
        checkForNewEvents();
    }, 300000);
}

/**
 * Update time display
 */
function updateTimeDisplay() {
    const now = new Date();
    const timeString = now.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    
    $('.current-time').text(timeString);
}

/**
 * Check for new events
 */
function checkForNewEvents() {
    console.log('Checking for new events...');
    // This could be expanded to make an AJAX call to check for new events
    // and show notifications to students
}

/**
 * Initialize student-specific handlers
 */
function initializeStudentHandlers() {
    // Add click handlers for event items
    $(document).on('click', '.event-item', function() {
        const eventId = $(this).data('event-id');
        console.log('Event clicked:', eventId);
        viewEventDetails(eventId);
    });
    
    // Add click handlers for attendance items
    $(document).on('click', '.attendance-item', function() {
        const attendanceId = $(this).data('attendance-id');
        console.log('Attendance record clicked:', attendanceId);
        // Could show more details about attendance record
    });
}

/**
 * View event details
 */
function viewEventDetails(eventId) {
    // This would open a modal or navigate to event details page
    console.log('Viewing event details for:', eventId);
    // Implementation would depend on your event details modal/page
}

/**
 * Show notification to student
 */
function showNotification(message, type = 'info') {
    const alertClass = type === 'error' ? 'alert-danger' : 
                      type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    // Insert at the top of the dashboard
    $('.container-fluid').prepend(notification);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        notification.alert('close');
    }, 5000);
}

/**
 * Scroll to personal info section
 */
function scrollToPersonalInfo() {
    const personalInfoSection = document.getElementById('personal-info-section');
    if (personalInfoSection) {
        personalInfoSection.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
        
        // Add highlight effect
        personalInfoSection.style.animation = 'none';
        setTimeout(() => {
            personalInfoSection.style.animation = 'highlight 2s ease-in-out';
        }, 100);
        
        console.log('Scrolled to personal info section');
    } else {
        console.error('Personal info section not found');
    }
}


// Initialize when page loads
initStudentDashboard();
