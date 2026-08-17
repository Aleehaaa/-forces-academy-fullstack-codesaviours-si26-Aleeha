<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';
$active = 'fees';

// Auto-flip any pending fee whose due date has passed into "overdue"
mysqli_query($conn, "UPDATE fees SET status = 'overdue' WHERE status = 'pending' AND due_date < CURDATE()");

$fees = [];
$stmt = mysqli_prepare($conn, "SELECT amount, due_date, paid_date, status, description
                                FROM fees WHERE student_id = ? ORDER BY due_date ASC");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$total_pending = 0;
while ($row = mysqli_fetch_assoc($res)) {
    if ($row['status'] === 'pending' || $row['status'] === 'overdue') {
        $total_pending += (float) $row['amount'];
    }
    $fees[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Fees - Forces Academy LMS</title>
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
            <h2>My Fees</h2>
            <p>Your fee records and payment status.</p>
        </div>

        <div class="card notice-card mb-4">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon <?php echo $total_pending > 0 ? 'gold' : 'navy'; ?>">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div>
                        <div class="stat-label mb-1">Total Pending Amount</div>
                        <div class="stat-value">Rs. <?php echo number_format($total_pending, 2); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($fees)): ?>
            <div class="card notice-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-cash-coin" style="font-size:2.5rem; color:var(--text-muted);"></i>
                    <p class="text-muted mt-3 mb-0">No fee records found.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card notice-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Paid Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fees as $f): ?>
                                <?php
                                    $badgeClass = 'bg-secondary';
                                    if ($f['status'] === 'paid') { $badgeClass = 'bg-success'; }
                                    elseif ($f['status'] === 'pending') { $badgeClass = 'bg-warning text-dark'; }
                                    elseif ($f['status'] === 'overdue') { $badgeClass = 'bg-danger'; }
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($f['description'] ?: '—'); ?></td>
                                    <td>Rs. <?php echo number_format((float) $f['amount'], 2); ?></td>
                                    <td><?php echo date('d M Y', strtotime($f['due_date'])); ?></td>
                                    <td><?php echo $f['paid_date'] ? date('d M Y', strtotime($f['paid_date'])) : '—'; ?></td>
                                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($f['status']); ?></span></td>
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
