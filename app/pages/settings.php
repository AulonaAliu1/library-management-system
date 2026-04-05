<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

$pageTitle = 'Settings';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Settings</h1>
    <p class="text-muted">
        Placeholder: app or account preferences (theme, notifications, etc.) — implementation deferred to Phase I settings task.
    </p>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
