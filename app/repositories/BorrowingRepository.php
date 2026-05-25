<?php
declare(strict_types=1);

final class BorrowingRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllWithDetails(): array
    {
        $sql = 'SELECT
                    br.id,
                    br.user_id,
                    br.book_id,
                    br.borrow_date,
                    br.return_date,
                    br.status,
                    u.name AS member_name,
                    b.title AS book_title
                FROM borrowings br
                INNER JOIN users u ON u.id = br.user_id
                INNER JOIN books b ON b.id = br.book_id
                ORDER BY br.borrow_date DESC, br.id DESC';

        $statement = $this->pdo->query($sql);

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getByUserIdWithDetails(int $userId): array
    {
        $sql = 'SELECT
                    br.id,
                    br.user_id,
                    br.book_id,
                    br.borrow_date,
                    br.return_date,
                    br.status,
                    u.name AS member_name,
                    b.title AS book_title
                FROM borrowings br
                INNER JOIN users u ON u.id = br.user_id
                INNER JOIN books b ON b.id = br.book_id
                WHERE br.user_id = :user_id
                ORDER BY br.borrow_date DESC, br.id DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $borrowingId): ?array
    {
        $sql = 'SELECT *
                FROM borrowings
                WHERE id = :id
                LIMIT 1';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $borrowingId]);

        $borrowing = $statement->fetch(PDO::FETCH_ASSOC);

        return $borrowing !== false ? $borrowing : null;
    }

    public function createBorrowing(int $userId, int $bookId): int
    {
        $sql = 'INSERT INTO borrowings (
                    user_id,
                    book_id,
                    borrow_date,
                    return_date,
                    status
                ) VALUES (
                    :user_id,
                    :book_id,
                    CURDATE(),
                    DATE_ADD(CURDATE(), INTERVAL 14 DAY),
                    :status
                )';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'user_id' => $userId,
            'book_id' => $bookId,
            'status' => 'active',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function markReturned(int $borrowingId): bool
    {
        $sql = 'UPDATE borrowings
                SET status = :status
                WHERE id = :id';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'status' => 'returned',
            'id' => $borrowingId,
        ]);

        return $statement->rowCount() === 1;
    }
}
