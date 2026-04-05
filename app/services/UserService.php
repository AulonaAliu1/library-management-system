<?php
declare(strict_types=1);

/**
 * Users / members — profile and admin member list will build on this.
 */
class UserService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllMembers(): array
    {
        $users = require __DIR__ . '/../data/users-data.php';
        return array_values(array_filter($users, static fn (array $u): bool => ($u['role'] ?? '') === 'member'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllUsers(): array
    {
        return require __DIR__ . '/../data/users-data.php';
    }
}
