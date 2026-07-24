<?php
require_once 'includes/admin_auth_check.php';
require_once '../config/db.php';

$active = 'results';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id  = (int) $_POST['student_id'];
    $course_id   = (int) $_POST['course_id'];
    $subject     = trim($_POST['subject']);
    $marks       = trim($_POST['marks']);
    $total_marks = trim($_POST['total_marks']);
    $grade       = trim($_POST['grade']);
    $exam_type   = trim($_POST['exam_type']);

    if (!$student_id || $subject === '' || $marks === '' || $total_marks === '' || $grade === '' || $exam_type === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO results (student_id, course_id, subject, marks, total_marks, grade, exam_type)
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iisssss', $student_id, $course_id, $subject, $marks, $total_marks, $grade, $exam_type);
        // Note: marks/total_marks bound as strings here works fine for numeric columns via mysqli.
        mysqli_stmt_execute($stmt);
        header('Location: results.php?uploaded=1');
        exit;
    }
}

if (isset($_GET['uploaded'])) {
    $success = 'Result uploaded successfully!';
}

// Students for dropdown
$students = [];
$res = mysqli_query($conn, "SELECT id, full_name, roll_number FROM students ORDER BY full_name ASC");
if ($res) { while ($row = mysqli_fetch_assoc($res)) { $students[] = $row; } }

// Courses for dropdown
$courses = [];
$res2 = mysqli_query($conn, "SELECT id, course_name FROM courses ORDER BY course_name ASC");
if ($res2) { while ($row = mysqli_fetch_assoc($res2)) { $courses[] = $row; } }

// Recently uploaded results (last 15)
$recent = [];
$sql = "SELECT r.id, s.full_name, c.course_name, r.subject, r.marks, r.total_marks, r.grade, r.exam_type
        FROM results r
        LEFT JOIN students s ON r.student_id = s.id
        LEFT JOIN courses c ON r.course_id = c.id
        ORDER BY r.id DESC LIMIT 15";
$res3 = mysqli_query($conn, $sql);
if ($res3) { while ($row = mysqli_fetch_assoc($res3)) { $recent[] = $row; } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Results - Forces Academy LMS</title>
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
            <h2>Upload Results</h2>
            <p>Add exam results for students.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card notice-card mb-4">
            <div class="card-header">New Result</div>
            <div class="card-body">
                <form method="POST" action="results.php">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Student</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">-- Select Student --</option>
                                <?php foreach ($students as $s): ?>
                                    <option value="<?php echo $s['id']; ?>">
                                        <?php echo htmlspecialchars($s['full_name'] . ' (' . $s['roll_number'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course</label>
                            <select name="course_id" class="form-select" required>
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Marks</label>
                            <input type="number" name="marks" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Marks</label>
                            <input type="number" name="total_marks" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Grade</label>
                            <input type="text" name="grade" class="form-control" placeholder="e.g. A, B+" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Exam Type</label>
                            <select name="exam_type" class="form-select" required>
                                <option value="">-- Select --</option>
                                <option value="Quiz">Quiz</option>
                                <option value="Midterm">Midterm</option>
                                <option value="Final">Final</option>
                                <option value="Assignment">Assignment</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-accent mt-3">Upload Result</button>
                </form>
            </div>
        </div>

        <div class="dashboard-heading">
            <h4>Recently Uploaded Results</h4>
        </div>

        <div class="card notice-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Exam Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No results uploaded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['full_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($r['course_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($r['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($r['marks'] . ' / ' . $r['total_marks']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($r['grade']); ?></span></td>
                                    <td><?php echo htmlspecialchars($r['exam_type']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
