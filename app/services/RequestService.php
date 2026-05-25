<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/RequestRepository.php';
require_once __DIR__ . '/../repositories/BorrowingRepository.php';

final class RequestService
{
    public function __construct(
        private PDO $pdo,
        private RequestRepository $requestRepository,
        private BorrowingRepository $borrowingRepository
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRequestsForUser(int $userId, bool $isAdmin): array
    {
        if ($isAdmin) {
            return $this->requestRepository->getAllWithDetails();
        }

        return $this->requestRepository->getByUserIdWithDetails($userId);
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
     * @return array{success: bool, message: string}
     */
    public function approveRequest(int $requestId): array
    {
        $request = $this->requestRepository->findById($requestId);

        if ($request === null) {
            return $this->result(false, 'Request not found.');
        }

        if ((string) $request['status'] !== 'pending') {
            return $this->result(false, 'This request has already been processed.');
        }

        $book = $this->findBookById((int) $request['book_id']);

        if ($book === null) {
            return $this->result(false, 'Related book not found.');
        }

        if ((int) $book['available_quantity'] <= 0) {
            return $this->result(false, 'This book is currently unavailable.');
        }

        $this->pdo->beginTransaction();

        try {
            if (! $this->requestRepository->updateStatus($requestId, 'approved')) {
                throw new RuntimeException('Unable to update request status.');
            }

            $this->borrowingRepository->createBorrowing(
                (int) $request['user_id'],
                (int) $request['book_id']
            );

            if (! $this->decreaseBookAvailability((int) $request['book_id'])) {
                throw new RuntimeException('Unable to update book availability.');
            }

            $this->pdo->commit();

            return $this->result(true, 'Request approved successfully.');
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return $this->result(false, 'Unable to approve the request.');
        }
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function rejectRequest(int $requestId): array
    {
        $request = $this->requestRepository->findById($requestId);

        if ($request === null) {
            return $this->result(false, 'Request not found.');
        }

        if ((string) $request['status'] !== 'pending') {
            return $this->result(false, 'This request has already been processed.');
        }

        try {
            if (! $this->requestRepository->updateStatus($requestId, 'rejected')) {
                throw new RuntimeException('Unable to update request status.');
            }

            return $this->result(true, 'Request rejected successfully.');
        } catch (Throwable $exception) {
            return $this->result(false, 'Unable to reject the request.');
        }
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function createRequest(int $userId, int $bookId): array
    {
        if ($userId <= 0 || $bookId <= 0) {
            return $this->result(false, 'Unable to create the request.');
        }

        $book = $this->findBookById($bookId);

        if ($book === null) {
            return $this->result(false, 'Selected book does not exist.');
        }

        if ((int) $book['available_quantity'] <= 0) {
            return $this->result(false, 'This book is currently unavailable.');
        }

        try {
            $this->requestRepository->createRequest($userId, $bookId);

            return $this->result(true, 'Book request created successfully.');
        } catch (Throwable $exception) {
            return $this->result(false, 'Unable to create the request.');
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDetailedRequestById(int $requestId): ?array
    {
        return $this->requestRepository->findDetailedById($requestId);
    }

    public function formatDate(?string $date): string
    {
        if ($date === null || $date === '') {
            return '-';
        }

        $timestamp = strtotime($date);

        return $timestamp === false ? $date : date('d M Y', $timestamp);
    }

    public function getRequestStatusClass(string $status): string
    {
        return match ($status) {
            'pending' => 'status-pending',
            'approved' => 'status-approved',
            'rejected' => 'status-rejected',
            default => 'status-default',
        };
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function countByStatus(array $items, string $status): int
    {
        return count(array_filter(
            $items,
            fn (array $item): bool => (string) ($item['status'] ?? '') === $status
        ));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findBookById(int $bookId): ?array
    {
        $sql = 'SELECT id, available_quantity
                FROM books
                WHERE id = :id
                LIMIT 1';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $bookId]);

        $book = $statement->fetch(PDO::FETCH_ASSOC);

        return $book !== false ? $book : null;
    }

    private function decreaseBookAvailability(int $bookId): bool
    {
        $sql = 'UPDATE books
                SET available_quantity = available_quantity - 1,
                    borrowed_quantity = borrowed_quantity + 1
                WHERE id = :id
                  AND available_quantity > 0';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $bookId]);

        return $statement->rowCount() === 1;
    }

    /**
     * @return array{success: bool, message: string}
     */
    private function result(bool $success, string $message): array
    {
        return [
            'success' => $success,
            'message' => $message,
        ];
    }
}
