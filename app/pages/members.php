<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../services/UserService.php';

require_admin();

$pageTitle = 'Members';
$extraCss = './../../assets/css/members.css';
$userService = new UserService();
$members = [];
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_member') {
    $token = (string) ($_POST['csrf_token'] ?? '');
    $memberId = (int) ($_POST['member_id'] ?? 0);

    if (! csrf_check($token)) {
        $message = 'Security check failed. Please try again.';
    } elseif ($memberId <= 0) {
        $message = 'Invalid member id.';
    } elseif ($userService->deleteMember($memberId)) {
        $message = 'Member deleted successfully.';
    } else {
        $message = 'Could not delete the member.';
    }
}

$members = $userService->getAllMembers();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Members</h1>
    <p class="text-muted">Admin can create, edit, and delete member accounts from the database.</p>

    <?php if ($message !== null): ?>
        <p><strong><?= e($message) ?></strong></p>
    <?php endif; ?>

    <p>
        <a href="member-create.php">Create Member</a>
    </p>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($members === []): ?>
                <tr>
                    <td colspan="7">No members found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?= (int) $member['id'] ?></td>
                        <td><?= e($member['name'] ?? '') ?></td>
                        <td><?= e($member['username'] ?? '') ?></td>
                        <td><?= e($member['email'] ?? '') ?></td>
                        <td><?= e($member['role'] ?? '') ?></td>
                        <td><?= e($member['created_at'] ?? '-') ?></td>
                        <td>
                            <a href="member-edit.php?id=<?= (int) $member['id'] ?>">Edit</a>
                            <form method="POST" style="display:inline-block; margin-left: 8px;">
                                <input type="hidden" name="action" value="delete_member">
                                <input type="hidden" name="member_id" value="<?= (int) $member['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
