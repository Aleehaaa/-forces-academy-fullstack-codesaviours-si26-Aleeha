<?php
require_once 'includes/admin_auth_check.php';
require_once '../config/db.php';

$active = 'timetable';
$error = '';
$success = '';

$classes = ['Computer Science', 'Software Engineering', 'Artificial Intelligence'];
$days    = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_timetable_id'])) {
    $delete_id = (int) $_POST['delete_timetable_id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM timetable WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    mysqli_stmt_execute($stmt);
    header('Location: timetable.php?deleted=1');
    exit;
}

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class'])) {
    $class     = trim($_POST['class']);
    $day       = trim($_POST['day']);
    $time_slot = trim($_POST['time_slot']);
    $subject   = trim($_POST['subject']);
    $teacher   = trim($_POST['teacher']);

    if ($class === '' || $day === '' || $time_slot === '' || $subject === '' || $teacher === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO timetable (class, day, time_slot, subject, teacher) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssss', $class, $day, $time_slot, $subject, $teacher);
        mysqli_stmt_execute($stmt);
        header('Location: timetable.php?added=1');
        exit;
    }
}

if (isset($_GET['added'])) {
    $success = 'Timetable entry added successfully!';
}
if (isset($_GET['deleted'])) {
    $success = 'Timetable entry deleted successfully!';
}

// Fetch all timetable entries, ordered by class, then weekday order, then time slot
$entries = [];
$sql = "SELECT * FROM timetable
        ORDER BY class ASC,
                 FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') ASC,
                 time_slot ASC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) { $entries[] = $row; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Timetable - Forces Academy LMS</title>
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
            <h2>Manage Timetable</h2>
            <p>Add and manage class timetable entries.</p>
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
            <div class="card-header">New Timetable Entry</div>
            <div class="card-body">
                <form method="POST" action="timetable.php">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Class</label>
                            <select name="class" class="form-select" required>
                                <option value="">-- Select Class --</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Day</label>
                            <select name="day" class="form-select" required>
                                <option value="">-- Select Day --</option>
                                <?php foreach ($days as $d): ?>
                                    <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Time Slot</label>
                            <input type="text" name="time_slot" class="form-control" placeholder="e.g. 9:00 - 10:00 AM" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teacher</label>
                            <input type="text" name="teacher" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-accent mt-3">Add Entry</button>
                </form>
            </div>
        </div>

        <div class="dashboard-heading">
            <h4>All Timetable Entries</h4>
        </div>

        <div class="card notice-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Class</th>
                            <th>Day</th>
                            <th>Time Slot</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($entries)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No timetable entries yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($entries as $e): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($e['class']); ?></td>
                                    <td><?php echo htmlspecialchars($e['day']); ?></td>
                                    <td><?php echo htmlspecialchars($e['time_slot']); ?></td>
                                    <td><?php echo htmlspecialchars($e['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($e['teacher']); ?></td>
                                    <td class="text-center">
                                        <form method="POST" action="timetable.php" onsubmit="return confirm('Delete this timetable entry?');">
                                            <input type="hidden" name="delete_timetable_id" value="<?php echo $e['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
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
