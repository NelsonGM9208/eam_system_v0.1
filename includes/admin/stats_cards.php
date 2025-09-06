<!-- Redesigned Stats Cards with Bottom Margin -->
<div class="row g-3">
  <?php
  $cards = [
      ['title' => 'Users', 'count' => $totalUsers, 'icon' => 'bx bx-user', 'link' => '?page=users', 'color' => 'primary'],
      ['title' => 'Events', 'count' => $totalEvents, 'icon' => 'bx bx-calendar-event', 'link' => '?page=events', 'color' => 'success'],
      ['title' => 'Classes', 'count' => $totalClasses, 'icon' => 'bx bx-book', 'link' => '?page=classes', 'color' => 'info'],
      ['title' => 'Activity Logs', 'count' => $totalLogs, 'icon' => 'bx bx-receipt', 'link' => '?page=logs', 'color' => 'warning'],
      ['title' => 'Attendance Records', 'count' => $totalAttendance, 'icon' => 'bx bx-check-square', 'link' => '?page=attendance', 'color' => 'danger'],
  ];

  foreach ($cards as $card):
  ?>
    <div class="col-md-3 col-sm-6 col-12 mb-3"><!-- added mb-3 here -->
      <div class="card border-<?= $card['color'] ?> shadow-sm h-100">
        <div class="card-body d-flex flex-column justify-content-between">
          <div class="d-flex align-items-center mb-3">
            <i class="<?= $card['icon'] ?> text-<?= $card['color'] ?> bx-lg mr-3"></i>
            <div>
              <h6 class="card-subtitle mb-1 text-muted"><?= $card['title'] ?></h6>
              <h3 class="card-title mb-0"><?= $card['count'] ?></h3>
            </div>
          </div>
          <div class="text-right mt-auto">
            <a href="<?= $card['link'] ?>" class="btn btn-outline-<?= $card['color'] ?> btn-sm">View</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
