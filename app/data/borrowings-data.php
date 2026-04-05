<?php
declare(strict_types=1);

/**
 * Sample active / historical borrowings — borrowings module placeholder data.
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
        'user_id' => 2,
        'book_id' => 3,
        'borrow_date' => '2026-02-01',
        'return_date' => '2026-02-15',
        'status' => 'returned',
    ],
];
