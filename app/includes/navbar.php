<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/functions.php';

$isLoggedIn = is_logged_in();
$isAdmin = is_admin();

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
        <a class="navbar-brand" href="<?= h($homeUrl) ?>">LMS</a>
        <ul class="navbar-links">
            <li><a href="<?= h($homeUrl) ?>">Home</a></li>
            <li><a href="<?= h($pageBase . 'dashboard.php') ?>">Dashboard</a></li>
            <li><a href="<?= h($pageBase . 'books.php') ?>">Books</a></li>
            <li><a href="<?= h($pageBase . 'requests.php') ?>">Requests</a></li>
            <li><a href="<?= h($pageBase . 'borrowings.php') ?>">Borrowings</a></li>
            <li><a href="<?= h($pageBase . 'profile.php') ?>">Profile</a></li>
            <li><a href="<?= h($pageBase . 'settings.php') ?>">Settings</a></li>
            <?php if ($isAdmin) : ?>
                <li><a href="<?= h($pageBase . 'members.php') ?>">Members</a></li>
            <?php endif; ?>
            <?php if ($isLoggedIn) : ?>
                <li><a href="<?= h($logoutUrl) ?>">Logout</a></li>
            <?php else : ?>
                <li><a href="<?= h($loginUrl) ?>">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
