<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$active = 'notices';

$notices = [];
$result = mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $notices[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice Board - Forces Academy LMS</title>
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
            <h2>Notice Board</h2>
            <p>Latest announcements from the academy.</p>
        </div>

        <?php if (empty($notices)): ?>
            <div class="card notice-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-megaphone" style="font-size:2.5rem; color:var(--text-muted);"></i>
                    <p class="text-muted mt-3 mb-0">No notices posted yet.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($notices as $notice): ?>
                <div class="card notice-board-item mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($notice['title']); ?></h5>
                            <span class="badge notice-date"><?php echo date('d M Y', strtotime($notice['created_at'])); ?></span>
                        </div>
                        <p class="card-text text-muted mb-0"><?php echo nl2br(htmlspecialchars($notice['content'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>