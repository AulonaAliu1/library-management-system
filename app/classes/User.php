<?php
declare(strict_types=1);

/**
 * Base user domain model — extend for Admin / Member.
 */
class User
{
    private int $id;
    private string $username;
    private string $role;
    private string $email;

    public function __construct(int $id, string $username, string $role, string $email)
    {
        $this->id = $id;
        $this->username = $username;
        $this->role = $role;
        $this->email = $email;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
