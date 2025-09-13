<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . "/../../utils/index.php";

// Get database connection using utils
$con = getDatabaseConnection();

// Fetch all available sections for exclusive events
$sections_query = "SELECT section_id, grade, section FROM section ORDER BY grade, section";
$sections_result = mysqli_query($con, $sections_query);
$all_sections = [];
if ($sections_result) {
    while ($section = mysqli_fetch_assoc($sections_result)) {
        $all_sections[] = $section;
    }
}

?>

<div class="container-fluid">
    <!-- Add Event Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="bx bx-calendar-plus me-2" style="font-size: 1.5rem;"></i>
                    <h4 class="mb-0">Add New Event</h4>
                </div>
                <div class="card-body">

                    <form id="addEventForm">
                        <input type="hidden" name="action" value="add">

                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label for="eventTitle">Event Title *</label>
                                    <input type="text" class="form-control" id="eventTitle" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label for="eventType">Event Type *</label>
                                    <select class="form-control" id="eventType" name="event_type" required>
                                        <option value="">Select Type</option>
                                        <option value="Open">Open</option>
                                        <option value="Exclusive">Exclusive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="eventDescription">Description</label>
                            <textarea class="form-control" id="eventDescription" name="event_description"
                                rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label for="eventDate">Event Date *</label>
                                    <input type="date" class="form-control" id="eventDate" name="event_date" required>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="form-group">
                                    <label for="startTime">Start Time *</label>
                                    <input type="time" class="form-control" id="startTime" name="start_time" required>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="form-group">
                                    <label for="endTime">End Time *</label>
                                    <input type="time" class="form-control" id="endTime" name="end_time" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="eventLocation">Location *</label>
                            <input type="text" class="form-control" id="eventLocation" name="location" required>
                        </div>

                        <div class="form-group">
                            <label for="absPenalty">Absence Penalty</label>
                            <input type="number" class="form-control" id="absPenalty" name="abs_penalty" min="0"
                                step="0.01" placeholder="Penalty amount in pesos (₱)">
                        </div>

                        <!-- Section Selection for Exclusive Events -->
                        <div class="form-group" id="sectionSelectionGroup"
                            style="display: none; border: 2px solid #007bff; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                            <label class="font-weight-bold text-primary">
                                <i class="bx bx-group"></i> Select Sections for Exclusive Event
                            </label>
                            <small class="form-text text-muted mb-3">
                                Choose which sections can participate in this exclusive event.
                            </small>
                            <div class="row" id="sectionCheckboxes">
                                <?php if (!empty($all_sections)): ?>
                                    <?php foreach ($all_sections as $section): ?>
                                        <div class="col-md-6 col-lg-4 mb-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input section-checkbox"
                                                    id="section_<?php echo $section['section_id']; ?>" name="selected_classes[]"
                                                    value="<?php echo $section['section_id']; ?>">
                                                <label class="custom-control-label"
                                                    for="section_<?php echo $section['section_id']; ?>">
                                                    <i class="bx bx-group text-primary"></i>
                                                    <?php echo htmlspecialchars($section['grade'] . ' - ' . $section['section']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle"></i> No sections available. Please add sections
                                            first.
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-plus"></i> Create Event
                            </button>
                            <button type="reset" class="btn btn-secondary ml-2">
                                <i class="bx bx-refresh"></i> Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Creation Confirmation Modal -->
    <?php include __DIR__ . "/modals/event_confirmation_modal.php"; ?>

    <!-- Pending Events Table -->
     <?php include __DIR__ . "/pending_eventsTBL.php"; ?>

<!-- Include JavaScript for Add Events functionality -->
<script src="/eam_system_v0.1.1/includes/sslg/js/add_events.js"></script>