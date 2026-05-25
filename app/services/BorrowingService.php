<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/BorrowingRepository.php';

final class BorrowingService
{
    public function __construct(
        private PDO $pdo,
        private BorrowingRepository $borrowingRepository
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getBorrowingsForUser(int $userId, bool $isAdmin): array
    {
        if ($isAdmin) {
            return $this->borrowingRepository->getAllWithDetails();
        }

        return $this->borrowingRepository->getByUserIdWithDetails($userId);
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

    public function markReturned(int $borrowingId): string
    {
        $borrowing = $this->borrowingRepository->findById($borrowingId);

        if ($borrowing === null) {
            return 'Borrowing not found.';
        }

        if ((string) $borrowing['status'] !== 'active') {
            return 'This borrowing is already returned.';
        }

        $this->pdo->beginTransaction();

        try {
            if (! $this->borrowingRepository->markReturned($borrowingId)) {
                throw new RuntimeException('Unable to update borrowing status.');
            }

            if (! $this->increaseBookAvailability((int) $borrowing['book_id'])) {
                throw new RuntimeException('Unable to update book availability.');
            }

            $this->pdo->commit();

            return 'Book return confirmed successfully.';
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return 'Unable to confirm the book return.';
        }
    }

    public function formatDate(?string $date): string
    {
        if ($date === null || $date === '') {
            return '-';
        }

        $timestamp = strtotime($date);

        return $timestamp === false ? $date : date('d M Y', $timestamp);
    }

    public function getBorrowingStatusClass(string $status): string
    {
        return match ($status) {
            'active' => 'status-active',
            'returned' => 'status-returned',
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

    private function increaseBookAvailability(int $bookId): bool
    {
        $sql = 'UPDATE books
                SET available_quantity = available_quantity + 1,
                    borrowed_quantity = CASE
                        WHEN borrowed_quantity > 0 THEN borrowed_quantity - 1
                        ELSE 0
                    END
                WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $bookId]);

        return $statement->rowCount() === 1;
    }
}
