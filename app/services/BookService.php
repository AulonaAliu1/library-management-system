<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Book.php';

class BookService
{
    /**
     * @return Book[]
     */
    public function getAllBooks(): array
    {
        $booksData = require __DIR__ . '/../data/books-data.php';

        return array_map(
            fn (array $row): Book => $this->rowToBook($row),
            $booksData
        );
    }

    public function rowToBook(array $row): Book
    {
        return new Book(
            (int) ($row['id'] ?? 0),
            (string) ($row['title'] ?? ''),
            (string) ($row['author'] ?? ''),
            (string) ($row['category'] ?? ''),
            (string) ($row['description'] ?? ''),
            (string) ($row['isbn'] ?? $row['ISBN'] ?? ''),
            (int) ($row['total_quantity'] ?? $row['totalQuantity'] ?? 0),
            (int) ($row['available_quantity'] ?? $row['availableQuantity'] ?? 0),
            (int) ($row['borrowed_quantity'] ?? $row['borrowedQuantity'] ?? 0)
        );
    }

    /**
     * @param Book[] $books
     * @return Book[]
     */
    public function searchBooks(array $books, string $search): array
    {
        $needle = trim($search);

        if ($needle === '') {
            return $books;
        }

        return array_values(array_filter(
            $books,
            function (Book $book) use ($needle): bool {
                $haystack = [
                    $book->getTitle(),
                    $book->getAuthor(),
                    $book->getCategory(),
                    $book->getDescription(),
                    $book->getIsbn(),
                ];

                foreach ($haystack as $value) {
                    if (stripos($value, $needle) !== false) {
                        return true;
                    }
                }

                return false;
            }
        ));
    }

    /**
     * @param Book[] $books
     * @return Book[]
     */
    public function filterByCategory(array $books, string $category): array
    {
        $selectedCategory = trim($category);

        if ($selectedCategory === '') {
            return $books;
        }

        return array_values(array_filter(
            $books,
            fn (Book $book): bool => strcasecmp($book->getCategory(), $selectedCategory) === 0
        ));
    }

    /**
     * @param Book[] $books
     * @return Book[]
     */
    public function sortBooks(array $books, string $sort): array
    {
        switch ($sort) {
            case 'author':
                usort(
                    $books,
                    fn (Book $a, Book $b): int => strcasecmp($a->getAuthor(), $b->getAuthor())
                );
                break;

            case 'available_quantity_desc':
                usort(
                    $books,
                    fn (Book $a, Book $b): int => $b->getAvailableQuantity() <=> $a->getAvailableQuantity()
                );
                break;

            case 'category':
                usort(
                    $books,
                    fn (Book $a, Book $b): int => strcasecmp($a->getCategory(), $b->getCategory())
                );
                break;

            case 'title':
            default:
                usort(
                    $books,
                    fn (Book $a, Book $b): int => strcasecmp($a->getTitle(), $b->getTitle())
                );
                break;
        }

        return $books;
    }

    /**
     * @param Book[] $books
     * @return string[]
     */
    public function getCategories(array $books): array
    {
        $categories = array_map(
            fn (Book $book): string => $book->getCategory(),
            $books
        );

        $categories = array_values(array_unique($categories));
        natcasesort($categories);

        return array_values($categories);
    }
}
