<?php
/**
 * UI Utilities
 * Handles UI components, styling, and formatting
 */

// Prevent direct access
if (!defined('IN_APP')) {
    header('HTTP/1.0 403 Forbidden');
    exit();
}

/**
 * Get badge class based on value and type
 * @param string $value Value to determine badge class
 * @param string $type Badge type (role, status, verification, type)
 * @return string Badge CSS class
 */
function getBadgeClass($value, $type = 'default') {
    $value = strtolower($value);
    
    switch ($type) {
        case 'role':
            switch($value) {
                case 'student': return 'badge-info';
                case 'teacher': return 'badge-warning';
                case 'sslg': return 'badge-success';
                case 'admin': return 'badge-danger';
                default: return 'badge-secondary';
            }
        case 'status':
            switch($value) {
                case 'active':
                case 'approved':
                case 'upcoming': return 'badge-success';
                case 'pending': return 'badge-warning';
                case 'inactive':
                case 'rejected':
                case 'finished': return 'badge-danger';
                case 'ongoing': return 'badge-primary';
                default: return 'badge-secondary';
            }
        case 'verification':
            switch($value) {
                case 'verified': return 'badge-success';
                case 'notverified': return 'badge-danger';
                default: return 'badge-secondary';
            }
        case 'type':
            switch($value) {
                case 'open': return 'badge-success';
                case 'exclusive': return 'badge-warning';
                default: return 'badge-secondary';
            }
        case 'account_status':
            switch($value) {
                case 'active': return 'badge-success';
                case 'deactivated': return 'badge-danger';
                default: return 'badge-secondary';
            }
        default:
            return 'badge-secondary';
    }
}

/**
 * Format date string
 * @param string $dateString Date string
 * @param string $format Date format (default: M d, Y)
 * @return string Formatted date
 */
function formatDate($dateString, $format = 'M d, Y') {
    if (!$dateString) return 'N/A';
    return date($format, strtotime($dateString));
}

/**
 * Get profile photo HTML
 * @param string $photoPath Path to profile photo
 * @param int $size Photo size in pixels
 * @return string HTML for profile photo
 */
function getProfilePhoto($photoPath, $size = 35) {
    if ($photoPath) {
        return "<img src='{$photoPath}' class='rounded-circle shadow-sm' width='{$size}' height='{$size}' alt='Profile' style='object-fit: cover;'>";
    }
    return "<div class='rounded-circle bg-light d-flex align-items-center justify-content-center' style='width: {$size}px; height: {$size}px;'><i class='bx bx-user-circle text-muted'></i></div>";
}

/**
 * Generate action buttons HTML
 * @param array $actions Array of action types
 * @param int $id Entity ID
 * @param string $entityType Entity type
 * @return string HTML for action buttons
 */
function generateActionButtons($actions, $id, $entityType = 'user', $additionalData = []) {
    $html = '<div class="btn-group" role="group">';
    
    foreach ($actions as $action) {
        $buttonClass = '';
        $icon = '';
        $title = '';
        
        switch ($action) {
            case 'view':
                $buttonClass = 'btn-info';
                $icon = 'bx-show';
                $title = 'View Details';
                break;
            case 'edit':
                $buttonClass = 'btn-warning';
                $icon = 'bx-edit';
                $title = 'Edit';
                break;
            case 'delete':
                $buttonClass = 'btn-danger';
                $icon = 'bx-trash';
                $title = 'Remove';
                break;
            case 'approve':
                $buttonClass = 'btn-success';
                $icon = 'bx-check';
                $title = 'Approve';
                break;
            case 'reject':
                $buttonClass = 'btn-danger';
                $icon = 'bx-x';
                $title = 'Reject';
                break;
        }
        
        if ($buttonClass) {
            // Build data attributes
            $dataAttributes = "data-id='{$id}' data-entity='{$entityType}'";
            foreach ($additionalData as $key => $value) {
                $dataAttributes .= " data-{$key}='" . htmlspecialchars($value) . "'";
            }
            
            $html .= "<button class='btn {$buttonClass} btn-sm {$action}-btn' {$dataAttributes} title='{$title}'>
                        <i class='bx {$icon}'></i>
                      </button>";
        }
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Generate search and filter form
 * @param array $config Search configuration
 * @return string HTML for search form
 */
function generateSearchForm($config) {
    $html = '<div class="row mb-3">';
    
    if (isset($config['search'])) {
        $html .= '<div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" id="entitySearch" placeholder="' . sanitizeOutput($config['search']['placeholder']) . '">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                    </div>
                  </div>';
    }
    
    if (isset($config['filters'])) {
        foreach ($config['filters'] as $filter) {
            $html .= '<div class="col-md-3">
                        <select class="form-control" id="' . sanitizeOutput($filter['id']) . '">
                            <option value="">' . sanitizeOutput($filter['placeholder']) . '</option>';
            
            foreach ($filter['options'] as $value => $label) {
                $html .= '<option value="' . sanitizeOutput($value) . '">' . sanitizeOutput($label) . '</option>';
            }
            
            $html .= '</select></div>';
        }
    }
    
    if (isset($config['bulkAction'])) {
        $html .= '<div class="col-md-3">
                    <button type="button" class="btn btn-success btn-sm" id="bulkActionBtn" disabled>
                        <i class="bx bx-check-double"></i> ' . sanitizeOutput($config['bulkAction']['text']) . '
                    </button>
                  </div>';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Generate loading spinner HTML
 * @param string $message Loading message
 * @param string $size Spinner size (sm, md, lg)
 * @return string HTML for loading spinner
 */
function generateLoadingSpinner($message = 'Loading...', $size = 'md') {
    $sizeClass = $size === 'sm' ? 'bx-sm' : ($size === 'lg' ? 'bx-lg' : '');
    return "<div class='text-center p-4'>
                <i class='bx bx-loader-alt bx-spin {$sizeClass}' style='font-size: 2rem;'></i>
                <p class='mt-2 mb-0'>{$message}</p>
            </div>";
}

/**
 * Generate empty state HTML
 * @param string $icon Icon class
 * @param string $title Title text
 * @param string $message Message text
 * @param string $actionButton Optional action button HTML
 * @return string HTML for empty state
 */
function generateEmptyState($icon, $title, $message, $actionButton = '') {
    return "<div class='text-center py-5'>
                <i class='{$icon}' style='font-size: 3rem; color: #6c757d;'></i>
                <h5 class='mt-3 text-muted'>{$title}</h5>
                <p class='text-muted'>{$message}</p>
                {$actionButton}
            </div>";
}
?>
