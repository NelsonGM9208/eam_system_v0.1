<?php
/**
 * Pagination Utilities
 * Handles pagination calculations and HTML generation
 */

// Prevent direct access
if (!defined('IN_APP')) {
    header('HTTP/1.0 403 Forbidden');
    exit();
}

/**
 * Calculate pagination variables
 * @param int $totalRecords Total number of records
 * @param int $recordsPerPage Records per page
 * @param int $currentPage Current page number
 * @return array Pagination data
 */
function calculatePagination($totalRecords, $recordsPerPage = 10, $currentPage = 1) {
    $totalPages = ceil($totalRecords / $recordsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $recordsPerPage;
    
    return [
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'offset' => $offset,
        'recordsPerPage' => $recordsPerPage,
        'totalRecords' => $totalRecords
    ];
}

/**
 * Generate pagination HTML
 * @param array $pagination Pagination data from calculatePagination()
 * @param string $urlPattern URL pattern for pagination links
 * @return string HTML for pagination
 */
function generatePagination($pagination, $urlPattern) {
    if ($pagination['totalPages'] <= 1) return '';
    
    $html = '<nav aria-label="Pagination"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($pagination['currentPage'] > 1) {
        $prevPage = $pagination['currentPage'] - 1;
        $html .= "<li class='page-item'><a class='page-link' href='{$urlPattern}{$prevPage}'>&lt;</a></li>";
    }
    
    // Page numbers
    $startPage = max(1, $pagination['currentPage'] - 2);
    $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
    
    if ($startPage > 1) {
        $html .= "<li class='page-item'><a class='page-link' href='{$urlPattern}1'>1</a></li>";
        if ($startPage > 2) {
            $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $activeClass = ($i == $pagination['currentPage']) ? 'active' : '';
        $html .= "<li class='page-item {$activeClass}'><a class='page-link' href='{$urlPattern}{$i}'>{$i}</a></li>";
    }
    
    if ($endPage < $pagination['totalPages']) {
        if ($endPage < $pagination['totalPages'] - 1) {
            $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }
        $html .= "<li class='page-item'><a class='page-link' href='{$urlPattern}{$pagination['totalPages']}'>{$pagination['totalPages']}</a></li>";
    }
    
    // Next button
    if ($pagination['currentPage'] < $pagination['totalPages']) {
        $nextPage = $pagination['currentPage'] + 1;
        $html .= "<li class='page-item'><a class='page-link' href='{$urlPattern}{$nextPage}'>&gt;</a></li>";
    }
    
    $html .= '</ul></nav>';
    
    // Page info
    $html .= "<div class='text-center mt-2'><small class='text-muted'>Page {$pagination['currentPage']} of {$pagination['totalPages']}</small></div>";
    
    return $html;
}

/**
 * Generate simple pagination info text
 * @param array $pagination Pagination data
 * @return string Pagination info text
 */
function getPaginationInfo($pagination) {
    $start = $pagination['offset'] + 1;
    $end = min($pagination['offset'] + $pagination['recordsPerPage'], $pagination['totalRecords']);
    
    return "Showing {$start} to {$end} of {$pagination['totalRecords']} results";
}
?>
