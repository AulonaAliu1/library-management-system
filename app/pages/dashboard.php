<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';

require_login();

$pageTitle = 'Dashboard';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Dashboard</h1>
    <p class="text-muted">
        Placeholder: overview widgets, quick stats, and role-specific shortcuts will be implemented in Phase I.
    </p>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
