<?php
declare(strict_types=1);

/**
 * Sample borrow / hold requests — RequestService will own real logic later.
 */
return [
    [
        'id' => 1,
        'user_id' => 2,
        'book_id' => 1,
        'status' => 'pending',
        'request_date' => '2026-03-15',
    ],
    [
        'id' => 2,
        'user_id' => 2,
        'book_id' => 3,
        'status' => 'approved',
        'request_date' => '2026-03-18',
    ],
];
