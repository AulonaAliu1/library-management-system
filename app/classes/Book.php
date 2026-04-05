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

    public function __construct(
        int $id,
        string $title,
        string $author,
        string $category,
        string $description,
        string $isbn,
        int $totalQuantity,
        int $availableQuantity,
        int $borrowedQuantity
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->category = $category;
        $this->description = $description;
        $this->isbn = $isbn;
        $this->totalQuantity = $totalQuantity;
        $this->availableQuantity = $availableQuantity;
        $this->borrowedQuantity = $borrowedQuantity;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): void
    {
        $this->isbn = $isbn;
    }

    public function getTotalQuantity(): int
    {
        return $this->totalQuantity;
    }

    public function setTotalQuantity(int $totalQuantity): void
    {
        $this->totalQuantity = $totalQuantity;
    }

    public function getAvailableQuantity(): int
    {
        return $this->availableQuantity;
    }

    public function setAvailableQuantity(int $availableQuantity): void
    {
        $this->availableQuantity = $availableQuantity;
    }

    public function getBorrowedQuantity(): int
    {
        return $this->borrowedQuantity;
    }

    public function setBorrowedQuantity(int $borrowedQuantity): void
    {
        $this->borrowedQuantity = $borrowedQuantity;
    }
}
