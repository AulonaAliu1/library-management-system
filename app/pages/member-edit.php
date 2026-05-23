<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../services/UserService.php';

require_admin();

$pageTitle = 'Edit Member';
$userService = new UserService();
$memberId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$errors = [];
$message = null;

if ($memberId <= 0) {
    redirect('members.php');
}

$member = $userService->getMemberById($memberId);

if ($member === null) {
    redirect('members.php');
}

$formData = [
    'name' => (string) ($member['name'] ?? ''),
    'username' => (string) ($member['username'] ?? ''),
    'email' => (string) ($member['email'] ?? ''),
    'role' => 'member',
    'password' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! csrf_check((string) ($_POST['csrf_token'] ?? ''))) {
        $message = 'Security check failed. Please try again.';
    } else {
        $result = $userService->updateMember($memberId, $_POST);
        $errors = $result['errors'];
        $formData = $result['member'];

        if ($result['success']) {
            flash_set('success', 'Member updated successfully.');
            redirect('members.php');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Edit Member</h1>

    <?php if ($message !== null): ?>
        <p><strong><?= e($message) ?></strong></p>
    <?php endif; ?>

    <?php if (isset($errors['general'])): ?>
        <p><strong><?= e($errors['general']) ?></strong></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $memberId ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <p>
            <label>Name</label><br>
            <input type="text" name="name" value="<?= e($formData['name']) ?>">
            <?php if (isset($errors['name'])): ?><br><small><?= e($errors['name']) ?></small><?php endif; ?>
        </p>

        <p>
            <label>Username</label><br>
            <input type="text" name="username" value="<?= e($formData['username']) ?>">
            <?php if (isset($errors['username'])): ?><br><small><?= e($errors['username']) ?></small><?php endif; ?>
        </p>

        <p>
            <label>Email</label><br>
            <input type="email" name="email" value="<?= e($formData['email']) ?>">
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
            <label>New Password (optional)</label><br>
            <input type="password" name="password">
            <?php if (isset($errors['password'])): ?><br><small><?= e($errors['password']) ?></small><?php endif; ?>
        </p>

        <p>
            <button type="submit">Save Changes</button>
            <a href="members.php">Cancel</a>
        </p>
    </form>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
