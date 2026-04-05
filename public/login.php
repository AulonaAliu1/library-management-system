<?php
/**
 * Login page — form UI only. Processing will be added by the auth module.
 */
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'public');

$pageTitle = 'Login';

require_once __DIR__ . '/../app/includes/header.php';
require_once __DIR__ . '/../app/includes/navbar.php';
?>

<main class="container main-content">
    <section class="card card-narrow">
        <h1>Sign in</h1>
        <p class="text-muted">Welcome back. Enter your credentials to securely access your account.</p>

        <!-- TODO: wire POST to AuthService::authenticate() and set session / cookies -->
        <form class="form-stack" action="" method="post" novalidate>
            <div class="form-group">
                <label for="username">Username or email</label>
                <input type="text" id="username" name="username" autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </section>
</main>

<?php
require_once __DIR__ . '/../app/includes/footer.php';
