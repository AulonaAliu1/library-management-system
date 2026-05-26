<?php
declare(strict_types=1);

class Book
{
    private int $id;
    private string $title;
    private string $author;
    private string $category;
    private string $description;
    private string $isbn;
    private int $totalQuantity;
    private int $availableQuantity;
    private int $borrowedQuantity;
    private ?string $imagePath;
    private string $status;

    public function __construct(
        int $id,
        string $title,
        string $author,
        string $category,
        string $description,
        string $isbn,
        int $totalQuantity,
        int $availableQuantity,
        int $borrowedQuantity,
        ?string $imagePath = null,
        string $status = 'active'
    ) {
        $this->setId($id);
        $this->setTitle($title);
        $this->setAuthor($author);
        $this->setCategory($category);
        $this->setDescription($description);
        $this->setIsbn($isbn);
        $this->setTotalQuantity($totalQuantity);
        $this->setAvailableQuantity($availableQuantity);
        $this->setBorrowedQuantity($borrowedQuantity);
        $this->setImagePath($imagePath);
        $this->setStatus($status);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = max(0, $id);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = trim($title);
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = trim($author);
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = trim($category);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = trim($description);
    }

    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): void
    {
        $this->isbn = trim($isbn);
    }

    public function getTotalQuantity(): int
    {
        return $this->totalQuantity;
    }

    public function setTotalQuantity(int $totalQuantity): void
    {
        $this->totalQuantity = max(0, $totalQuantity);
    }

    public function getAvailableQuantity(): int
    {
        return $this->availableQuantity;
    }

    public function setAvailableQuantity(int $availableQuantity): void
    {
        $this->availableQuantity = max(0, $availableQuantity);
    }

    public function getBorrowedQuantity(): int
    {
        return $this->borrowedQuantity;
    }

    public function setBorrowedQuantity(int $borrowedQuantity): void
    {
        $this->borrowedQuantity = max(0, $borrowedQuantity);
    }

    public function isAvailable(): bool
    {
        return $this->availableQuantity > 0;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): void{
        $this->imagePath = $imagePath ? trim($imagePath) : null;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = in_array($status, ['active', 'archived'], true) ? $status : 'active';
    }
}
