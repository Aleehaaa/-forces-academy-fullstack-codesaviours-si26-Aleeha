<?php
require_once 'includes/admin_auth_check.php';
require_once '../config/db.php';

$active = 'fees';
$error = '';
$success = '';

// Auto-flip any pending fee whose due date has passed into "overdue"
mysqli_query($conn, "UPDATE fees SET status = 'overdue' WHERE status = 'pending' AND due_date < CURDATE()");

// Handle new fee record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $student_id  = (int) $_POST['student_id'];
    $amount      = trim($_POST['amount']);
    $due_date    = trim($_POST['due_date']);
    $description = trim($_POST['description']);

    if (!$student_id || $amount === '' || $due_date === '') {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO fees (student_id, amount, due_date, description, status)
                                        VALUES (?, ?, ?, ?, 'pending')");
        mysqli_stmt_bind_param($stmt, 'idss', $student_id, $amount, $due_date, $description);
        mysqli_stmt_execute($stmt);
        header('Location: fees.php?added=1');
        exit;
    }
}

// Handle mark as paid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_paid') {
    $fee_id = (int) $_POST['fee_id'];
    if ($fee_id) {
        $stmt = mysqli_prepare($conn, "UPDATE fees SET status = 'paid', paid_date = CURDATE() WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $fee_id);
        mysqli_stmt_execute($stmt);
    }
    header('Location: fees.php?paid=1');
    exit;
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $fee_id = (int) $_POST['fee_id'];
    if ($fee_id) {
        $stmt = mysqli_prepare($conn, "DELETE FROM fees WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $fee_id);
        mysqli_stmt_execute($stmt);
    }
    header('Location: fees.php?deleted=1');
    exit;
}

if (isset($_GET['added']))   { $success = 'Fee record added successfully!'; }
if (isset($_GET['paid']))    { $success = 'Fee marked as paid!'; }
if (isset($_GET['deleted'])) { $success = 'Fee record deleted successfully!'; }

// Students for dropdown
$students = [];
$res = mysqli_query($conn, "SELECT id, full_name, roll_number FROM students ORDER BY full_name ASC");
if ($res) { while ($row = mysqli_fetch_assoc($res)) { $students[] = $row; } }

// All fee records with student info
$fees = [];
$sql = "SELECT f.id, f.amount, f.due_date, f.paid_date, f.status, f.description,
               s.full_name, s.roll_number
        FROM fees f
        LEFT JOIN students s ON f.student_id = s.id
        ORDER BY f.id DESC";
$res2 = mysqli_query($conn, $sql);
if ($res2) { while ($row = mysqli_fetch_assoc($res2)) { $fees[] = $row; } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Fees - Forces Academy LMS</title>
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
            <h2>Manage Fees</h2>
            <p>Add fee records and track payment status for students.</p>
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
            <div class="card-header">New Fee Record</div>
            <div class="card-body">
                <form method="POST" action="fees.php">
                    <input type="hidden" name="action" value="add">
                    <div class="row g-3">
                        <div class="col-md-4">
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
                        <div class="col-md-3">
                            <label class="form-label">Amount (Rs.)</label>
                            <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="e.g. Tuition">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-accent mt-3">Add Fee Record</button>
                </form>
            </div>
        </div>

        <div class="dashboard-heading">
            <h4>All Fee Records</h4>
        </div>

        <div class="card notice-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Paid Date</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($fees)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No fee records yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($fees as $f): ?>
                                <?php
                                    $badgeClass = 'bg-secondary';
                                    if ($f['status'] === 'paid') { $badgeClass = 'bg-success'; }
                                    elseif ($f['status'] === 'pending') { $badgeClass = 'bg-warning text-dark'; }
                                    elseif ($f['status'] === 'overdue') { $badgeClass = 'bg-danger'; }
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($f['full_name'] ?? 'N/A'); ?></td>
                                    <td>Rs. <?php echo number_format((float) $f['amount'], 2); ?></td>
                                    <td><?php echo date('d M Y', strtotime($f['due_date'])); ?></td>
                                    <td><?php echo $f['paid_date'] ? date('d M Y', strtotime($f['paid_date'])) : '—'; ?></td>
                                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($f['status']); ?></span></td>
                                    <td><?php echo htmlspecialchars($f['description'] ?: '—'); ?></td>
                                    <td class="text-center">
                                        <?php if ($f['status'] !== 'paid'): ?>
                                            <form method="POST" action="fees.php" class="d-inline">
                                                <input type="hidden" name="action" value="mark_paid">
                                                <input type="hidden" name="fee_id" value="<?php echo $f['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-check2"></i> Mark Paid
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                data-id="<?php echo (int) $f['id']; ?>"
                                                data-student="<?php echo htmlspecialchars($f['full_name'] ?? 'this student'); ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="fees.php">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="fee_id" id="deleteFeeId" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Fee Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this fee record for <strong id="deleteStudentName"></strong>? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var student = button.getAttribute('data-student');
        deleteModal.querySelector('#deleteFeeId').value = id;
        deleteModal.querySelector('#deleteStudentName').textContent = student;
    });
</script>
</body>
</html>
