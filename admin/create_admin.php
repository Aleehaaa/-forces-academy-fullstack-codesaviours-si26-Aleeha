<?php
/**
 * ONE-TIME SETUP SCRIPT.
 * Run this once in the browser (e.g. http://localhost/forces-academy-lms/admin/create_admin.php)
 * to create your first admin login. Then DELETE this file — don't leave it on a live server.
 *
 * Default admin created:
 *   Username: admin
 *   Email:    admin@forcesacademy.com
 *   Password: Admin@123
 * Change these below before running if you want different credentials.
 */

require_once '../config/db.php';

$username = 'admin';
$email    = 'admin@forcesacademy.com';
$password = 'Admin@123';

$hashed = password_hash($password, PASSWORD_DEFAULT);

$check = mysqli_prepare($conn, "SELECT id FROM admins WHERE email = ? OR username = ?");
mysqli_stmt_bind_param($check, 'ss', $email, $username);
mysqli_stmt_execute($check);
$existing = mysqli_stmt_get_result($check)->fetch_assoc();

if ($existing) {
    echo "An admin with this username or email already exists. Nothing was created.";
} else {
    $stmt = mysqli_prepare($conn, "INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $hashed);
    if (mysqli_stmt_execute($stmt)) {
        echo "Admin created successfully!<br>Username: $username<br>Email: $email<br>Password: $password<br><br>";
        echo "<strong>Now delete this file (create_admin.php) for security.</strong>";
    } else {
        echo "Something went wrong: " . mysqli_error($conn);
    }
}
