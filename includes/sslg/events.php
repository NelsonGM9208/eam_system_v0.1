<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

// Check database connection
if (!isset($con) || !$con) {
    echo "<div class='alert alert-danger'>Database connection failed.</div>";
    exit;
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// Get total number of approved events
$total_query = "SELECT COUNT(*) as total FROM events WHERE approval_status = 'Approved'";
$total_result = mysqli_query($con, $total_query);
if (!$total_result) {
    echo "<div class='alert alert-danger'>Database error: " . $con->error . "</div>";
    exit;
}
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Ensure page is within valid range
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $records_per_page;
}

// Only run the query if there are events
if ($total_records > 0) {
    // Get approved events with pagination
    $query = "SELECT e.*, 
              CONCAT(u.firstname, ' ', u.lastname) as creator_name,
              u.role as creator_role
              FROM events e 
              LEFT JOIN users u ON e.created_by = u.user_id 
              WHERE e.approval_status = 'Approved'
              ORDER BY e.event_date DESC, e.start_time ASC
              LIMIT ?, ?";

    $stmt = $con->prepare($query);
    if (!$stmt) {
        echo "<div class='alert alert-danger'>Database error: " . $con->error . "</div>";
        exit;
    }

    $stmt->bind_param("ii", $offset, $records_per_page);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = null;
}
?>

     <?php include __DIR__ . "/eventsTBL.php"; ?>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Events pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                <i class="bx bx-chevron-left"></i> Previous
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                Next <i class="bx bx-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <div class="text-center mt-2">
                    <small class="text-muted">
                        Showing page <?php echo $page; ?> of <?php echo $total_pages; ?> 
                        (<?php echo $total_records; ?> total approved events)
                    </small>
                </div>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Event Modal -->
<?php include __DIR__ . "/modals/view_event_modal.php"; ?>

<!-- Include JavaScript for Events functionality -->
<script src="/eam_system_v0.1.1/includes/sslg/js/events.js"></script>
