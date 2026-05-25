<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../services/UserService.php';

require_admin();

$pageTitle = 'Create Member';
$userService = new UserService();
$errors = [];
$member = [
    'name' => '',
    'username' => '',
    'email' => '',
    'role' => 'member',
    'password' => '',
];
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! csrf_check((string) ($_POST['csrf_token'] ?? ''))) {
        $message = 'Security check failed. Please try again.';
    } else {
        $result = $userService->createMember($_POST);
        $errors = $result['errors'];
        $member = $result['member'];

        if ($result['success']) {
            flash_set('success', 'Member created successfully.');
            redirect('members.php');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Create Member</h1>

    <?php if ($message !== null): ?>
        <p><strong><?= e($message) ?></strong></p>
    <?php endif; ?>

    <?php if (isset($errors['general'])): ?>
        <p><strong><?= e($errors['general']) ?></strong></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <p>
            <label>Name</label><br>
            <input type="text" name="name" value="<?= e($member['name']) ?>">
            <?php if (isset($errors['name'])): ?><br><small><?= e($errors['name']) ?></small><?php endif; ?>
        </p>

        <p>
            <label>Username</label><br>
            <input type="text" name="username" value="<?= e($member['username']) ?>">
            <?php if (isset($errors['username'])): ?><br><small><?= e($errors['username']) ?></small><?php endif; ?>
        </p>

        <p>
            <label>Email</label><br>
            <input type="email" name="email" value="<?= e($member['email']) ?>">
            <?php if (isset($errors['email'])): ?><br><small><?= e($errors['email']) ?></small><?php endif; ?>
        </p>

        <p>
            <label>Role</label><br>
            <select name="role">
                <option value="member" selected>member</option>
            </select>
            <?php if (isset($errors['role'])): ?><br><small><?= e($errors['role']) ?></small><?php endif; ?>
        </p>

        <p>
            <label>Password</label><br>
            <input type="password" name="password">
            <?php if (isset($errors['password'])): ?><br><small><?= e($errors['password']) ?></small><?php endif; ?>
        </p>

        <p>
            <button type="submit">Create Member</button>
            <a href="members.php">Cancel</a>
        </p>
    </form>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
