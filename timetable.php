<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$active = 'timetable';

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Fetch this student's class timetable entries
$rows = [];
$stmt = mysqli_prepare($conn, "SELECT day, time_slot, subject, teacher FROM timetable
                                WHERE class = ?
                                ORDER BY FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') ASC,
                                         time_slot ASC");
mysqli_stmt_bind_param($stmt, 's', $student_class);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) { $rows[] = $row; }
}

// Build a unique, ordered list of time slots (rows of the grid)
$time_slots = [];
foreach ($rows as $r) {
    if (!in_array($r['time_slot'], $time_slots, true)) {
        $time_slots[] = $r['time_slot'];
    }
}

// Build a quick lookup: grid[time_slot][day] = ['subject' => ..., 'teacher' => ...]
$grid = [];
foreach ($rows as $r) {
    $grid[$r['time_slot']][$r['day']] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable - Forces Academy LMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <?php require 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-heading">
            <h2>Timetable</h2>
            <p>Your weekly class schedule<?php echo $student_class ? ' — ' . htmlspecialchars($student_class) : ''; ?>.</p>
        </div>

        <div class="card notice-card">
            <div class="table-responsive">
                <?php if (empty($time_slots)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-calendar-week" style="font-size:2.5rem;"></i>
                        <p class="mt-3 mb-0">No timetable has been published for your class yet.</p>
                    </div>
                <?php else: ?>
                    <table class="table table-bordered align-middle mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 130px;">Time Slot</th>
                                <?php foreach ($days as $day): ?>
                                    <th style="min-width: 150px;"><?php echo $day; ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($time_slots as $slot): ?>
                                <tr>
                                    <td class="fw-semibold text-nowrap"><?php echo htmlspecialchars($slot); ?></td>
                                    <?php foreach ($days as $day): ?>
                                        <td>
                                            <?php if (isset($grid[$slot][$day])): ?>
                                                <div class="fw-semibold" style="color: var(--primary-color);">
                                                    <?php echo htmlspecialchars($grid[$slot][$day]['subject']); ?>
                                                </div>
                                                <div class="text-muted small">
                                                    <?php echo htmlspecialchars($grid[$slot][$day]['teacher']); ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">&mdash;</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
