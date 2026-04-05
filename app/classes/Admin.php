<?php
declare(strict_types=1);

require_once __DIR__ . '/User.php';

class Admin extends User
{
    public function __construct(int $id, string $username, string $email)
    {
        parent::__construct($id, $username, 'admin', $email);
    }
}
