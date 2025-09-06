<?php
require_once __DIR__ . "/../../config/database.php";

// Check database connection
if (!isset($con) || !$con) {
    echo '<div class="alert alert-danger">Database connection failed.</div>';
    exit;
}

// Get all sections/classes
$query = "SELECT section_id, section_name, grade_level FROM section ORDER BY grade_level, section_name";
$result = mysqli_query($con, $query);

if (!$result) {
    echo '<div class="alert alert-danger">Error loading classes: ' . mysqli_error($con) . '</div>';
    exit;
}

echo '<div class="row">';
while ($row = mysqli_fetch_assoc($result)) {
    echo '<div class="col-md-6 mb-2">';
    echo '<div class="form-check">';
    echo '<input class="form-check-input" type="checkbox" value="' . $row['section_id'] . '" id="class_' . $row['section_id'] . '">';
    echo '<label class="form-check-label" for="class_' . $row['section_id'] . '">';
    echo 'Grade ' . $row['grade_level'] . ' - ' . htmlspecialchars($row['section_name']);
    echo '</label>';
    echo '</div>';
    echo '</div>';
}
echo '</div>';

if (mysqli_num_rows($result) === 0) {
    echo '<div class="alert alert-info">No classes found.</div>';
}
?>