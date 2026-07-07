<?php
if (!isset($active)) { $active = ''; }
function nav_class($page, $active) {
    return 'nav-link text-white' . ($page === $active ? ' active fw-bold' : '');
}
?>
<nav class="d-flex flex-column flex-shrink-0 p-3" style="width: 250px; min-height: 100vh; background-color:#0d2c54;">
    <a href="dashboard.php" class="d-flex align-items-center mb-3 text-decoration-none" style="color:#d4af37;">
        <span class="fs-5 fw-semibold">Forces Academy LMS</span>
    </a>
    <hr style="border-color: rgba(255,255,255,0.2);">
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item"><a href="dashboard.php" class="<?php echo nav_class('dashboard', $active); ?>">Dashboard</a></li>
        <li class="nav-item"><a href="courses.php" class="<?php echo nav_class('courses', $active); ?>">My Courses</a></li>
        <li class="nav-item"><a href="assignments.php" class="<?php echo nav_class('assignments', $active); ?>">Assignments</a></li>
        <li class="nav-item"><a href="my-results.php" class="<?php echo nav_class('results', $active); ?>">My Results</a></li>
        <li class="nav-item"><a href="notices.php" class="<?php echo nav_class('notices', $active); ?>">Notices</a></li>
    </ul>
    <hr style="border-color: rgba(255,255,255,0.2);">
    <a href="logout.php" class="nav-link" style="color:#f1a3a3;">Logout</a>
</nav>