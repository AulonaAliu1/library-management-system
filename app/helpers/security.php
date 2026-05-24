<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function csrf_check(string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    return hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token);
}
