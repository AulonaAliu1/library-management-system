<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../app/helpers/functions.php';
require_once __DIR__ . '/../../app/helpers/security.php';
require_once __DIR__ . '/../../app/helpers/response.php';
require_once __DIR__ . '/../../app/core/Database.php';
require_once __DIR__ . '/../../app/repositories/RequestRepository.php';
require_once __DIR__ . '/../../app/repositories/BorrowingRepository.php';
require_once __DIR__ . '/../../app/services/RequestService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'success' => false,
        'message' => 'Method not allowed.',
    ], 405);
}

if (! is_logged_in()) {
    json_response([
        'success' => false,
        'message' => 'Authentication required.',
    ], 401);
}

if (! is_admin()) {
    json_response([
        'success' => false,
        'message' => 'Only admins can perform this action.',
    ], 403);
}

$token = (string) ($_POST['csrf_token'] ?? '');

if (! csrf_check($token)) {
    json_response([
        'success' => false,
        'message' => 'Security check failed.',
    ], 422);
}

$requestId = (int) ($_POST['request_id'] ?? 0);
$action = (string) ($_POST['request_action'] ?? '');

if ($requestId <= 0) {
    json_response([
        'success' => false,
        'message' => 'Invalid request id.',
    ], 422);
}

if (! in_array($action, ['approve', 'reject'], true)) {
    json_response([
        'success' => false,
        'message' => 'Invalid action value.',
    ], 422);
}

$pdo = Database::connection();

if (! ($pdo instanceof PDO)) {
    json_response([
        'success' => false,
        'message' => 'Database connection is not available.',
    ], 500);
}

$requestRepository = new RequestRepository($pdo);
$borrowingRepository = new BorrowingRepository($pdo);
$requestService = new RequestService($pdo, $requestRepository, $borrowingRepository);

$result = $action === 'approve'
    ? $requestService->approveRequest($requestId)
    : $requestService->rejectRequest($requestId);

$request = $requestService->getDetailedRequestById($requestId);
$allRequests = $requestService->getRequestsForUser(0, true);
$counts = $requestService->getRequestCounts($allRequests);

if ($request === null) {
    json_response([
        'success' => false,
        'message' => $result['message'],
    ], 404);
}

$status = (string) ($request['status'] ?? '');
$statusClass = $requestService->getRequestStatusClass($status);

json_response([
    'success' => $result['success'],
    'message' => $result['message'],
    'request' => [
        'id' => (int) $request['id'],
        'status' => $status,
        'statusLabel' => ucfirst($status),
        'statusClass' => $statusClass,
    ],
    'counts' => $counts,
]);
