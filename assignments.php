<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$active = 'assignments';

$assignments = [];
$sql = "SELECT a.id, a.title, a.description, a.due_date, c.course_name
        FROM assignments a
        LEFT JOIN courses c ON a.course_id = c.id
        WHERE c.target_class = 'All' OR c.target_class = ? OR c.target_class IS NULL
        ORDER BY a.due_date ASC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $student_class);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $assignments[] = $row;
    }
}

$submitted_map = [];
$stmt = mysqli_prepare($conn, "SELECT assignment_id, status FROM submissions WHERE student_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$sub_result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($sub_result)) {
    $submitted_map[$row['assignment_id']] = $row['status'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - Forces Academy LMS</title>
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
            <h2>Assignments</h2>
            <p>View and submit your pending assignments.</p>
        </div>

        <?php if (isset($_GET['submitted']) && $_GET['submitted'] === '1'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Assignment submitted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php
                    $errors = [
                        'filetype' => 'Only PDF or image files (jpg, jpeg, png) are allowed.',
                        'upload'   => 'File upload failed. Please try again.',
                        'closed'   => 'This assignment\'s deadline has passed and is now closed for submissions.',
                    ];
                    echo htmlspecialchars($errors[$_GET['error']] ?? 'Something went wrong. Please try again.');
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($assignments)): ?>
            <div class="card notice-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-clipboard-x" style="font-size:2.5rem; color:var(--text-muted);"></i>
                    <p class="text-muted mt-3 mb-0">No assignments have been posted yet. Check back soon!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($assignments as $a): ?>
                    <?php
                        $isSubmitted = isset($submitted_map[$a['id']]);
                        $isOverdue   = strtotime($a['due_date']) < strtotime(date('Y-m-d'));
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card assignment-card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($a['title']); ?></h5>
                                <p class="mb-1"><small class="text-muted"><i class="bi bi-journal-bookmark"></i> <?php echo htmlspecialchars($a['course_name'] ?? 'N/A'); ?></small></p>
                                <p class="mb-2"><small class="text-muted"><i class="bi bi-calendar-event"></i> Due: <?php echo htmlspecialchars(date('d M Y', strtotime($a['due_date']))); ?></small></p>
                                <p class="card-text flex-grow-1"><?php echo nl2br(htmlspecialchars($a['description'])); ?></p>

                                <?php if ($isSubmitted): ?>
                                    <span class="badge bg-success align-self-start px-3 py-2">
                                        <i class="bi bi-check-circle"></i>
                                        <?php echo $submitted_map[$a['id']] === 'graded' ? 'Graded' : 'Submitted'; ?>
                                    </span>
                                <?php elseif ($isOverdue): ?>
                                    <span class="badge bg-danger align-self-start px-3 py-2">
                                        <i class="bi bi-lock"></i> Closed
                                    </span>
                                <?php else: ?>
                                    <button type="button" class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#submitModal<?php echo $a['id']; ?>">
                                        Submit Assignment
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!$isSubmitted && !$isOverdue): ?>
                    <div class="modal fade" id="submitModal<?php echo $a['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="submit_assignment.php" method="POST" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Submit: <?php echo htmlspecialchars($a['title']); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="assignment_id" value="<?php echo $a['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Upload your file (PDF or Image only)</label>
                                            <input type="file" name="submission_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-accent">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>