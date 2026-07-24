<?php
require_once 'includes/admin_auth_check.php';
require_once '../config/db.php';

$active = 'notices';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_notice_id'])) {
        $id = (int) $_POST['delete_notice_id'];
        $stmt = mysqli_prepare($conn, "DELETE FROM notices WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        header('Location: notices.php');
        exit;
    }

    if (isset($_POST['title'])) {
        $title   = trim($_POST['title']);
        $content = trim($_POST['content']);

        if ($title === '' || $content === '') {
            $error = 'Title and content are both required.';
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO notices (title, content, posted_by) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sss', $title, $content, $admin_name);
            mysqli_stmt_execute($stmt);
            header('Location: notices.php?posted=1');
            exit;
        }
    }
}

if (isset($_GET['posted'])) {
    $success = 'Notice posted successfully!';
}

$notices = [];
$result = mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) { $notices[] = $row; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Notice - Forces Academy LMS</title>
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
            <h2>Post Notice</h2>
            <p>Publish announcements for all students.</p>
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
            <div class="card-header">New Notice</div>
            <div class="card-body">
                <form method="POST" action="notices.php">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea name="content" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-accent">Post Notice</button>
                </form>
            </div>
        </div>

        <div class="dashboard-heading">
            <h4>Existing Notices</h4>
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
                            <div>
                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($notice['title']); ?></h5>
                                <span class="badge notice-date mb-2">
                                    <?php echo date('d M Y', strtotime($notice['created_at'])); ?>
                                    <?php if (!empty($notice['posted_by'])): ?>
                                        &middot; by <?php echo htmlspecialchars($notice['posted_by']); ?>
                                    <?php endif; ?>
                                </span>
                                <p class="card-text text-muted mb-0"><?php echo nl2br(htmlspecialchars($notice['content'])); ?></p>
                            </div>
                            <form method="POST" action="notices.php" onsubmit="return confirm('Delete this notice?');">
                                <input type="hidden" name="delete_notice_id" value="<?php echo $notice['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
