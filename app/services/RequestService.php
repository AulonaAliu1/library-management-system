<?php
declare(strict_types=1);

/**
 * Requests / borrowings coordination for Phase I dummy data.
 */
class RequestService
{
    private string $requestsFile;
    private string $borrowingsFile;
    private string $booksFile;
    private string $usersFile;

    public function __construct()
    {
        $this->requestsFile = __DIR__ . '/../data/requests-data.php';
        $this->borrowingsFile = __DIR__ . '/../data/borrowings-data.php';
        $this->booksFile = __DIR__ . '/../data/books-data.php';
        $this->usersFile = __DIR__ . '/../data/users-data.php';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllRequests(): array
    {
        $requests = $this->readDataFile($this->requestsFile);
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
        $borrowings = $this->readDataFile($this->borrowingsFile);
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
        $requests = $this->readDataFile($this->requestsFile);
        $borrowings = $this->readDataFile($this->borrowingsFile);
        $books = $this->readDataFile($this->booksFile);

        $targetRequest = null;

        foreach ($requests as &$request) {
            if ((int) $request['id'] !== $requestId) {
                continue;
            }

            if ((string) $request['status'] !== 'pending') {
                unset($request);
                return 'This request has already been processed.';
            }

            $bookIndex = $this->findBookIndex($books, (int) $request['book_id']);

            if ($bookIndex === null) {
                unset($request);
                return 'Book not found.';
            }

            $availableQuantity = (int) ($books[$bookIndex]['available_quantity'] ?? $books[$bookIndex]['availableQuantity'] ?? 0);

            if ($availableQuantity <= 0) {
                unset($request);
                return 'This book is currently unavailable.';
            }

            $request['status'] = 'approved';
            $targetRequest = $request;

            $books[$bookIndex]['available_quantity'] = $availableQuantity - 1;
            $books[$bookIndex]['borrowed_quantity'] = (int) ($books[$bookIndex]['borrowed_quantity'] ?? $books[$bookIndex]['borrowedQuantity'] ?? 0) + 1;

            break;
        }
        unset($request);

        if ($targetRequest === null) {
            return 'Request not found.';
        }

        $ids = array_column($borrowings, 'id');
        $newBorrowingId = empty($ids) ? 1 : max($ids) + 1;

        $borrowings[] = [
            'id' => $newBorrowingId,
            'user_id' => (int) $targetRequest['user_id'],
            'book_id' => (int) $targetRequest['book_id'],
            'borrow_date' => date('Y-m-d'),
            'return_date' => date('Y-m-d', strtotime('+14 days')),
            'status' => 'active',
        ];

        $this->writeDataFile($this->requestsFile, $requests);
        $this->writeDataFile($this->borrowingsFile, $borrowings);
        $this->writeDataFile($this->booksFile, $books);

        return 'Request #' . $requestId . ' approved successfully.';
    }

    public function rejectRequest(int $requestId): string
    {
        $requests = $this->readDataFile($this->requestsFile);

        foreach ($requests as &$request) {
            if ((int) $request['id'] !== $requestId) {
                continue;
            }

            if ((string) $request['status'] !== 'pending') {
                unset($request);
                return 'This request has already been processed.';
            }

            $request['status'] = 'rejected';
            $this->writeDataFile($this->requestsFile, $requests);
            unset($request);

            return 'Request #' . $requestId . ' rejected successfully.';
        }
        unset($request);

        return 'Request not found.';
    }

    public function markBorrowingReturned(int $borrowingId): string
    {
        $borrowings = $this->readDataFile($this->borrowingsFile);
        $books = $this->readDataFile($this->booksFile);

        foreach ($borrowings as &$borrowing) {
            if ((int) $borrowing['id'] !== $borrowingId) {
                continue;
            }

            if ((string) $borrowing['status'] !== 'active') {
                unset($borrowing);
                return 'This borrowing is already returned.';
            }

            $borrowing['status'] = 'returned';

            $bookIndex = $this->findBookIndex($books, (int) $borrowing['book_id']);

            if ($bookIndex !== null) {
                $books[$bookIndex]['available_quantity'] = (int) ($books[$bookIndex]['available_quantity'] ?? $books[$bookIndex]['availableQuantity'] ?? 0) + 1;
                $books[$bookIndex]['borrowed_quantity'] = max(
                    0,
                    (int) ($books[$bookIndex]['borrowed_quantity'] ?? $books[$bookIndex]['borrowedQuantity'] ?? 0) - 1
                );
            }

            $this->writeDataFile($this->borrowingsFile, $borrowings);
            $this->writeDataFile($this->booksFile, $books);
            unset($borrowing);

            return 'Borrowing #' . $borrowingId . ' marked as returned successfully.';
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

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readDataFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $data = require $filePath;

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<int, array<string, mixed>> $data
     */
    private function writeDataFile(string $filePath, array $data): void
    {
        $export = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($data, true) . ";\n";
        file_put_contents($filePath, $export);
    }

    /**
     * @param array<int, array<string, mixed>> $books
     */
    private function findBookIndex(array $books, int $bookId): ?int
    {
        foreach ($books as $index => $book) {
            if ((int) ($book['id'] ?? 0) === $bookId) {
                return $index;
            }
        }

        return null;
    }

    private function getUserNameById(int $userId): string
    {
        $users = $this->readDataFile($this->usersFile);

        foreach ($users as $user) {
            if ((int) ($user['id'] ?? 0) === $userId) {
                return (string) ($user['name'] ?? 'Unknown User');
            }
        }

        return 'Unknown User';
    }

    private function getBookTitleById(int $bookId): string
    {
        $books = $this->readDataFile($this->booksFile);

        foreach ($books as $book) {
            if ((int) ($book['id'] ?? 0) === $bookId) {
                return (string) ($book['title'] ?? 'Unknown Book');
            }
        }

        return 'Unknown Book';
    }
}
