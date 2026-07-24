<?php
require_once '../config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in as admin, skip straight to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, username, password FROM admins WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);

    if ($admin && password_verify($password, $admin['password'])) {
        // admin_id / admin_name only — never touches student_id, keeping sessions separate
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Forces Academy LMS</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Forces Academy LMS</a>
            <span class="navbar-text text-white">Admin Area</span>
        </div>
    </nav>

    <!-- Admin Login Form -->
    <div class="container mt-5" style="max-width: 450px;">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                <h4 class="mb-0"><i class="bi bi-shield-lock"></i> Admin Login</h4>
            </div>
            <div class="card-body p-4">

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-accent w-100 py-2">Login</button>
                </form>

                <p class="text-center mt-3 mb-0"><a href="../login.php">&larr; Back to student login</a></p>
            </div>
        </div>
    </div>

    <footer class="text-center mt-5 py-3">
        <p class="mb-0">&copy; 2026 Forces Academy LMS — Code Saviours SI-26</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
