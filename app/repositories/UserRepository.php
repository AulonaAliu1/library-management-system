<?php declare(strict_types=1);
require_once __DIR__ . '/../core/Database.php';
class UserRepository{
    public function all(): array {
        $connection = Database::connection();
        if(! $connection instanceof PDO) {
            return[];
        }
        $statement = $connection->query('SELECT id, name, username, email, role FROM users ORDER BY id ASC');
        return $statement->fetchAll();
    }
    public function findByUsernameOrEmail(string $identifier): ?array
    {
        $connection = Database:: connection();
        if(!$connection instanceof PDO){
            return null;
        }
        $statement = $connection->prepare(
            'SELECT id, name, username, email,role, password FROM users
            WHERE username = :username OR email = :email
            LIMIT 1'
        );
        $statement->execute([
            'username'=> $identifier,
            'email'=>$identifier,
        ]);
        $user = $statement->fetch();
        return is_array($user) ? $user : null;}
        public function create(array $data): bool {
            $connection = Database::connection();
            if(! $connection instanceof PDO){
                return false;
            }
            $statement = $connection->prepare(
                'INSERT INTO users (name, username, email, role, password)
                VALUES (:name, :username, :email, :role, :password)'

            );
            return $statement->execute([
                'name'=> $data['name'],
                'username'=>$data['username'],
                'email'=> $data['email'],
                'role'=>$data['role'],
                'password'=> password_hash((string) $data['password'], PASSWORD_DEFAULT),

            ]);

        }
        public function findByEmail(string $email): ?array {
            $connection = Database::connection();
            if(! $connection instanceof PDO){
                return null;
            }
            $statement = $connection->prepare(
                'SELECT id, name, username, email, role FROM users WHERE email = :email LIMIT 1'

            );
            $statement->execute(['email'=>$email]);
            $user = $statement->fetch();
            return is_array($user) ? $user : null;
             }
            public function createPasswordReset(int $userId, string $tokenHash, string $expiresAt): bool{
                $connection = Database::connection();
                if(! $connection instanceof PDO){
                    return false;
                }
                $delete = $connection->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
                $delete->execute(['user_id' => $userId]);
                $statement = $connection->prepare(
                    'INSERT INTO password_resets (user_id, token_hash, expires_at)
                    VALUES (:user_id, :token_hash, :expires_at)'

                );
                return $statement->execute([
                    'user_id'=>$userId,
                    'token_hash'=> $tokenHash,
                    'expires_at'=> $expiresAt,
                ]);
            }
            public function findValidPasswordReset (string $tokenHash): ?array{
                $connection = Database::connection();
                if(! $connection instanceof PDO){
                    return null;
                }
                $statement = $connection->prepare(
                    'SELECT password_resets.*, users.email
                    FROM password_resets
                    INNER JOIN users ON users.id = password_resets.user_id
                    WHERE password_resets.token_hash = :token_hash
                    AND password_resets.used_at IS NULL
                    AND passwod_resets.expires_at > NOW()
                    LIMIT 1'
                );
                $statement->execute(['token_hash' => $tokenHash]);
                $reset = $statement->fetch();
                return is_array($reset)? $reset : null; }
                public function updatePassword(int $userId, string $password): bool{
                    $connection = Database::connection();
                    if(! $connection instanceof PDO){
                        return false;
                    }
                    $statement = $connection->prepare('UPDATE users SET password = :password WHERE id = :id');
                    return $statement->execute([
                        'id'=>$userId,
                        'password'=> password_hash($password, PASSWORD_DEFAULT),

                    ]);

                }
                public function markPasswordResetUsed(int $resetId): bool{
                    $connection = Database::connection();
                    if(! $connection instanceof PDO){
                        return false;
                    }
                    $statement = $connection->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
                     return $statement->execute(['id'=>$resetId]);
                }


            }
        
    
