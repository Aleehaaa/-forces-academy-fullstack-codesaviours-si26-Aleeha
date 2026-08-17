<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$active = 'notices';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$notices = [];
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = mysqli_prepare($conn, "SELECT * FROM notices WHERE title LIKE ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 's', $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC");
}
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

        <!-- Search bar -->
        <form method="GET" action="notices.php" class="mb-4 d-flex gap-2" style="max-width: 420px;">
            <input type="text" name="search" class="form-control" placeholder="Search notices by title"
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
            <?php if ($search !== ''): ?>
                <a href="notices.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>

        <?php if (empty($notices)): ?>
            <div class="card notice-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-megaphone" style="font-size:2.5rem; color:var(--text-muted);"></i>
                    <p class="text-muted mt-3 mb-0"><?php echo $search !== '' ? 'No notices match your search.' : 'No notices posted yet.'; ?></p>
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