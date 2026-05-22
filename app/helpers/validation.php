<?php
declare(strict_types=1);

function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function is_filled(string $value): bool
{
    return trim($value) !== '';
}

function is_valid_role(string $role): bool
{
    return in_array($role, ['admin', 'member'], true);
}

function is_valid_username(string $username): bool
{
    return preg_match('/^[A-Za-z][A-Za-z0-9_]{2,49}$/', $username) === 1;
}

function is_strong_enough_password(string $password): bool
{
    return strlen($password) >= 8;
}
