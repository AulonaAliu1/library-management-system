<?php
declare(strict_types=1);

class AuthService
{
    /**
     * returns the user dummy data, we will remove in phase 2 
     */
    public function getHardcodedUsers(): array
    {
        return require __DIR__ . '/../data/users-data.php';
    }

    public function authenticate(string $usernameOrEmail, string $password): ?array
    {
        $identifier = trim($usernameOrEmail);

        if ($identifier === '' || $password === '') {
            return null;
        }

        $users = $this->getHardcodedUsers();

        foreach ($users as $user) {
            $username = isset($user['username']) ? trim((string) $user['username']) : '';
            $email = isset($user['email']) ? trim((string) $user['email']) : '';

            $matchesUsername = $username !== '' && strcasecmp($username, $identifier) === 0;
            $matchesEmail = $email !== '' && strcasecmp($email, $identifier) === 0;

            if (! $matchesUsername && ! $matchesEmail) {
                continue;
            }
            //This needs to be updated in phase 2 then we will compare hashes not plaintext
            $storedPassword = isset($user['password']) ? (string) $user['password'] : '';

            if ($storedPassword !== $password) {
                return null;
            }

            return $this->sanitizeUser($user);
        }

        return null;
    }
    /**
     * This was added so we dont send the users password to login in case we use it in Session
     */
    private function sanitizeUser(array $user): array
    {
        unset($user['password']);

        return $user;
    }
}
