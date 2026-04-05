<?php
declare(strict_types=1);

/**
 * Sample books  arrays for BookService / books module.
 */
return [
    [
        'id' => 1,
        'title' => 'Clean Code',
        'author' => 'Robert C. Martin',
        'category' => 'Software Engineering',
        'description' => 'A handbook of agile software craftsmanship.',
        'isbn' => '978-0132350884',
        'totalQuantity' => 5,
        'availableQuantity' => 3,
        'borrowedQuantity' => 2,
    ],
    [
        'id' => 2,
        'title' => 'Introduction to Algorithms',
        'author' => 'Cormen et al.',
        'category' => 'Computer Science',
        'description' => 'Classic algorithms textbook.',
        'isbn' => '978-0262033848',
        'totalQuantity' => 4,
        'availableQuantity' => 4,
        'borrowedQuantity' => 0,
    ],
    [
        'id' => 3,
        'title' => 'The Pragmatic Programmer',
        'author' => 'Hunt and Thomas',
        'category' => 'Software Engineering',
        'description' => 'Tips for modern software development.',
        'isbn' => '978-0135957059',
        'totalQuantity' => 6,
        'availableQuantity' => 5,
        'borrowedQuantity' => 1,
    ],
];
