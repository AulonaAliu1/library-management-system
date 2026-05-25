<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../repositories/RequestRepository.php';
require_once __DIR__ . '/../repositories/BorrowingRepository.php';
require_once __DIR__ . '/../services/RequestService.php';
require_once __DIR__ . '/../core/Database.php';

require_login();

$pageTitle = 'Requests';
$actionMessage = null;
$requests = [];
$requestCounts = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
];
$currentUser = current_user() ?? [];
$currentUserId = (int) ($currentUser['id'] ?? 0);
$currentUserRole = strtolower((string) ($currentUser['role'] ?? 'member'));
$isAdmin = $currentUserRole === 'admin';
$databaseError = null;
$requestService = null;

try {
    $pdo = Database::connection();

    if (! ($pdo instanceof PDO)) {
        throw new RuntimeException('Database connection is not available.');
    }

    $requestRepository = new RequestRepository($pdo);
    $borrowingRepository = new BorrowingRepository($pdo);
    $requestService = new RequestService($pdo, $requestRepository, $borrowingRepository);

    if (
        $isAdmin &&
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['request_action'], $_POST['request_id'])
    ) {
        if (! csrf_check((string) ($_POST['csrf_token'] ?? ''))) {
            $actionMessage = 'Security check failed. Please try again.';
        } else {
            $requestId = (int) $_POST['request_id'];
            $requestAction = (string) $_POST['request_action'];

            if ($requestAction === 'approve') {
                $actionMessage = $requestService->approveRequest($requestId);
            } elseif ($requestAction === 'reject') {
                $actionMessage = $requestService->rejectRequest($requestId);
            }
        }
    }

    $requests = $requestService->getRequestsForUser($currentUserId, $isAdmin);
    $requestCounts = $requestService->getRequestCounts($requests);
} catch (Throwable $exception) {
    $databaseError = 'Requests module is not ready until the Phase II database foundation is available.';
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Requests</h1>
    <p class="text-muted">
        <?= $isAdmin
            ? 'Admin can view all borrow and hold requests and update their status.'
            : 'Here you can view your own borrow and hold requests.'; ?>
    </p>

    <?php if ($databaseError !== null): ?>
        <p><strong><?= e($databaseError) ?></strong></p>
    <?php else: ?>
        <?php if ($actionMessage !== null): ?>
            <p><strong><?= e($actionMessage) ?></strong></p>
        <?php endif; ?>

        <section>
            <p><strong>Total:</strong> <span id="requests-total"><?= $requestCounts['total'] ?></span></p>
            <p><strong>Pending:</strong> <span id="requests-pending"><?= $requestCounts['pending'] ?></span></p>
            <p><strong>Approved:</strong> <span id="requests-approved"><?= $requestCounts['approved'] ?></span></p>
            <p><strong>Rejected:</strong> <span id="requests-rejected"><?= $requestCounts['rejected'] ?></span></p>
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
                        <th>Request Date</th>
                        <th>Status</th>
                        <?php if ($isAdmin): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($requests === []): ?>
                        <tr>
                            <td colspan="<?= $isAdmin ? '6' : '4' ?>">No requests found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                            <tr data-request-row="<?= (int) $request['id'] ?>">
                                <td><?= (int) $request['id'] ?></td>
                                <?php if ($isAdmin): ?>
                                    <td><?= e((string) ($request['member_name'] ?? '')) ?></td>
                                <?php endif; ?>
                                <td><?= e((string) ($request['book_title'] ?? '')) ?></td>
                                <td><?= e($requestService->formatDate((string) ($request['request_date'] ?? ''))) ?></td>
                                <td data-request-status class="<?= e($requestService->getRequestStatusClass((string) ($request['status'] ?? ''))) ?>">
                                    <?= e(ucfirst((string) ($request['status'] ?? ''))) ?>
                                </td>
                                <?php if ($isAdmin): ?>
                                    <td data-request-actions>
                                        <?php if ((string) ($request['status'] ?? '') === 'pending'): ?>
                                            <form method="POST" data-ajax-request-status style="display:inline-block; margin-right: 6px;">
                                                <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                                <input type="hidden" name="request_action" value="approve">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                <button type="submit">Approve</button>
                                            </form>
                                            <form method="POST" data-ajax-request-status style="display:inline-block;">
                                                <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                                <input type="hidden" name="request_action" value="reject">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                <button type="submit">Reject</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">Processed</span>
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

<?php if ($isAdmin && $databaseError === null): ?>
    <script src="../../assets/js/requests.js"></script>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/footer.php';
