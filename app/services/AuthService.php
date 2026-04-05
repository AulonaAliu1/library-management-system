<?php
declare(strict_types=1);

/**
 * Authentication — stubs only. Teammate implements sessions / cookies and real checks.
 */
class AuthService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getHardcodedUsers(): array
    {
        return require __DIR__ . '/../data/users-data.php';
    }

    /**
     * TODO: validate credentials, set $_SESSION, optional remember-me cookie.
     *
     * @param array<string, mixed> $users
     */
    public function authenticate(string $usernameOrEmail, string $password, array $users): ?array
    {
        // Stub: no real auth yet
        return null;
    }
}
