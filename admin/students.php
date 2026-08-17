<?php
require_once 'includes/admin_auth_check.php';
require_once '../config/db.php';

$active = 'students';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student_id'])) {
    $delete_id = (int) $_POST['delete_student_id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    mysqli_stmt_execute($stmt);

    $redirect = 'students.php';
    if (!empty($_POST['search_redirect'])) {
        $redirect .= '?search=' . urlencode($_POST['search_redirect']);
    }
    header('Location: ' . $redirect);
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$students = [];
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, roll_number, class, created_at
                                    FROM students
                                    WHERE full_name LIKE ? OR email LIKE ? OR roll_number LIKE ?
                                    ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT id, full_name, email, roll_number, class, created_at
                                    FROM students ORDER BY id DESC");
}
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Forces Academy LMS</title>
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
            <h2>Manage Students</h2>
            <p>View, search, and remove student accounts.</p>
        </div>

        <!-- Search bar -->
        <form method="GET" action="students.php" class="mb-4 d-flex gap-2" style="max-width: 420px;">
            <input type="text" name="search" class="form-control" placeholder="Search by name, email, or roll number"
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
            <?php if ($search !== ''): ?>
                <a href="students.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>

        <div class="card notice-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roll Number</th>
                            <th>Class</th>
                            <th>Registered Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No students found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students as $s): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($s['email']); ?></td>
                                    <td><?php echo htmlspecialchars($s['roll_number']); ?></td>
                                    <td><?php echo htmlspecialchars($s['class']); ?></td>
                                    <td><?php echo $s['created_at'] ? date('d M Y', strtotime($s['created_at'])) : '—'; ?></td>
                                    <td class="text-end">
                                        <a href="student_details.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $s['id']; ?>">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>

                                <!-- Delete confirmation modal -->
                                <div class="modal fade" id="deleteModal<?php echo $s['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="students.php">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Delete Student</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete
                                                    <strong><?php echo htmlspecialchars($s['full_name']); ?></strong>?
                                                    This cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <input type="hidden" name="delete_student_id" value="<?php echo $s['id']; ?>">
                                                    <input type="hidden" name="search_redirect" value="<?php echo htmlspecialchars($search); ?>">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
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
