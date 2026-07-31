<?php
require_once 'includes/admin_auth_check.php';
require_once '../config/db.php';

$active = 'courses';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $course_name  = trim($_POST['course_name']);
        $description  = trim($_POST['description']);
        $teacher_name = trim($_POST['teacher_name']);
        $target_class = trim($_POST['target_class'] ?? 'All');

        if ($course_name === '' || $teacher_name === '') {
            $error = 'Course name and teacher name are required.';
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO courses (course_name, description, teacher_name, target_class) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssss', $course_name, $description, $teacher_name, $target_class);
            mysqli_stmt_execute($stmt);
            header('Location: courses.php');
            exit;
        }
    } elseif ($action === 'edit') {
        $id           = (int) $_POST['course_id'];
        $course_name  = trim($_POST['course_name']);
        $description  = trim($_POST['description']);
        $teacher_name = trim($_POST['teacher_name']);
        $target_class = trim($_POST['target_class'] ?? 'All');

        $stmt = mysqli_prepare($conn, "UPDATE courses SET course_name = ?, description = ?, teacher_name = ?, target_class = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssssi', $course_name, $description, $teacher_name, $target_class, $id);
        mysqli_stmt_execute($stmt);
        header('Location: courses.php');
        exit;
    } elseif ($action === 'delete') {
        $id = (int) $_POST['course_id'];
        $stmt = mysqli_prepare($conn, "DELETE FROM courses WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        header('Location: courses.php');
        exit;
    }
}

$courses = [];
$result = mysqli_query($conn, "SELECT * FROM courses ORDER BY id DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) { $courses[] = $row; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Forces Academy LMS</title>
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
                <h2>Manage Courses</h2>
                <p>Add, edit, or remove courses.</p>
            </div>
            <button type="button" class="btn btn-accent mb-3" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                <i class="bi bi-plus-lg"></i> Add New Course
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
                            <th>Course Name</th>
                            <th>Description</th>
                            <th>Teacher</th>
                            <th>Class</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($courses)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No courses added yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($courses as $c): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['course_name']); ?></td>
                                    <td><?php echo htmlspecialchars($c['description']); ?></td>
                                    <td><?php echo htmlspecialchars($c['teacher_name']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($c['target_class'] ?? 'All'); ?></span></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal" data-bs-target="#editModal<?php echo $c['id']; ?>">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $c['id']; ?>">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>

                                <!-- Edit modal (pre-filled) -->
                                <div class="modal fade" id="editModal<?php echo $c['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="courses.php">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Course</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Course Name</label>
                                                        <input type="text" name="course_name" class="form-control"
                                                               value="<?php echo htmlspecialchars($c['course_name']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Description</label>
                                                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($c['description']); ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Teacher Name</label>
                                                        <input type="text" name="teacher_name" class="form-control"
                                                               value="<?php echo htmlspecialchars($c['teacher_name']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Class</label>
                                                        <?php $currentClass = $c['target_class'] ?? 'All'; ?>
                                                        <select name="target_class" class="form-control">
                                                            <option value="All" <?php echo $currentClass === 'All' ? 'selected' : ''; ?>>All Classes</option>
                                                            <option value="Computer Science" <?php echo $currentClass === 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                                                            <option value="Software Engineering" <?php echo $currentClass === 'Software Engineering' ? 'selected' : ''; ?>>Software Engineering</option>
                                                            <option value="Artificial Intelligence" <?php echo $currentClass === 'Artificial Intelligence' ? 'selected' : ''; ?>>Artificial Intelligence</option>
                                                        </select>
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

                                <!-- Delete confirmation modal -->
                                <div class="modal fade" id="deleteModal<?php echo $c['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="courses.php">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Delete Course</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete
                                                    <strong><?php echo htmlspecialchars($c['course_name']); ?></strong>?
                                                    This cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
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

<!-- Add Course modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="courses.php">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Course Name</label>
                        <input type="text" name="course_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teacher Name</label>
                        <input type="text" name="teacher_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Class</label>
                        <select name="target_class" class="form-control">
                            <option value="All">All Classes</option>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Software Engineering">Software Engineering</option>
                            <option value="Artificial Intelligence">Artificial Intelligence</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Add Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
