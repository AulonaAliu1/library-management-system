<?php
declare(strict_types=1);

/**
 * Sample active / historical borrowings — Phase I dummy data only.
 */
return [
    [
        'id' => 1,
        'user_id' => 2,
        'book_id' => 1,
        'borrow_date' => '2026-03-10',
        'return_date' => '2026-03-24',
        'status' => 'active',
    ],
    [
        'id' => 2,
        'user_id' => 4,
        'book_id' => 3,
        'borrow_date' => '2026-02-01',
        'return_date' => '2026-02-15',
        'status' => 'returned',
    ],
    [
        'id' => 3,
        'user_id' => 3,
        'book_id' => 2,
        'borrow_date' => '2026-03-05',
        'return_date' => '2026-03-19',
        'status' => 'active',
    ],
    [
        'id' => 4,
        'user_id' => 5,
        'book_id' => 3,
        'borrow_date' => '2026-01-12',
        'return_date' => '2026-01-26',
        'status' => 'returned',
    ],
];
