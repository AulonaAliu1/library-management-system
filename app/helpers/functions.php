<?php
declare(strict_types=1);

/**
 * Shared helpers qe mundemi me perdore ne te gjithe projektin
 */
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/security.php';

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Me i shtu flash messages in later implementaition
 */
function flash_set(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

/**
 * @return string|null
 */
function flash_get(string $key): ?string
{
    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }
    $msg = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return is_string($msg) ? $msg : null;
}
// Funksion me  prevent XSS edhe per te mos shkaktu break te html ne rast te inputeve si <, " etj.
function h(?string $value): string
{
    return e($value);
}

/**
 * Returns the authenticated user stored in session, if available.
 */
function current_user(): ?array
{
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        return null;
    }

    return $_SESSION['user'];
}

function is_logged_in(): bool
{
             
    return current_user() !== null;
}

function current_role(): ?string
{
    $user = current_user();

    if ($user === null || !isset($user['role']) || !is_string($user['role']) || $user['role'] === '') {
        $sessionRole = $_SESSION['role'] ?? null;

        return is_string($sessionRole) && $sessionRole !== '' ? strtolower($sessionRole) : null;
    }

    return strtolower($user['role']);
}

function is_admin(): bool
{
    return current_role() === 'admin';
}

function is_member(): bool
{
    return current_role() === 'member';
}
