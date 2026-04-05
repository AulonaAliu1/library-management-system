<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

$pageTitle = 'Borrowings';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Borrowings</h1>
    <p class="text-muted">
        Placeholder: active loans, due dates, and returns will use borrowing records from <code>borrowings-data.php</code>.
    </p>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
