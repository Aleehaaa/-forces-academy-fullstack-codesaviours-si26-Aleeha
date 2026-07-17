<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';
$active = 'results';

$results = [];
$stmt = mysqli_prepare($conn, "SELECT subject, marks, total_marks, grade, exam_type FROM results WHERE student_id = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) {
    $results[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Results - Forces Academy LMS</title>
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
            <h2>My Results</h2>
            <p>Your exam results across all subjects.</p>
        </div>

        <?php if (empty($results)): ?>
            <div class="card notice-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-bar-chart-line" style="font-size:2.5rem; color:var(--text-muted);"></i>
                    <p class="text-muted mt-3 mb-0">No results have been published yet. Check back soon!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card notice-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Total</th>
                                <th>Grade</th>
                                <th>Exam Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($r['marks']); ?></td>
                                    <td><?php echo htmlspecialchars($r['total_marks']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($r['grade']); ?></span></td>
                                    <td><?php echo htmlspecialchars($r['exam_type']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>