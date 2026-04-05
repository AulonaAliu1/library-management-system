<?php
/**
 * Library Management System — public entry (landing).
 * Phase I template: no business logic here.
 */
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'public');

$pageTitle = 'Home';

require_once __DIR__ . '/../app/includes/header.php';
require_once __DIR__ . '/../app/includes/navbar.php';
?>

<main class="container main-content">
    <section class="card">
        <h1>Library Management System</h1>
        <p class="lead">
            This is the <strong>Phase I</strong> shared template: dummy data only, no database, and feature modules are placeholders for your team.
        </p>
        <p>
            <a class="btn btn-primary" href="login.php">Go to login</a>
        </p>
    </section>
</main>

<?php
require_once __DIR__ . '/../app/includes/footer.php';
