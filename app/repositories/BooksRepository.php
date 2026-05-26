<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

class BookRepository{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $connection = $db ?? Database::connection();

        if (! ($connection instanceof PDO)) {
            throw new RuntimeException('Database connection is not available.');
        }

        $this->db = $connection;
    }

    public function findAll(bool $includeArchived = false): array{
        $sql = "SELECT * FROM books";

        if (! $includeArchived) {
            $sql .= " WHERE status = 'active'";
        }

        $sql .= " ORDER BY title ASC";

        $stmt = $this->db->query($sql);
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
        $sql = "INSERT INTO books(title, author, category, isbn, total_quantity, available_quantity, borrowed_quantity, description, image_path, status)
                VALUES (:title, :author, :category, :isbn, :total_quantity, :available_quantity, 0, :description, :image_path, :status)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
        ':title' => $data['title'],
        ':author' => $data['author'],
        ':category' => $data['category'],
        ':isbn' => $data['isbn'],
        ':total_quantity' => $data['total_quantity'],
        ':available_quantity' => $data['total_quantity'],
        ':description' => $data['description'],
        ':image_path' => $data['image_path'] ?? null,
        ':status' => $data['status'] ?? 'active'
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
                    available_quantity = CASE
                        WHEN borrowed_quantity >= :total_qty_check THEN 0
                        ELSE :total_qty_calc - borrowed_quantity
                    END";
        
        $params = [
            ':id' => $id,
            ':title' => trim((string)($data['title'] ?? '')),
            ':author' => trim((string)($data['author'] ?? '')),
            ':category' => trim((string)($data['category'] ?? '')),
            ':isbn' => trim((string)($data['isbn'] ?? '')),
            ':total_quantity' => (int)($data['total_quantity'] ?? 0),
            ':total_qty_check' => (int)($data['total_quantity'] ?? 0),
            ':total_qty_calc' => (int)($data['total_quantity'] ?? 0),
            ':description' => trim((string)($data['description'] ?? ''))
        ];
        
        if (isset($data['image_path']) && $data['image_path'] !== null && $data['image_path'] !== '') {
            $sql .= ", image_path = :image_path";
            $params[':image_path'] = $data['image_path'];
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }

    public function updateQuantity(int $id, int $quantity): bool{
        $sql = "UPDATE books SET
                    total_quantity = :total_quantity,
                    available_quantity = CASE
                        WHEN borrowed_quantity >= :total_qty_check THEN 0
                        ELSE :total_qty_calc - borrowed_quantity
                    END
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':total_quantity' => $quantity,
            ':total_qty_check' => $quantity,
            ':total_qty_calc' => $quantity,
        ]);
    }

    public function archive(int $id): bool{
        $stmt = $this->db->prepare("UPDATE books SET status = :status WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':status' => 'archived',
        ]);
    }

    public function restore(int $id): bool{
        $stmt = $this->db->prepare("UPDATE books SET status = :status WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':status' => 'active',
        ]);
    }
}
