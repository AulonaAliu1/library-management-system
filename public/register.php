<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'public');

require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/helpers/security.php';
require_once __DIR__ . '/../app/helpers/validation.php';
require_once __DIR__ . '/../app/services/UserService.php';

if (is_logged_in()) {
    redirect('../app/pages/dashboard.php');
}

$pageTitle = 'Register';
$userService = new UserService();
$errors = [];
$message = null;
$member = [
    'name' => '',
    'username' => '',
    'email' => '',
    'role' => 'member',
    'password' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! csrf_check((string) ($_POST['csrf_token'] ?? ''))) {
        $message = 'Security check failed. Please try again.';
    } elseif ((string) ($_POST['password'] ?? '') !== (string) ($_POST['password_confirm'] ?? '')) {
        $member = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'username' => trim((string) ($_POST['username'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'role' => 'member',
            'password' => '',
        ];
        $errors['password_confirm'] = 'Passwords do not match.';
    } else {
        $result = $userService->createMember($_POST);
        $errors = $result['errors'];
        $member = $result['member'];
        $member['password'] = '';

        if ($result['success']) {
            flash_set('success', 'Account created successfully. You can now log in.');
            redirect('login.php');
        }
    }
}

require_once __DIR__ . '/../app/includes/header.php';
require_once __DIR__ . '/../app/includes/navbar.php';
?>

<main class="container main-content">
    <section class="card card-narrow">
        <h1>Create account</h1>
        <p class="text-muted">Register as a library member to request books and track your borrowings.</p>

        <?php if ($message !== null) : ?>
            <div class="flash flash-error"><?= h($message) ?></div>
        <?php endif; ?>

        <?php if (isset($errors['general'])) : ?>
            <div class="flash flash-error"><?= h($errors['general']) ?></div>
        <?php endif; ?>

        <form class="form-stack" action="" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= h($member['name']) ?>" autocomplete="name">
                <?php if (isset($errors['name'])) : ?><small><?= h($errors['name']) ?></small><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= h($member['username']) ?>" autocomplete="username">
                <?php if (isset($errors['username'])) : ?><small><?= h($errors['username']) ?></small><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= h($member['email']) ?>" autocomplete="email">
                <?php if (isset($errors['email'])) : ?><small><?= h($errors['email']) ?></small><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="new-password">
                <?php if (isset($errors['password'])) : ?><small><?= h($errors['password']) ?></small><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm password</label>
                <input type="password" id="password_confirm" name="password_confirm" autocomplete="new-password">
                <?php if (isset($errors['password_confirm'])) : ?><small><?= h($errors['password_confirm']) ?></small><?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
            <a class="btn btn-secondary" href="login.php">Back to login</a>
        </form>
    </section>
</main>

<?php
require_once __DIR__ . '/../app/includes/footer.php';
