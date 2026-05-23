<?php
declare(strict_types=1);

final class RequestRepository
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
                    r.id,
                    r.user_id,
                    r.book_id,
                    r.status,
                    r.request_date,
                    u.name AS member_name,
                    b.title AS book_title
                FROM requests r
                INNER JOIN users u ON u.id = r.user_id
                INNER JOIN books b ON b.id = r.book_id
                ORDER BY r.request_date DESC, r.id DESC';

        $statement = $this->pdo->query($sql);

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getByUserIdWithDetails(int $userId): array
    {
        $sql = 'SELECT
                    r.id,
                    r.user_id,
                    r.book_id,
                    r.status,
                    r.request_date,
                    u.name AS member_name,
                    b.title AS book_title
                FROM requests r
                INNER JOIN users u ON u.id = r.user_id
                INNER JOIN books b ON b.id = r.book_id
                WHERE r.user_id = :user_id
                ORDER BY r.request_date DESC, r.id DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $requestId): ?array
    {
        $sql = 'SELECT *
                FROM requests
                WHERE id = :id
                LIMIT 1';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $requestId]);

        $request = $statement->fetch(PDO::FETCH_ASSOC);

        return $request !== false ? $request : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findDetailedById(int $requestId): ?array
    {
        $sql = 'SELECT
                    r.id,
                    r.user_id,
                    r.book_id,
                    r.status,
                    r.request_date,
                    u.name AS member_name,
                    b.title AS book_title
                FROM requests r
                INNER JOIN users u ON u.id = r.user_id
                INNER JOIN books b ON b.id = r.book_id
                WHERE r.id = :id
                LIMIT 1';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $requestId]);

        $request = $statement->fetch(PDO::FETCH_ASSOC);

        return $request !== false ? $request : null;
    }

    public function createRequest(int $userId, int $bookId): int
    {
        $sql = 'INSERT INTO requests (user_id, book_id, status, request_date)
                VALUES (:user_id, :book_id, :status, CURDATE())';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'user_id' => $userId,
            'book_id' => $bookId,
            'status' => 'pending',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateStatus(int $requestId, string $status): bool
    {
        $sql = 'UPDATE requests
                SET status = :status
                WHERE id = :id';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'status' => $status,
            'id' => $requestId,
        ]);
    }
}
