<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../services/UserService.php';

require_login();

$pageTitle = 'Profile';
$extraCss = '../../assets/css/profile.css';
$userService = new UserService();
$currentUser = current_user() ?? [];
$userId = (int) ($currentUser['id'] ?? 0);
$errors = [];
$message = null;
$formData = [
    'name' => (string) ($currentUser['name'] ?? ''),
    'username' => (string) ($currentUser['username'] ?? ''),
    'email' => (string) ($currentUser['email'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! csrf_check((string) ($_POST['csrf_token'] ?? ''))) {
        $message = 'Security check failed. Please try again.';
    } else {
        $result = $userService->updateProfile($userId, $_POST);
        $errors = $result['errors'];
        $formData = $result['user'];

        if ($result['success']) {
            $_SESSION['user']['name'] = $formData['name'];
            $_SESSION['user']['username'] = $formData['username'];
            $_SESSION['user']['email'] = $formData['email'];
            $message = 'Profile updated successfully.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Profile</h1>

    <?php if ($message !== null): ?>
        <p><strong><?= e($message) ?></strong></p>
    <?php endif; ?>

    <?php if (isset($errors['general'])): ?>
        <p><strong><?= e($errors['general']) ?></strong></p>
    <?php endif; ?>

    <form method="POST" class="profile-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div>
            <label for="name">Name</label><br>
            <input id="name" type="text" name="name" value="<?= e($formData['name']) ?>">
            <?php if (isset($errors['name'])): ?><br><small><?= e($errors['name']) ?></small><?php endif; ?>
        </div>

        <br>

        <div>
            <label for="username">Username</label><br>
            <input id="username" type="text" name="username" value="<?= e($formData['username']) ?>">
            <?php if (isset($errors['username'])): ?><br><small><?= e($errors['username']) ?></small><?php endif; ?>
        </div>

        <br>

        <div>
            <label for="email">Email</label><br>
            <input id="email" type="email" name="email" value="<?= e($formData['email']) ?>">
            <?php if (isset($errors['email'])): ?><br><small><?= e($errors['email']) ?></small><?php endif; ?>
        </div>

        <br>

        <button type="submit">Update Profile</button>
    </form>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
