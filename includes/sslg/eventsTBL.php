<div class="container-fluid">
    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 d-flex align-items-center">
                <i class="bx bx-calendar-check mr-2"></i>
                Events List
            </h5>
            <span class="badge badge-light">
                <?php echo $total_records; ?> events
            </span>
        </div>
        <div class="card-body">
            <!-- Search and Filter -->
            <div class="row mb-3">
                <div class="col-md-4 col-sm-12 mb-2">
                    <div class="input-group">
                        <input type="text" class="form-control" id="eventSearch" placeholder="Search events...">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <select class="form-control" id="eventStatusFilter">
                        <option value="">All Status</option>
                        <option value="Upcoming">Upcoming</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Finished">Finished</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <select class="form-control" id="eventTypeFilter">
                        <option value="">All Types</option>
                        <option value="Open">Open</option>
                        <option value="Exclusive">Exclusive</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-12 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="clearEventFilters">
                        <i class="bx bx-x"></i> Clear
                    </button>
                </div>
            </div>

            <!-- Events Table -->
            <div class="table-responsive">
                <table class="table table-hover" id="eventsTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Event Title</th>
                            <th>Description</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($total_records > 0 && $result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $event_id = $row['event_id'];
                                $title = htmlspecialchars($row['title']);
                                $description = htmlspecialchars($row['event_description']);
                                $event_date = formatEventDate($row['event_date']);
                                $start_time = formatDisplayTime($row['start_time']);
                                $end_time = formatDisplayTime($row['end_time']);
                                $location = htmlspecialchars($row['location']);
                                $event_type = htmlspecialchars($row['event_type']);
                                $status = htmlspecialchars($row['event_status']);
                                $creator_name = htmlspecialchars($row['creator_name'] ?? 'Unknown');
                                $creator_role = htmlspecialchars($row['creator_role'] ?? 'Unknown');
                                
                                // Status badge colors
                                $status_badge = '';
                                switch($status) {
                                    case 'Upcoming':
                                        $status_badge = 'badge-warning';
                                        break;
                                    case 'Ongoing':
                                        $status_badge = 'badge-info';
                                        break;
                                    case 'Finished':
                                        $status_badge = 'badge-success';
                                        break;
                                    default:
                                        $status_badge = 'badge-secondary';
                                }
                                
                                // Type badge colors
                                $type_badge = ($event_type == 'Exclusive') ? 'badge-danger' : 'badge-primary';
                                
                                echo "<tr data-event-id='$event_id' data-event-type='$event_type' data-event-status='$status'>
                                    <td><strong>$title</strong></td>
                                    <td>" . (strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description) . "</td>
                                    <td>
                                        <div><strong>$event_date</strong></div>
                                        <small class='text-muted'>$start_time - $end_time</small>
                                    </td>
                                    <td>$location</td>
                                    <td><span class='badge $type_badge'>$event_type</span></td>
                                    <td><span class='badge $status_badge'>$status</span></td>
                                    <td>
                                        <div>$creator_name</div>
                                        <small class='text-muted'>$creator_role</small>
                                    </td>
                                    <td>
                                        <button type='button' class='btn btn-info btn-sm view-event-btn' 
                                                data-toggle='modal' data-target='#viewEventModal' 
                                                data-event-id='$event_id' title='View Details'>
                                            <i class='bx bx-show'></i>
                                        </button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr>
                                <td colspan='8' class='text-center py-4'>
                                    <i class='bx bx-calendar-x' style='font-size: 3rem; color: #6c757d;'></i>
                                    <h5 class='mt-3 text-muted'>No Approved Events</h5>
                                    <p class='text-muted'>There are no approved events to display at the moment.</p>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>