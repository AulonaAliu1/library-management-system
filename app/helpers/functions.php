<?php
declare(strict_types=1);

/**
 * Shared helpers qe mundemi me perdore ne te gjithe projektin
 */

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
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
