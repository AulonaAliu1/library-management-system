<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'public');

require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/helpers/validation.php';
require_once __DIR__ . '/../app/services/AuthService.php';

if (is_logged_in()) {
    redirect('../app/pages/dashboard.php');
}

$pageTitle = 'Reset Password';
$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$message = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($token === '') {
        $message = 'Invalid or missing reset token.';
    } elseif (! is_strong_enough_password($password)) {
        $message = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
    } else {
        try {
            $authService = new AuthService();
            $success = $authService->resetPassword($token, $password);
            $message = $success
                ? 'Your password has been reset. You can now log in.'
                : 'This reset link is invalid or expired.';
        } catch (Throwable $exception) {
            $message = 'Password reset is temporarily unavailable. Please try again later.';
        }
    }
}

require_once __DIR__ . '/../app/includes/header.php';
require_once __DIR__ . '/../app/includes/navbar.php';
?>

<main class="container main-content">
    <section class="card card-narrow">
        <h1>Reset password</h1>

        <?php if ($message !== null): ?>
            <div class="flash"><?= h($message) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <p><a class="btn btn-primary" href="login.php">Go to login</a></p>
        <?php else: ?>
            <form class="form-stack" action="" method="post" novalidate>
                <input type="hidden" name="token" value="<?= h($token) ?>">

                <div class="form-group">
                    <label for="password">New password</label>
                    <input type="password" id="password" name="password" autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm password</label>
                    <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary">Reset password</button>
                <a class="btn btn-secondary" href="login.php">Back to login</a>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php
require_once __DIR__ . '/../app/includes/footer.php';
