<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: assignments.php');
    exit;
}

$assignment_id = (int) ($_POST['assignment_id'] ?? 0);

if (!$assignment_id || !isset($_FILES['submission_file']) || $_FILES['submission_file']['error'] !== UPLOAD_ERR_OK) {
    header('Location: assignments.php?error=upload');
    exit;
}

// Block submissions after the due date has passed
$stmt = mysqli_prepare($conn, "SELECT due_date FROM assignments WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $assignment_id);
mysqli_stmt_execute($stmt);
$due_result = mysqli_stmt_get_result($stmt);
$assignment = mysqli_fetch_assoc($due_result);

if (!$assignment || strtotime($assignment['due_date']) < strtotime(date('Y-m-d'))) {
    header('Location: assignments.php?error=closed');
    exit;
}

$file = $_FILES['submission_file'];

$allowed_ext  = ['pdf', 'jpg', 'jpeg', 'png'];
$allowed_mime = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$mime = mime_content_type($file['tmp_name']);

if (!in_array($ext, $allowed_ext, true) || !in_array($mime, $allowed_mime, true)) {
    header('Location: assignments.php?error=filetype');
    exit;
}

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$unique_name = uniqid('sub_', true) . '_' . $student_id . '_' . $assignment_id . '.' . $ext;
$destination = $upload_dir . $unique_name;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    header('Location: assignments.php?error=upload');
    exit;
}

$file_path = 'uploads/' . $unique_name;

$stmt = mysqli_prepare($conn, "INSERT INTO submissions (assignment_id, student_id, file_path, status) VALUES (?, ?, ?, 'submitted')");
mysqli_stmt_bind_param($stmt, 'iis', $assignment_id, $student_id, $file_path);

if (mysqli_stmt_execute($stmt)) {
    header('Location: assignments.php?submitted=1');
} else {
    header('Location: assignments.php?error=upload');
}
exit;