<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$active = 'profile';
$profile_error = '';
$profile_success = '';
$password_error = '';
$password_success = '';

// Handle profile update (name + email)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);

    if ($full_name === '' || $email === '') {
        $profile_error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profile_error = 'Please enter a valid email address.';
    } else {
        // Make sure this email isn't already used by another student
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($checkStmt, 'si', $email, $student_id);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if (mysqli_fetch_assoc($checkResult)) {
            $profile_error = 'This email is already in use by another account.';
        } else {
            $updateStmt = mysqli_prepare($conn, "UPDATE students SET full_name = ?, email = ? WHERE id = ?");
            mysqli_stmt_bind_param($updateStmt, 'ssi', $full_name, $email, $student_id);
            mysqli_stmt_execute($updateStmt);

            // Keep session data in sync with the new name
            $_SESSION['student_name'] = $full_name;
            $student_name = $full_name;

            $profile_success = 'Profile updated successfully!';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
        $password_error = 'Please fill in all password fields.';
    } elseif (strlen($new_password) < 6) {
        $password_error = 'New password must be at least 6 characters.';
    } elseif ($new_password !== $confirm_password) {
        $password_error = 'New password and confirm password do not match.';
    } else {
        $pwStmt = mysqli_prepare($conn, "SELECT password FROM students WHERE id = ?");
        mysqli_stmt_bind_param($pwStmt, 'i', $student_id);
        mysqli_stmt_execute($pwStmt);
        $pwResult = mysqli_stmt_get_result($pwStmt);
        $row = mysqli_fetch_assoc($pwResult);

        if (!$row || !password_verify($current_password, $row['password'])) {
            $password_error = 'Current password is incorrect.';
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $updateStmt = mysqli_prepare($conn, "UPDATE students SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($updateStmt, 'si', $hashed, $student_id);
            mysqli_stmt_execute($updateStmt);

            $password_success = 'Password changed successfully!';
        }
    }
}

// Fetch current profile details
$student = null;
$stmt = mysqli_prepare($conn, "SELECT full_name, email, roll_number, class FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) { $student = mysqli_fetch_assoc($result); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Forces Academy LMS</title>
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
            <h2>My Profile</h2>
            <p>View and manage your account details.</p>
        </div>

        <!-- Read-only overview -->
        <div class="card notice-card mb-4">
            <div class="card-header">Account Details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Full Name</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($student['full_name']); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Email</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($student['email']); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Roll Number</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($student['roll_number']); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Class</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($student['class']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Edit profile -->
            <div class="col-lg-6">
                <div class="card notice-card h-100">
                    <div class="card-header">Edit Profile</div>
                    <div class="card-body">
                        <?php if ($profile_success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo htmlspecialchars($profile_success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($profile_error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($profile_error); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="profile.php">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control"
                                       value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?php echo htmlspecialchars($student['email']); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-accent">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change password -->
            <div class="col-lg-6">
                <div class="card notice-card h-100">
                    <div class="card-header">Change Password</div>
                    <div class="card-body">
                        <?php if ($password_success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo htmlspecialchars($password_success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($password_error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($password_error); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="profile.php">
                            <input type="hidden" name="action" value="change_password">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" minlength="6" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                            </div>
                            <button type="submit" class="btn btn-accent">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
