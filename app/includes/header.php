<?php
declare(strict_types=1);
if(isset($_COOKIE['theme'])){
    $theme=$_COOKIE['theme'];
}
else{
    $theme='light';
}
if(isset($_COOKIE['font_size'])){
    $fontSize=$_COOKIE['font_size'];

}
else{
    $fontSize='normal';

}
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
    <?php if (isset($extraCss)) : ?>
<link rel="stylesheet" href="<?= htmlspecialchars($extraCss) ?>">
<?php endif; ?>
</head>
<body class="<?= $theme ?> <?= $fontSize ?>">
<div class="page-wrap">
