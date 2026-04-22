<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';
require_once __DIR__ . '/../services/RequestService.php';

require_login();

$pageTitle = 'Requests';

$requestService = new RequestService();

$currentUser = $_SESSION['user'] ?? [];
$currentUserId = (int) ($currentUser['id'] ?? 0);
$currentUserRole = strtolower((string) ($currentUser['role'] ?? 'member'));
$isAdmin = $currentUserRole === 'admin';

$requests = $isAdmin
    ? $requestService->getAllRequests()
    : $requestService->getRequestsByUserId($currentUserId);

$requestCounts = $requestService->getRequestCounts($requests);
$actionMessage = null;

if (
    $isAdmin &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['request_action'], $_POST['request_id'])
) {
    $actionMessage = $requestService->getRequestActionMessage(
        (string) $_POST['request_action'],
        (int) $_POST['request_id']
    );
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <h1>Requests</h1>
    <p class="text-muted">
        <?php echo $isAdmin
            ? 'Admin can view all borrow and hold requests. Approve and reject are placeholder actions in Phase I.'
            : 'Here you can view your own borrow and hold requests.'; ?>
    </p>

    <?php if ($actionMessage !== null): ?>
        <p><strong><?php echo htmlspecialchars($actionMessage); ?></strong></p>
    <?php endif; ?>

    <section>
        <p><strong>Total:</strong> <?php echo $requestCounts['total']; ?></p>
        <p><strong>Pending:</strong> <?php echo $requestCounts['pending']; ?></p>
        <p><strong>Approved:</strong> <?php echo $requestCounts['approved']; ?></p>
        <p><strong>Rejected:</strong> <?php echo $requestCounts['rejected']; ?></p>
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
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="<?php echo $isAdmin ? '6' : '4'; ?>">No requests found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo (int) $request['id']; ?></td>
                            <?php if ($isAdmin): ?>
                                <td><?php echo htmlspecialchars((string) $request['member_name']); ?></td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars((string) $request['book_title']); ?></td>
                            <td><?php echo htmlspecialchars($requestService->formatDate((string) $request['request_date'])); ?></td>
                            <td class="<?php echo htmlspecialchars($requestService->getRequestStatusClass((string) $request['status'])); ?>">
                                <?php echo htmlspecialchars(ucfirst((string) $request['status'])); ?>
                            </td>
                            <?php if ($isAdmin): ?>
                                <td>
                                    <?php if ((string) $request['status'] === 'pending'): ?>
                                        <form method="POST" style="display:inline-block; margin-right: 6px;">
                                            <input type="hidden" name="request_id" value="<?php echo (int) $request['id']; ?>">
                                            <button type="submit" name="request_action" value="approve">Approve</button>
                                        </form>

                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="request_id" value="<?php echo (int) $request['id']; ?>">
                                            <button type="submit" name="request_action" value="reject">Reject</button>
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
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
