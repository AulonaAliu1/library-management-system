<?php
declare(strict_types=1);

if (!defined('LMS_ENTRY')) {
    define('LMS_ENTRY', 'pages');
}

$pageTitle = $pageTitle ?? 'Library Management System';
$assetsCss = $assetsCss ?? (LMS_ENTRY === 'public' ? '../assets/css/style.css' : '../../assets/css/style.css');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string) $pageTitle, ENT_QUOTES, 'UTF-8') ?> — Library Management System</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($assetsCss, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
<div class="page-wrap">
