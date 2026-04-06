<?php
/**
 * Login page — form UI only. Processing will be added by the auth module.
 */
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'public');

require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/services/AuthService.php';

if (is_logged_in()) {
    redirect('../app/pages/dashboard.php');
}

$pageTitle = 'Login';
$errorMessage = flash_get('error');
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $errorMessage = 'Please enter both your username or email and password.';
    } else {
        $authService = new AuthService();
        $user = $authService->authenticate($username, $password);

        if ($user === null) {
            $errorMessage = 'Invalid username or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = $user;

            redirect('../app/pages/dashboard.php');
        }
    }
}

require_once __DIR__ . '/../app/includes/header.php';
require_once __DIR__ . '/../app/includes/navbar.php';
?>

<main class="container main-content">
    <section class="card card-narrow">
        <h1>Sign in</h1>
        <p class="text-muted">Welcome back. Enter your credentials to securely access your account.</p>

        <?php if ($errorMessage !== null) : ?>
            <div class="flash flash-error"><?= h($errorMessage) ?></div>
        <?php endif; ?>

        <form class="form-stack" action="" method="post" novalidate>
            <div class="form-group">
                <label for="username">Username or email</label>
                <input type="text" id="username" name="username" value="<?= h($username) ?>" autocomplete="username">
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
