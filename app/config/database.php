<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/env.php';

return [
    'host' => getenv('LMS_DB_HOST') ?: '127.0.0.1',
    'port' => getenv('LMS_DB_PORT') ?: '3307',
    'database' => getenv('LMS_DB_NAME') ?: 'library_management_system',
    'username' => getenv('LMS_DB_USER') ?: 'root',
    'password' => getenv('LMS_DB_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
];
