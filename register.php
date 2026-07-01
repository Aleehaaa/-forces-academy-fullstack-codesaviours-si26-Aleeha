<?php
require_once 'config/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name    = trim($_POST['full_name']);
    $email        = trim($_POST['email']);
    $password     = $_POST['password'];
    $confirm      = $_POST['confirm_password'];
    $roll_number  = trim($_POST['roll_number']);
    $class        = trim($_POST['class']);

    if (empty($full_name) || empty($email) || empty($password) || empty($roll_number) || empty($class)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO students (full_name, email, password, roll_number, class) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssss', $full_name, $email, $hashed, $roll_number, $class);

        try {
            mysqli_stmt_execute($stmt);
            header('Location: login.php?registered=1');
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = 'This email or roll number is already registered. Please login instead.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Forces Academy LMS</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">Forces Academy LMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <div class="container mt-5" style="max-width: 550px;">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                <h4 class="mb-0">Student Registration</h4>
            </div>
            <div class="card-body p-4">

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Roll Number</label>
                            <input type="text" name="roll_number" class="form-control" value="<?php echo isset($roll_number) ? htmlspecialchars($roll_number) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class</label>
                            <input type="text" name="class" class="form-control" value="<?php echo isset($class) ? htmlspecialchars($class) : ''; ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-accent w-100 py-2">Register</button>
                </form>

                <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center mt-5 py-3">
        <p class="mb-0">&copy; 2026 Forces Academy LMS — Code Saviours SI-26</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
