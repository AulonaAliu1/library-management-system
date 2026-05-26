<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Book.php';
require_once __DIR__ . '/../repositories/BooksRepository.php';

class BookService
{
    private BookRepository $bookRepository;

    public function __construct(?BookRepository $bookRepository = null){
        $this->bookRepository = $bookRepository ?? new BookRepository();
    }

    
    public function getAllBooks(bool $includeArchived = false): array
    {
        $rows = $this->bookRepository->findAll($includeArchived);

        return array_map(
            fn (array $row): Book => $this->rowToBook($row),
            $rows
        );
    }

    public function getBookById(int $id){
        $row = $this->bookRepository->findById($id);
        return $row ? $this->rowToBook($row) : null;
    }

    public function createBook(array $data): bool{
        $quantity = (int)($data['total_quantity'] ?? 0);
        if($quantity < 0){
            return false;
        }
    
        if(isset($data['isbn']) && trim($data['isbn']) !== ''){
            $existing = $this->bookRepository->findByIsbn($data['isbn']);
            if($existing !== null){
                return false;
            }
        }
        return $this->bookRepository->create($data);
    }

    public function updateBook(int $id, array $data): bool{
        $quantity = (int)($data['total_quantity'] ?? 0);
        if($quantity < 0){
            return false;
        }
        return $this->bookRepository->update($id, $data);
    }

    public function updateQuantity(int $id, int $quantity): bool{
        if($quantity < 0){
            return false;
        }

        return $this->bookRepository->updateQuantity($id, $quantity);
    }

    public function deleteBook(int $id): bool{
        return $this->bookRepository->archive($id);
    }

    public function restoreBook(int $id): bool{
        return $this->bookRepository->restore($id);
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
            (int) ($row['borrowed_quantity'] ?? $row['borrowedQuantity'] ?? 0),
            $row['image_path'] ?? null,
            (string) ($row['status'] ?? 'active')
        );
    }

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
