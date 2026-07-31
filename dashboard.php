<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$active = 'dashboard';

// Total Courses count (only courses relevant to this student's class)
$total_courses = 0;
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM courses WHERE target_class = 'All' OR target_class = ?");
mysqli_stmt_bind_param($stmt, 's', $student_class);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if ($res) { $total_courses = mysqli_fetch_assoc($res)['total']; }

// Pending Assignments (assignments not yet submitted by this student, only for their class)
$pending_assignments = 0;
$stmt2 = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM assignments a
    LEFT JOIN courses c ON a.course_id = c.id
    WHERE (c.target_class = 'All' OR c.target_class = ? OR c.target_class IS NULL)
    AND a.id NOT IN (SELECT assignment_id FROM submissions WHERE student_id = ?)");
mysqli_stmt_bind_param($stmt2, 'si', $student_class, $student_id);
mysqli_stmt_execute($stmt2);
$res_pending = mysqli_stmt_get_result($stmt2);
if ($res_pending) { $pending_assignments = mysqli_fetch_assoc($res_pending)['total']; }

// Latest Notice
$latest_notice = null;
$res2 = mysqli_query($conn, "SELECT title, created_at FROM notices ORDER BY created_at DESC LIMIT 1");
if ($res2 && mysqli_num_rows($res2) > 0) { $latest_notice = mysqli_fetch_assoc($res2); }

// Recent Notices (last 3)
$recent_notices = [];
$res3 = mysqli_query($conn, "SELECT title, content, created_at FROM notices ORDER BY created_at DESC LIMIT 3");
if ($res3) { while ($row = mysqli_fetch_assoc($res3)) { $recent_notices[] = $row; } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Forces Academy LMS</title>
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
            <h2>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>
            <p>Here's what's happening with your courses today.</p>
        </div>

        <!-- Stats cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon navy"><i class="bi bi-journal-bookmark"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $total_courses; ?></div>
                        <div class="stat-label">Total Courses</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon gold"><i class="bi bi-clipboard-check"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $pending_assignments; ?></div>
                        <div class="stat-label">Pending Assignments</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon muted"><i class="bi bi-megaphone"></i></div>
                    <div>
                        <div class="fw-semibold" style="color:var(--primary-color); font-size:0.95rem;">
                            <?php echo $latest_notice ? htmlspecialchars($latest_notice['title']) : 'No notices yet'; ?>
                        </div>
                        <div class="stat-label">Latest Notice</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent notices -->
        <div class="card notice-card mb-4">
            <div class="card-header">Recent Notices</div>
            <div class="card-body">
                <?php if (empty($recent_notices)): ?>
                    <p class="text-muted mb-0">No notices posted yet.</p>
                <?php else: ?>
                    <?php foreach ($recent_notices as $notice): ?>
                        <div class="notice-item">
                            <strong><?php echo htmlspecialchars($notice['title']); ?></strong>
                            <div class="text-muted small"><?php echo date('d M Y, h:i A', strtotime($notice['created_at'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick links -->
        <div class="d-flex gap-3 quick-actions">
            <a href="courses.php" class="btn btn-primary">Go to My Courses</a>
            <a href="assignments.php" class="btn btn-accent">Go to Assignments</a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>