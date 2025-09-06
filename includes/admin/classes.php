<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
require_once __DIR__ . "/../../utils/index.php";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="bx bx-book-reader"></i> Classes Management
                        </h4>
                        <button class="btn btn-light btn-sm" data-toggle="modal" data-target="#addClassModal">
                            <i class="bx bx-plus"></i> Add Class
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php include __DIR__ . "/views/classes_table_view.php"; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Add Class Modal (doesn't require ID parameter) -->
<?php include __DIR__ . "/modals/add_class.php"; ?>

<!-- Other modals are loaded dynamically via AJAX -->
