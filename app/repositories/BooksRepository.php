<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

class BookRepository{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function findAll(): array{
        $stmt = $this->db->query("SELECT * FROM books ORDER BY title ASC");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array{
        $stmt = $this->db->prepare("SELECT * FROM books WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $book = $stmt->fetch();

        return $book ?: null;
    }

    public function findByIsbn(string $isbn): ?array{
        $stmt = $this->db->prepare("SELECT * FROM books WHERE isbn = :isbn LIMIT 1");
        $stmt->execute([':isbn' => trim($isbn)]);
        $book = $stmt->fetch();

        return $book ?: null;
    }

    public function create(array $data): bool{
        $sql = "INSERT INTO books(title, author, category, isbn, total_quantity, available_quantity, borrowed_quantity, description) 
                VALUES (:title, :author, :category, :isbn, :total_quantity, :available_quantity, 0, :description)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
        ':title' => $data['title'],
        ':author' => $data['author'],
        ':category' => $data['category'],
        ':isbn' => $data['isbn'],
        ':total_quantity' => $data['total_quantity'],
        ':available_quantity' => $data['total_quantity'],
        ':description' => $data['description']
        ]);
    }

    public function update(int $id, array $data): bool{
        $hasImage = isset($data['image_path']) && $data['image_path'] !== null;
        
        $sql = "UPDATE books SET 
                    title = :title, 
                    author = :author, 
                    category = :category, 
                    isbn = :isbn,
                    total_quantity = :total_quantity,
                    description = :description,
                    available_quantity = :total_quantity - borrowed_quantity";
        
        if ($hasImage) {
            $sql .= ", image_path = :image_path";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $params = [
            ':id' => $id,
            ':title' => trim((string)($data['title'] ?? '')),
            ':author' => trim((string)($data['author'] ?? '')),
            ':category' => trim((string)($data['category'] ?? '')),
            ':isbn' => trim((string)($data['isbn'] ?? '')),
            ':total_quantity' => (int)($data['total_quantity'] ?? 0),
            ':description' => trim((string)($data['description'] ?? ''))
        ];
        
        if ($hasImage) {
            $params[':image_path'] = $data['image_path'];
        }
        
        return $stmt->execute($params);
    }

    public function delete(int $id): bool{
        $stmt = $this->db->prepare("DELETE FROM books WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}