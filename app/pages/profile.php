<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';

require_login();

$pageTitle = 'Profile';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Profile</h1>
    <p class="text-muted">
        Placeholder: view and edit member profile (session user) — to be wired to <code>User</code> / <code>UserService</code>.
    </p>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
