<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../repositories/BorrowingRepository.php';
require_once __DIR__ . '/../services/BorrowingService.php';
require_once __DIR__ . '/../core/Database.php';

require_login();

$pageTitle = 'Borrowings';
$actionMessage = null;
$borrowings = [];
$borrowingCounts = [
    'total' => 0,
    'active' => 0,
    'returned' => 0,
];
$currentUser = current_user() ?? [];
$currentUserId = (int) ($currentUser['id'] ?? 0);
$currentUserRole = strtolower((string) ($currentUser['role'] ?? 'member'));
$isAdmin = $currentUserRole === 'admin';
$databaseError = null;
$borrowingService = null;

try {
    $pdo = Database::connection();

    if (! ($pdo instanceof PDO)) {
        throw new RuntimeException('Database connection is not available.');
    }

    $borrowingRepository = new BorrowingRepository($pdo);
    $borrowingService = new BorrowingService($pdo, $borrowingRepository);

    if (
        $isAdmin &&
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['borrowing_action'], $_POST['borrowing_id'])
    ) {
        if (! csrf_check((string) ($_POST['csrf_token'] ?? ''))) {
            $actionMessage = 'Security check failed. Please try again.';
        } else {
            $borrowingId = (int) $_POST['borrowing_id'];
            $borrowingAction = (string) $_POST['borrowing_action'];

            if ($borrowingAction === 'mark_returned') {
                $actionMessage = $borrowingService->markReturned($borrowingId);
            }
        }
    }

    $borrowings = $borrowingService->getBorrowingsForUser($currentUserId, $isAdmin);
    $borrowingCounts = $borrowingService->getBorrowingCounts($borrowings);
} catch (Throwable $exception) {
    $databaseError = 'Borrowings module is not ready until the Phase II database foundation is available.';
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Borrowings</h1>
    <p class="text-muted">
        <?= $isAdmin
            ? 'Admin staff can view borrowings and confirm books returned at the library desk.'
            : 'Here you can view your borrowed books, due dates, and borrowing status.'; ?>
    </p>

    <?php if ($databaseError !== null): ?>
        <p><strong><?= e($databaseError) ?></strong></p>
    <?php else: ?>
        <?php if ($actionMessage !== null): ?>
            <p><strong><?= e($actionMessage) ?></strong></p>
        <?php endif; ?>

        <section>
            <p><strong>Total:</strong> <?= $borrowingCounts['total'] ?></p>
            <p><strong>Active:</strong> <?= $borrowingCounts['active'] ?></p>
            <p><strong>Returned:</strong> <?= $borrowingCounts['returned'] ?></p>
        </section>

        <section>
            <table border="1" cellpadding="8" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if ($isAdmin): ?>
                            <th>Member</th>
                        <?php endif; ?>
                        <th>Book</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <?php if ($isAdmin): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($borrowings === []): ?>
                        <tr>
                            <td colspan="<?= $isAdmin ? '7' : '5' ?>">No borrowings found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($borrowings as $borrowing): ?>
                            <tr>
                                <td><?= (int) $borrowing['id'] ?></td>
                                <?php if ($isAdmin): ?>
                                    <td><?= e((string) ($borrowing['member_name'] ?? '')) ?></td>
                                <?php endif; ?>
                                <td><?= e((string) ($borrowing['book_title'] ?? '')) ?></td>
                                <td><?= e($borrowingService->formatDate((string) ($borrowing['borrow_date'] ?? ''))) ?></td>
                                <td><?= e($borrowingService->formatDate((string) ($borrowing['return_date'] ?? ''))) ?></td>
                                <td class="<?= e($borrowingService->getBorrowingStatusClass((string) ($borrowing['status'] ?? ''))) ?>">
                                    <?= e(ucfirst((string) ($borrowing['status'] ?? ''))) ?>
                                </td>
                                <?php if ($isAdmin): ?>
                                    <td>
                                        <?php if ((string) ($borrowing['status'] ?? '') === 'active'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="borrowing_id" value="<?= (int) $borrowing['id'] ?>">
                                                <input type="hidden" name="borrowing_action" value="mark_returned">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                <button type="submit">Confirm Return</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">Already returned</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
