<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Uses admin_id / admin_name — kept completely separate from student_id / student_name
// so a student session never grants admin access and vice versa.
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$admin_name = $_SESSION['admin_name'];
$admin_id   = $_SESSION['admin_id'];
