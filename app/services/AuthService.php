<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/UserRepository.php';

class AuthService
{
    private UserRepository $users;

    public function __construct(?UserRepository $users = null)
    {
        $this->users = $users ?? new UserRepository();
    }

    public function authenticate(string $usernameOrEmail, string $password): ?array
    {
        $identifier = trim($usernameOrEmail);

        if ($identifier === '' || $password === '') {
            return null;
        }

        $user = $this->users->findByUsernameOrEmail($identifier);

        if ($user === null) {
            return null;
        }

        $storedPassword = (string) ($user['password'] ?? '');

        if (! password_verify($password, $storedPassword)) {
            return null;
        }

        return $this->sanitizeUser($user);
    }

    public function createPasswordReset(string $email): ?string
    {
        $email = trim($email);

        if ($email === '') {
            return null;
        }

        $user = $this->users->findByEmail($email);

        if ($user === null) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        if (! $this->users->createPasswordReset((int) $user['id'], $tokenHash, $expiresAt)) {
            return null;
        }

        return $token;
    }

    public function resetPassword(string $token, string $password): bool
    {
        $token = trim($token);

        if ($token === '' || $password === '') {
            return false;
        }

        $reset = $this->users->findValidPasswordReset(hash('sha256', $token));

        if ($reset === null) {
            return false;
        }

        if (! $this->users->updatePassword((int) $reset['user_id'], $password)) {
            return false;
        }

        $this->users->markPasswordResetUsed((int) $reset['id']);

        return true;
    }

    private function sanitizeUser(array $user): array
    {
        unset($user['password']);

        return [
            'id' => (int) ($user['id'] ?? 0),
            'name' => (string) ($user['name'] ?? ''),
            'username' => (string) ($user['username'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'role' => (string) ($user['role'] ?? 'member'),
        ];
    }
}
