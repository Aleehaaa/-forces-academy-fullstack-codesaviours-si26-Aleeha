<?php
require_once 'includes/admin_auth_check.php';
require_once '../config/db.php';

$active = 'dashboard';

$total_students   = 0;
$total_courses    = 0;
$total_assignments = 0;
$total_notices    = 0;

$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM students");
if ($res) { $total_students = mysqli_fetch_assoc($res)['total']; }

$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM courses");
if ($res) { $total_courses = mysqli_fetch_assoc($res)['total']; }

$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM assignments");
if ($res) { $total_assignments = mysqli_fetch_assoc($res)['total']; }

$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM notices");
if ($res) { $total_notices = mysqli_fetch_assoc($res)['total']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Forces Academy LMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>

<div class="app-shell">
    <?php require 'includes/admin_sidebar.php'; ?>

    <div class="main-content">

        <div class="dashboard-heading">
            <h2>Welcome, <?php echo htmlspecialchars($admin_name); ?>!</h2>
            <p>Here's an overview of the whole academy.</p>
        </div>

        <!-- Stats cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon navy"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $total_students; ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon gold"><i class="bi bi-journal-bookmark"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $total_courses; ?></div>
                        <div class="stat-label">Total Courses</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon muted"><i class="bi bi-clipboard-check"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $total_assignments; ?></div>
                        <div class="stat-label">Total Assignments</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon navy"><i class="bi bi-megaphone"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $total_notices; ?></div>
                        <div class="stat-label">Total Notices</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick links -->
        <div class="d-flex gap-3 quick-actions flex-wrap">
            <a href="students.php" class="btn btn-primary">Manage Students</a>
            <a href="courses.php" class="btn btn-primary">Manage Courses</a>
            <a href="assignments.php" class="btn btn-primary">Manage Assignments</a>
            <a href="notices.php" class="btn btn-accent">Post Notice</a>
            <a href="results.php" class="btn btn-accent">Upload Results</a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
