<?php
require_once 'includes/admin_auth_check.php';
require_once '../config/db.php';

$active = 'students';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT id, full_name, email, roll_number, class, created_at FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$student = mysqli_stmt_get_result($stmt)->fetch_assoc();

if (!$student) {
    header('Location: students.php');
    exit;
}

// Results for this student
$results = [];
$stmt2 = mysqli_prepare($conn, "SELECT subject, marks, total_marks, grade, exam_type FROM results WHERE student_id = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt2, 'i', $id);
mysqli_stmt_execute($stmt2);
$res2 = mysqli_stmt_get_result($stmt2);
while ($row = mysqli_fetch_assoc($res2)) { $results[] = $row; }

// Submissions for this student
$submissions = [];
$stmt3 = mysqli_prepare($conn, "SELECT a.title, s.status FROM submissions s
                                 JOIN assignments a ON s.assignment_id = a.id
                                 WHERE s.student_id = ? ORDER BY s.id DESC");
mysqli_stmt_bind_param($stmt3, 'i', $id);
mysqli_stmt_execute($stmt3);
$res3 = mysqli_stmt_get_result($stmt3);
while ($row = mysqli_fetch_assoc($res3)) { $submissions[] = $row; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details - Forces Academy LMS</title>
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
            <h2><?php echo htmlspecialchars($student['full_name']); ?></h2>
            <p>Student profile, results, and assignment submissions.</p>
        </div>

        <div class="card notice-card mb-4">
            <div class="card-header">Profile</div>
            <div class="card-body">
                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                <p class="mb-1"><strong>Roll Number:</strong> <?php echo htmlspecialchars($student['roll_number']); ?></p>
                <p class="mb-1"><strong>Class:</strong> <?php echo htmlspecialchars($student['class']); ?></p>
                <p class="mb-0"><strong>Registered:</strong>
                    <?php echo $student['created_at'] ? date('d M Y', strtotime($student['created_at'])) : '—'; ?>
                </p>
            </div>
        </div>

        <div class="card notice-card mb-4">
            <div class="card-header">Results</div>
            <div class="card-body">
                <?php if (empty($results)): ?>
                    <p class="text-muted mb-0">No results uploaded yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Subject</th><th>Marks</th><th>Total</th><th>Grade</th><th>Exam Type</th></tr></thead>
                            <tbody>
                            <?php foreach ($results as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($r['marks']); ?></td>
                                    <td><?php echo htmlspecialchars($r['total_marks']); ?></td>
                                    <td><?php echo htmlspecialchars($r['grade']); ?></td>
                                    <td><?php echo htmlspecialchars($r['exam_type']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card notice-card mb-4">
            <div class="card-header">Assignment Submissions</div>
            <div class="card-body">
                <?php if (empty($submissions)): ?>
                    <p class="text-muted mb-0">No assignments submitted yet.</p>
                <?php else: ?>
                    <ul class="mb-0">
                        <?php foreach ($submissions as $sub): ?>
                            <li><?php echo htmlspecialchars($sub['title']); ?> —
                                <span class="badge bg-<?php echo $sub['status'] === 'graded' ? 'success' : 'secondary'; ?>">
                                    <?php echo htmlspecialchars($sub['status']); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <a href="students.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Students</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
