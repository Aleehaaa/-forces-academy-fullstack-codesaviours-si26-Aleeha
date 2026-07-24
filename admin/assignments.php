<?php
require_once 'includes/admin_auth_check.php';
require_once '../config/db.php';

$active = 'assignments';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title       = trim($_POST['title']);
        $description = trim($_POST['description']);
        $course_id   = (int) $_POST['course_id'];
        $due_date    = $_POST['due_date'];

        if ($title === '' || $due_date === '') {
            $error = 'Title and due date are required.';
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO assignments (title, description, course_id, due_date) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssis', $title, $description, $course_id, $due_date);
            mysqli_stmt_execute($stmt);
            header('Location: assignments.php');
            exit;
        }
    } elseif ($action === 'edit') {
        $id          = (int) $_POST['assignment_id'];
        $title       = trim($_POST['title']);
        $description = trim($_POST['description']);
        $course_id   = (int) $_POST['course_id'];
        $due_date    = $_POST['due_date'];

        $stmt = mysqli_prepare($conn, "UPDATE assignments SET title = ?, description = ?, course_id = ?, due_date = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssisi', $title, $description, $course_id, $due_date, $id);
        mysqli_stmt_execute($stmt);
        header('Location: assignments.php');
        exit;
    } elseif ($action === 'delete') {
        $id = (int) $_POST['assignment_id'];
        $stmt = mysqli_prepare($conn, "DELETE FROM assignments WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        header('Location: assignments.php');
        exit;
    }
}

// Courses for the dropdown
$courses = [];
$res = mysqli_query($conn, "SELECT id, course_name FROM courses ORDER BY course_name ASC");
if ($res) { while ($row = mysqli_fetch_assoc($res)) { $courses[] = $row; } }

// All assignments with their course name
$assignments = [];
$sql = "SELECT a.id, a.title, a.description, a.due_date, a.course_id, c.course_name
        FROM assignments a
        LEFT JOIN courses c ON a.course_id = c.id
        ORDER BY a.due_date ASC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) { $assignments[] = $row; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments - Forces Academy LMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <?php require 'includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div class="dashboard-heading">
                <h2>Manage Assignments</h2>
                <p>Add, edit, or remove assignments.</p>
            </div>
            <button type="button" class="btn btn-accent mb-3" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
                <i class="bi bi-plus-lg"></i> Add New Assignment
            </button>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card notice-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Course</th>
                            <th>Due Date</th>
                            <th>Description</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assignments)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No assignments added yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($assignments as $a): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($a['title']); ?></td>
                                    <td><?php echo htmlspecialchars($a['course_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($a['due_date']))); ?></td>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($a['description'], 0, 60, '...')); ?></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal" data-bs-target="#editModal<?php echo $a['id']; ?>">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $a['id']; ?>">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>

                                <!-- Edit modal -->
                                <div class="modal fade" id="editModal<?php echo $a['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="assignments.php">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Assignment</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="assignment_id" value="<?php echo $a['id']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Title</label>
                                                        <input type="text" name="title" class="form-control"
                                                               value="<?php echo htmlspecialchars($a['title']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Course</label>
                                                        <select name="course_id" class="form-select">
                                                            <option value="">-- None --</option>
                                                            <?php foreach ($courses as $c): ?>
                                                                <option value="<?php echo $c['id']; ?>"
                                                                    <?php echo ($c['id'] == $a['course_id']) ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($c['course_name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Due Date</label>
                                                        <input type="date" name="due_date" class="form-control"
                                                               value="<?php echo htmlspecialchars($a['due_date']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Description</label>
                                                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($a['description']); ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-accent">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete modal -->
                                <div class="modal fade" id="deleteModal<?php echo $a['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="assignments.php">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Delete Assignment</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete
                                                    <strong><?php echo htmlspecialchars($a['title']); ?></strong>?
                                                    This will also remove any student submissions for it.
                                                </div>
                                                <div class="modal-footer">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="assignment_id" value="<?php echo $a['id']; ?>">
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

<!-- Add Assignment modal -->
<div class="modal fade" id="addAssignmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="assignments.php">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-select">
                            <option value="">-- None --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Add Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
