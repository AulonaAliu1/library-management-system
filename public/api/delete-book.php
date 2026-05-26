<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'public');

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../app/helpers/auth_guard.php';
require_once __DIR__ . '/../../app/helpers/security.php';
require_once __DIR__ . '/../../app/services/BookService.php';

if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Admin privileges required.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed. Use POST.'
    ]);
    exit;
}

$token = (string)($_POST['csrf_token'] ?? '');
if (!csrf_check($token)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or missing CSRF token.'
    ]);
    exit;
}

$bookId = (int)($_POST['book_id'] ?? 0);

if ($bookId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid Book ID provided.'
    ]);
    exit;
}

try {
    $bookService = new BookService();

    $deleted = $bookService->deleteBook($bookId);

    if ($deleted) {
        echo json_encode([
            'success' => true,
            'message' => 'Book was archived successfully.'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to archive the book. It may not exist.'
        ]);
    }
} catch (Exception $e) {
    error_log('Book archive API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to archive the book right now.'
    ]);
}
exit;
