<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $connection = null;
    private static ?string $lastError = null;

    public static function connection(): ?PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require __DIR__ . '/../config/database.php';

        $host = (string) ($config['host'] ?? '127.0.0.1');
        $port = (string) ($config['port'] ?? '3306');
        $database = (string) ($config['database'] ?? '');
        $charset = (string) ($config['charset'] ?? 'utf8mb4');
        $username = (string) ($config['username'] ?? 'root');
        $password = (string) ($config['password'] ?? '');

        try {
            self::$connection = new PDO(
                "mysql:host={$host};port={$port};dbname={$database};charset={$charset}",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            self::$lastError = null;
        } catch (PDOException $exception) {
            self::$connection = null;
            self::$lastError = $exception->getMessage();
        }

        return self::$connection;
    }

    public static function lastError(): ?string
    {
        return self::$lastError;
    }
}
