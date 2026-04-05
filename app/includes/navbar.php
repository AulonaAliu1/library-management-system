<?php
declare(strict_types=1);

$role = $_SESSION['role'] ?? null;
$isLoggedIn = !empty($_SESSION['user_id']);

if (LMS_ENTRY === 'public') {
    $homeUrl = 'index.php';
    $loginUrl = 'login.php';
    $logoutUrl = 'logout.php';
    $pageBase = '../app/pages/';
} else {
    $homeUrl = '../../public/index.php';
    $loginUrl = '../../public/login.php';
    $logoutUrl = '../../public/logout.php';
    $pageBase = '';
}
?>
<header class="site-header">
    <nav class="navbar container" aria-label="Main">
        <a class="navbar-brand" href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>">LMS</a>
        <ul class="navbar-links">
            <li><a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>">Home</a></li>
            <li><a href="<?= htmlspecialchars($pageBase . 'dashboard.php', ENT_QUOTES, 'UTF-8') ?>">Dashboard</a></li>
            <li><a href="<?= htmlspecialchars($pageBase . 'books.php', ENT_QUOTES, 'UTF-8') ?>">Books</a></li>
            <li><a href="<?= htmlspecialchars($pageBase . 'requests.php', ENT_QUOTES, 'UTF-8') ?>">Requests</a></li>
            <li><a href="<?= htmlspecialchars($pageBase . 'borrowings.php', ENT_QUOTES, 'UTF-8') ?>">Borrowings</a></li>
            <li><a href="<?= htmlspecialchars($pageBase . 'profile.php', ENT_QUOTES, 'UTF-8') ?>">Profile</a></li>
            <li><a href="<?= htmlspecialchars($pageBase . 'settings.php', ENT_QUOTES, 'UTF-8') ?>">Settings</a></li>
            <?php if ($role === 'admin') : ?>
                <li><a href="<?= htmlspecialchars($pageBase . 'members.php', ENT_QUOTES, 'UTF-8') ?>">Members</a></li>
            <?php endif; ?>
            <?php if ($isLoggedIn) : ?>
                <li><a href="<?= htmlspecialchars($logoutUrl, ENT_QUOTES, 'UTF-8') ?>">Logout</a></li>
            <?php else : ?>
                <li><a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') ?>">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
