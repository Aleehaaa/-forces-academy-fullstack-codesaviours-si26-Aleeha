<?php
// Copy this file to db.php and fill in your own local/live credentials.
// db.php is gitignored so real credentials never get pushed to GitHub.
$host     = 'your_host_here';
$user     = 'your_db_username';
$password = 'your_db_password';
$database = 'your_db_name';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
?>