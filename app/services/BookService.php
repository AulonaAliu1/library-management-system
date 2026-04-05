<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Book.php';

/**
 * Book catalogue — load dummy data; CRUD comes in Phase I books module.
 */
class BookService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllBooks(): array
    {
        return require __DIR__ . '/../data/books-data.php';
    }

    /**
     * @param array<string, mixed> $row
     */
    public function rowToBook(array $row): Book
    {
        return new Book(
            (int) $row['id'],
            (string) $row['title'],
            (string) $row['author'],
            (string) $row['category'],
            (string) $row['description'],
            (string) $row['isbn'],
            (int) $row['totalQuantity'],
            (int) $row['availableQuantity'],
            (int) $row['borrowedQuantity']
        );
    }
}
