<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
require_once __DIR__ . "/../../utils/index.php";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Include the existing eventsTBL.php which has all the database logic and table structure -->
            <?php include __DIR__ . '/eventsTBL.php'; ?>
        </div>
    </div>
</div>

