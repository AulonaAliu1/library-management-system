<?php
declare(strict_types=1);

/**
 * Dummy users — no database. Shape: id, username, email, role, password (plain for template only; replace in real auth).
 */
return [
    [
        'name'=>'Admin',
        'id' => 1,
        'username' => 'Admin',
        'email' => 'admin@library.local',
        'role' => 'admin',
        'password' => 'admin123',
    ],
    [
        'name'=>'Aulona',
        'id' => 2,
        'username' => 'Aulona',
        'email' => 'aulona@library.local',
        'role' => 'member',
        'password' => 'password123',
    ],
    [
        'name'=>'Eliza',
        'id' => 3,
        'username' => 'Eliza',
        'email' => 'eliza@library.local',
        'role' => 'member',
        'password' => 'password123',
    ],
    [
        'name'=>'Erdoart',
        'id' => 4,
        'username' => 'Erdoart',
        'email' => 'erdoart@library.local',
        'role' => 'member',
        'password' => 'password123',
    ],
    [
        'name'=>'Lindrit',
        'id' => 5,
        'username' => 'Lindrit',
        'email' => 'lindrit@library.local',
        'role' => 'member',
        'password' => 'password123',
    ],

];
