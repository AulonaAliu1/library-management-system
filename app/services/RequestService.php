<?php
declare(strict_types=1);

/**
 * Requests / borrowings coordination for Phase I dummy data.
 */
class RequestService
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['requests_data'])) {
            $_SESSION['requests_data'] = require __DIR__ . '/../data/requests-data.php';
        }

        if (!isset($_SESSION['borrowings_data'])) {
            $_SESSION['borrowings_data'] = require __DIR__ . '/../data/borrowings-data.php';
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllRequests(): array
    {
        $requests = $_SESSION['requests_data'] ?? [];
        $requests = $this->sortByDateDescending($requests, 'request_date');

        return $this->attachRequestDisplayData($requests);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRequestsByUserId(int $userId): array
    {
        return array_values(array_filter(
            $this->getAllRequests(),
            fn (array $request): bool => (int) $request['user_id'] === $userId
        ));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllBorrowings(): array
    {
        $borrowings = $_SESSION['borrowings_data'] ?? [];
        $borrowings = $this->sortByDateDescending($borrowings, 'borrow_date');

        return $this->attachBorrowingDisplayData($borrowings);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getBorrowingsByUserId(int $userId): array
    {
        return array_values(array_filter(
            $this->getAllBorrowings(),
            fn (array $borrowing): bool => (int) $borrowing['user_id'] === $userId
        ));
    }

    /**
     * @param array<int, array<string, mixed>> $requests
     * @return array<string, int>
     */
    public function getRequestCounts(array $requests): array
    {
        return [
            'total' => count($requests),
            'pending' => $this->countByStatus($requests, 'pending'),
            'approved' => $this->countByStatus($requests, 'approved'),
            'rejected' => $this->countByStatus($requests, 'rejected'),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $borrowings
     * @return array<string, int>
     */
    public function getBorrowingCounts(array $borrowings): array
    {
        return [
            'total' => count($borrowings),
            'active' => $this->countByStatus($borrowings, 'active'),
            'returned' => $this->countByStatus($borrowings, 'returned'),
        ];
    }

    public function getRequestStatusClass(string $status): string
    {
        switch ($status) {
            case 'pending':
                return 'status-pending';
            case 'approved':
                return 'status-approved';
            case 'rejected':
                return 'status-rejected';
            default:
                return 'status-default';
        }
    }

    public function getBorrowingStatusClass(string $status): string
    {
        switch ($status) {
            case 'active':
                return 'status-active';
            case 'returned':
                return 'status-returned';
            default:
                return 'status-default';
        }
    }

    public function formatDate(string $date): string
    {
        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return $date;
        }

        return date('d M Y', $timestamp);
    }

    public function approveRequest(int $requestId): string
    {
        foreach ($_SESSION['requests_data'] as &$request) {
            if ((int) $request['id'] === $requestId) {
                $request['status'] = 'approved';
                unset($request);

                return 'Request #' . $requestId . ' approved successfully.';
            }
        }

        unset($request);

        return 'Request not found.';
    }

    public function rejectRequest(int $requestId): string
    {
        foreach ($_SESSION['requests_data'] as &$request) {
            if ((int) $request['id'] === $requestId) {
                $request['status'] = 'rejected';
                unset($request);

                return 'Request #' . $requestId . ' rejected successfully.';
            }
        }

        unset($request);

        return 'Request not found.';
    }

    public function markBorrowingReturned(int $borrowingId): string
    {
        foreach ($_SESSION['borrowings_data'] as &$borrowing) {
            if ((int) $borrowing['id'] === $borrowingId) {
                $borrowing['status'] = 'returned';
                unset($borrowing);

                return 'Borrowing #' . $borrowingId . ' marked as returned successfully.';
            }
        }

        unset($borrowing);

        return 'Borrowing not found.';
    }

    /**
     * @param array<int, array<string, mixed>> $requests
     * @return array<int, array<string, mixed>>
     */
    private function attachRequestDisplayData(array $requests): array
    {
        foreach ($requests as &$request) {
            $request['member_name'] = $this->getUserNameById((int) $request['user_id']);
            $request['book_title'] = $this->getBookTitleById((int) $request['book_id']);
        }

        unset($request);

        return $requests;
    }

    /**
     * @param array<int, array<string, mixed>> $borrowings
     * @return array<int, array<string, mixed>>
     */
    private function attachBorrowingDisplayData(array $borrowings): array
    {
        foreach ($borrowings as &$borrowing) {
            $borrowing['member_name'] = $this->getUserNameById((int) $borrowing['user_id']);
            $borrowing['book_title'] = $this->getBookTitleById((int) $borrowing['book_id']);
        }

        unset($borrowing);

        return $borrowings;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function countByStatus(array $items, string $status): int
    {
        return count(array_filter(
            $items,
            fn (array $item): bool => (string) $item['status'] === $status
        ));
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function sortByDateDescending(array $items, string $dateKey): array
    {
        usort($items, function (array $first, array $second) use ($dateKey): int {
            return strtotime((string) $second[$dateKey]) <=> strtotime((string) $first[$dateKey]);
        });

        return $items;
    }

    private function getUserNameById(int $userId): string
    {
        $users = require __DIR__ . '/../data/users-data.php';

        foreach ($users as $user) {
            if ((int) $user['id'] === $userId) {
                return (string) $user['name'];
            }
        }

        return 'Unknown User';
    }

    private function getBookTitleById(int $bookId): string
    {
        $books = require __DIR__ . '/../data/books-data.php';

        foreach ($books as $book) {
            if ((int) $book['id'] === $bookId) {
                return (string) $book['title'];
            }
        }

        return 'Unknown Book';
    }
}
