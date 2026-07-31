<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
$student_name = $_SESSION['student_name'];
$student_id   = $_SESSION['student_id'];

// Fetch the student's class so pages can show only class-relevant content
require_once __DIR__ . '/../config/db.php';
$student_class = '';
if (isset($conn)) {
    $stmt = mysqli_prepare($conn, "SELECT class FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($res)) {
        $student_class = $row['class'];
    }
}