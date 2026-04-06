<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';

require_admin();

$pageTitle = 'Members';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Members</h1>
    <p class="text-muted">
        Placeholder: admin-only member directory and management. Navbar shows this link only when the current logged-in user has the <code>admin</code> role.
    </p>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
