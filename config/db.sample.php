<?php
$host     = 'sql310.infinityfree.com';
$user     = 'if0_42540677';
$password = 'juehi7o1szuRJp';
$database = 'if0_42540677_forces_academy_lms';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
?>