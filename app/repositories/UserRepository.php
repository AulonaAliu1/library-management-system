<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

class UserRepository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return [];
        }

        $statement = $connection->query(
            'SELECT id, name, username, email, role, created_at
             FROM users
             ORDER BY created_at DESC, id DESC'
        );

        return $statement->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function members(): array
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return [];
        }

        $statement = $connection->prepare(
            'SELECT id, name, username, email, role, created_at
             FROM users
             WHERE role = :role
             ORDER BY created_at DESC, id DESC'
        );
        $statement->execute(['role' => 'member']);

        return $statement->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return null;
        }

        $statement = $connection->prepare(
            'SELECT id, name, username, email, role, password, created_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findMemberById(int $id): ?array
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return null;
        }

        $statement = $connection->prepare(
            'SELECT id, name, username, email, role, password, created_at
             FROM users
             WHERE id = :id
               AND role = :role
             LIMIT 1'
        );
        $statement->execute([
            'id' => $id,
            'role' => 'member',
        ]);

        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByUsernameOrEmail(string $identifier): ?array
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return null;
        }

        $statement = $connection->prepare(
            'SELECT id, name, username, email, role, password
             FROM users
             WHERE username = :username OR email = :email
             LIMIT 1'
        );
        $statement->execute([
            'username' => $identifier,
            'email' => $identifier,
        ]);

        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    public function create(array $data): bool
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return false;
        }

        $statement = $connection->prepare(
            'INSERT INTO users (name, username, email, role, password)
             VALUES (:name, :username, :email, :role, :password)'
        );

        return $statement->execute([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
        ]);
    }

    public function updateMember(int $id, array $data): bool
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return false;
        }

        $sql = 'UPDATE users
                SET name = :name,
                    username = :username,
                    email = :email,
                    role = :role';

        $params = [
            'id' => $id,
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role'],
        ];

        if ((string) ($data['password'] ?? '') !== '') {
            $sql .= ',
                    password = :password';
            $params['password'] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
        }

        $sql .= '
                WHERE id = :id
                  AND role = :member_role';
        $params['member_role'] = 'member';

        $statement = $connection->prepare($sql);

        return $statement->execute($params);
    }

    public function usernameExists(string $username, ?int $exceptId = null): bool
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return false;
        }

        $sql = 'SELECT id
                FROM users
                WHERE username = :username';
        $params = ['username' => $username];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }
        
        $sql .= ' LIMIT 1';

        $statement = $connection->prepare($sql);
        $statement->execute($params);

        return $statement->fetch() !== false;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return null;
        }

        $statement = $connection->prepare(
            'SELECT id, name, username, email, role
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);

        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return false;
        }

        $sql = 'SELECT id
                FROM users
                WHERE email = :email';
        $params = ['email' => $email];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }

        $sql .= ' LIMIT 1';

        $statement = $connection->prepare($sql);
        $statement->execute($params);

        return $statement->fetch() !== false;
    }

    public function createPasswordReset(int $userId, string $tokenHash, string $expiresAt): bool
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return false;
        }

        $delete = $connection->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
        $delete->execute(['user_id' => $userId]);

        $statement = $connection->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at)
             VALUES (:user_id, :token_hash, :expires_at)'
        );

        return $statement->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findValidPasswordReset(string $tokenHash): ?array
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return null;
        }

        $statement = $connection->prepare(
            'SELECT password_resets.*, users.email
             FROM password_resets
             INNER JOIN users ON users.id = password_resets.user_id
             WHERE password_resets.token_hash = :token_hash
               AND password_resets.used_at IS NULL
               AND password_resets.expires_at > NOW()
             LIMIT 1'
        );
        $statement->execute(['token_hash' => $tokenHash]);

        $reset = $statement->fetch();

        return is_array($reset) ? $reset : null;
    }

    public function updatePassword(int $userId, string $password): bool
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return false;
        }

        $statement = $connection->prepare(
            'UPDATE users
             SET password = :password
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $userId,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    public function markPasswordResetUsed(int $resetId): bool
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return false;
        }

        $statement = $connection->prepare(
            'UPDATE password_resets
             SET used_at = NOW()
             WHERE id = :id'
        );

        return $statement->execute(['id' => $resetId]);
    }

    public function delete(int $id): bool
    {
        $connection = Database::connection();

        if (! ($connection instanceof PDO)) {
            return false;
        }

        $statement = $connection->prepare(
            'DELETE FROM users
             WHERE id = :id
               AND role = :role'
        );

        return $statement->execute([
            'id' => $id,
            'role' => 'member',
        ]);
    }
}
    