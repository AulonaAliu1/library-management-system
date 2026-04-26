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
$allowedThemes = ['light', 'dark'];
$allowedFontSizes = ['normal', 'large'];

if (!in_array($theme, $allowedThemes, true)) {
    $theme = 'light';
}

if (!in_array($fontSize, $allowedFontSizes, true)) {
    $fontSize = 'normal';
}

if (!defined('LMS_ENTRY')) {
    define('LMS_ENTRY', 'pages');
}

$pageTitle = $pageTitle ?? 'Library Management System';
$assetsCss = $assetsCss ?? (LMS_ENTRY === 'public' ? '../assets/css/style.css' : '../../assets/css/style.css');
?>
<!DOCTYPE html>
<html lang="en" class="<?= htmlspecialchars($theme . ' ' . $fontSize, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string) $pageTitle, ENT_QUOTES, 'UTF-8') ?> — Library Management System</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($assetsCss, ENT_QUOTES, 'UTF-8') ?>">
    <?php if (isset($extraCss)) : ?>
<link rel="stylesheet" href="<?= htmlspecialchars($extraCss) ?>">
<?php endif; ?>
</head>
<body class="<?= htmlspecialchars($theme . ' ' . $fontSize, ENT_QUOTES, 'UTF-8') ?>">
<div class="page-wrap">
