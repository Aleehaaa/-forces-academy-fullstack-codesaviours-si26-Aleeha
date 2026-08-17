<?php
// $active is set on each admin page (e.g. $active = 'dashboard';) before this include
$menu = [
    'dashboard'    => ['label' => 'Dashboard',       'href' => 'dashboard.php',    'icon' => 'bi-speedometer2'],
    'students'     => ['label' => 'Manage Students', 'href' => 'students.php',     'icon' => 'bi-people'],
    'courses'      => ['label' => 'Manage Courses',  'href' => 'courses.php',      'icon' => 'bi-journal-bookmark'],
    'assignments'  => ['label' => 'Manage Assignments', 'href' => 'assignments.php', 'icon' => 'bi-clipboard-check'],
    'timetable'    => ['label' => 'Manage Timetable', 'href' => 'timetable.php',   'icon' => 'bi-calendar-week'],
    'fees'         => ['label' => 'Manage Fees',     'href' => 'fees.php',         'icon' => 'bi-cash-coin'],
    'notices'      => ['label' => 'Post Notice',     'href' => 'notices.php',      'icon' => 'bi-megaphone'],
    'results'      => ['label' => 'Upload Results',  'href' => 'results.php',      'icon' => 'bi-bar-chart-line'],
];
?>

<!-- Mobile top bar with hamburger toggle (hidden on desktop) -->
<div class="mobile-topbar">
    <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Open menu">
        <i class="bi bi-list"></i>
    </button>
    <div class="mobile-topbar-brand">
        <i class="bi bi-shield-lock-fill"></i>
        <span>Admin Panel</span>
    </div>
</div>

<!-- Overlay shown behind the drawer on mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar d-flex flex-column" id="appSidebar">
    <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close menu">
        <i class="bi bi-x-lg"></i>
    </button>

    <div class="sidebar-brand">
        <i class="bi bi-shield-lock-fill"></i>
        <span>Admin Panel</span>
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

<script>
(function () {
    var sidebar   = document.getElementById('appSidebar');
    var overlay   = document.getElementById('sidebarOverlay');
    var openBtn   = document.getElementById('sidebarToggleBtn');
    var closeBtn  = document.getElementById('sidebarCloseBtn');

    function openSidebar() {
        sidebar.classList.add('show');
        overlay.classList.add('show');
        document.body.classList.add('sidebar-open');
    }

    function closeSidebar() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.classList.remove('sidebar-open');
    }

    if (openBtn)  { openBtn.addEventListener('click', openSidebar); }
    if (closeBtn) { closeBtn.addEventListener('click', closeSidebar); }
    if (overlay)  { overlay.addEventListener('click', closeSidebar); }

    var links = sidebar.querySelectorAll('.sidebar-link');
    links.forEach(function (link) {
        link.addEventListener('click', closeSidebar);
    });
})();
</script>
