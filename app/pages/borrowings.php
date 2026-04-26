<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';
require_once __DIR__ . '/../services/RequestService.php';

require_login();

$pageTitle = 'Borrowings';

$requestService = new RequestService();

$currentUser = $_SESSION['user'] ?? [];
$currentUserId = (int) ($currentUser['id'] ?? 0);
$currentUserRole = strtolower((string) ($currentUser['role'] ?? 'member'));
$isAdmin = $currentUserRole === 'admin';

$actionMessage = null;

if (
    $isAdmin &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['borrowing_action'], $_POST['borrowing_id'])
) {
    $borrowingId = (int) $_POST['borrowing_id'];
    $borrowingAction = (string) $_POST['borrowing_action'];

    if ($borrowingAction === 'mark_returned') {
        $actionMessage = $requestService->markBorrowingReturned($borrowingId);
    }
}

$borrowings = $isAdmin
    ? $requestService->getAllBorrowings()
    : $requestService->getBorrowingsByUserId($currentUserId);

$borrowingCounts = $requestService->getBorrowingCounts($borrowings);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Borrowings</h1>
    <p class="text-muted">
        <?php echo $isAdmin
            ? 'Admin can view all active and returned borrowings and update their status.'
            : 'Here you can view your own borrowings and return dates.'; ?>
    </p>

    <?php if ($actionMessage !== null): ?>
        <p><strong><?php echo htmlspecialchars($actionMessage); ?></strong></p>
    <?php endif; ?>

    <section>
        <p><strong>Total:</strong> <?php echo $borrowingCounts['total']; ?></p>
        <p><strong>Active:</strong> <?php echo $borrowingCounts['active']; ?></p>
        <p><strong>Returned:</strong> <?php echo $borrowingCounts['returned']; ?></p>
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
                    <th>Return Date</th>
                    <th>Status</th>
                    <?php if ($isAdmin): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($borrowings)): ?>
                    <tr>
                        <td colspan="<?php echo $isAdmin ? '7' : '5'; ?>">No borrowings found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($borrowings as $borrowing): ?>
                        <tr>
                            <td><?php echo (int) $borrowing['id']; ?></td>
                            <?php if ($isAdmin): ?>
                                <td><?php echo htmlspecialchars((string) $borrowing['member_name']); ?></td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars((string) $borrowing['book_title']); ?></td>
                            <td><?php echo htmlspecialchars($requestService->formatDate((string) $borrowing['borrow_date'])); ?></td>
                            <td><?php echo htmlspecialchars($requestService->formatDate((string) $borrowing['return_date'])); ?></td>
                            <td class="<?php echo htmlspecialchars($requestService->getBorrowingStatusClass((string) $borrowing['status'])); ?>">
                                <?php echo htmlspecialchars(ucfirst((string) $borrowing['status'])); ?>
                            </td>
                            <?php if ($isAdmin): ?>
                                <td>
                                    <?php if ((string) $borrowing['status'] === 'active'): ?>
                                        <form method="POST">
                                            <input type="hidden" name="borrowing_id" value="<?php echo (int) $borrowing['id']; ?>">
                                            <button type="submit" name="borrowing_action" value="mark_returned">Mark Returned</button>
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
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
