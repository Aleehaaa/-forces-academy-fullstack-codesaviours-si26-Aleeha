<?php
// $active is set on each page (e.g. $active = 'dashboard';) before this include
$menu = [
    'dashboard'    => ['label' => 'Dashboard',    'href' => 'dashboard.php',    'icon' => 'bi-speedometer2'],
    'courses'      => ['label' => 'My Courses',   'href' => 'courses.php',      'icon' => 'bi-journal-bookmark'],
    'assignments'  => ['label' => 'Assignments',  'href' => 'assignments.php',  'icon' => 'bi-clipboard-check'],
    'results'      => ['label' => 'My Results',   'href' => 'my_result.php',      'icon' => 'bi-bar-chart-line'],
    'notices'      => ['label' => 'Notices',      'href' => 'notices.php',      'icon' => 'bi-megaphone'],
];
?>
<div class="sidebar d-flex flex-column">
    <div class="sidebar-brand">
        <i class="bi bi-mortarboard-fill"></i>
        <span>Forces Academy</span>
    </div>

    <nav class="sidebar-nav flex-grow-1">
        <?php foreach ($menu as $key => $item): ?>
            <a href="<?php echo $item['href']; ?>"
               class="sidebar-link <?php echo ($active === $key) ? 'active' : ''; ?>">
                <i class="bi <?php echo $item['icon']; ?>"></i>
                <span><?php echo $item['label']; ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <a href="logout.php" class="sidebar-link sidebar-logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
    </a>
</div>