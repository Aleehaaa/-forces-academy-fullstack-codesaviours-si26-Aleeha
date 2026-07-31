<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$active = 'courses';

$courses = [];
$stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE target_class = 'All' OR target_class = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt, 's', $student_class);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Forces Academy LMS</title>
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
            <h2>My Courses</h2>
            <p>All courses you're enrolled in this term.</p>
        </div>

        <?php if (empty($courses)): ?>
            <div class="card notice-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-journal-x" style="font-size:2.5rem; color:var(--text-muted);"></i>
                    <p class="text-muted mt-3 mb-0">No courses have been added yet. Check back soon!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4">
                        <div class="card course-card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($course['course_name']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($course['description']); ?></p>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <small class="text-muted"><i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($course['teacher_name']); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>