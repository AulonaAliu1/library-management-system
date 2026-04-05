<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

$pageTitle = 'Books';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Books</h1>
    <p class="text-muted">
        Placeholder: catalogue listing, search, and filters will use <code>BookService</code> and <code>books-data.php</code> when the books module is built.
    </p>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
