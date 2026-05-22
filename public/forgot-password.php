<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'public');

require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/helpers/validation.php';
require_once __DIR__ . '/../app/services/AuthService.php';
require_once __DIR__ . '/../app/services/MailService.php';

if (is_logged_in()) {
    redirect('../app/pages/dashboard.php');
}

$pageTitle = 'Forgot Password';
$email = '';
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));

    if (! is_valid_email($email)) {
        $message = 'Please enter a valid email address.';
    } else {
        try {
            $authService = new AuthService();
            $token = $authService->createPasswordReset($email);
            $message = 'If this email exists, a password reset link has been created.';

            if ($token !== null) {
                $resetLink = 'reset-password.php?token=' . urlencode($token);
                $resetUrl = absolute_public_url($resetLink);

                $mailService = new MailService();
                $mailSent = $mailService->send(
                    $email,
                    'Reset your Library Management System password',
                    "Hello,\n\nUse this link to reset your password:\n\n" . $resetUrl . "\n\nThis link expires in 1 hour.\n\nIf you did not request this, you can ignore this email."
                );

                if (! $mailSent) {
                    error_log('Password reset email failed: ' . ($mailService->getLastError() ?? 'Unknown mail error'));
                }

            }
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
        <h1>Forgot password</h1>
        <p class="text-muted">Enter your account email to create a password reset link.</p>

        <?php if ($message !== null): ?>
            <div class="flash"><?= h($message) ?></div>
        <?php endif; ?>

        <form class="form-stack" action="" method="post" novalidate>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= h($email) ?>" autocomplete="email">
            </div>
            <button type="submit" class="btn btn-primary">Create reset link</button>
            <a class="btn btn-secondary" href="login.php">Back to login</a>
        </form>
    </section>
</main>

<?php
require_once __DIR__ . '/../app/includes/footer.php';

function absolute_public_url(string $path): string
{
    $https = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
    $scheme = $https ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $directory = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');

    return $scheme . '://' . $host . $directory . '/' . ltrim($path, '/');
}
