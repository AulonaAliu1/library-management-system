<?php
declare(strict_types=1);

/**
 * Dummy users — no database. Shape: id, username, email, role, password (plain for template only; replace in real auth).
 */
return [
    [
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@library.local',
        'role' => 'admin',
        'password' => 'admin123',
    ],
    [
        'id' => 2,
        'username' => 'member1',
        'email' => 'member1@library.local',
        'role' => 'member',
        'password' => 'member123',
    ],
];
