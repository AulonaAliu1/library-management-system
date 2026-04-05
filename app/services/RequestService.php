<?php
declare(strict_types=1);

/**
 * Requests / borrowings coordination — workflow TODO.
 */
class RequestService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllRequests(): array
    {
        return require __DIR__ . '/../data/requests-data.php';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllBorrowings(): array
    {
        return require __DIR__ . '/../data/borrowings-data.php';
    }
}
