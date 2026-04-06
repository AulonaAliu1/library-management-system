<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';

require_login();

$pageTitle = 'Requests';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Requests</h1>
    <p class="text-muted">
        Placeholder: borrow / hold request workflow will be implemented here using <code>RequestService</code> and <code>requests-data.php</code>.
    </p>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
