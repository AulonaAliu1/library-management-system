<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/security.php';

require_login();

$pageTitle = 'Settings';
$extraCss = '../../assets/css/settings.css';
$allowedThemes = ['light', 'dark'];
$allowedFontSizes = ['normal', 'large'];

if($_SERVER['REQUEST_METHOD']=='POST'){
    if (! csrf_check((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Security check failed. Please try again.');
        header("Location: settings.php");
        exit;
    }

    $theme = (string) ($_POST['theme'] ?? ($_COOKIE['theme'] ?? 'light'));
    $fontSize = (string) ($_POST['font_size'] ?? ($_COOKIE['font_size'] ?? 'normal'));

    if (! in_array($theme, $allowedThemes, true)) {
        $theme = 'light';
    }

    if (! in_array($fontSize, $allowedFontSizes, true)) {
        $fontSize = 'normal';
    }

    setcookie('theme', $theme, time() + (86400 * 30), "/");
    setcookie('font_size', $fontSize,time()+(86400*30),"/");
    header("Location: settings.php");
    exit;
}
$theme = $_COOKIE['theme'] ?? 'light';
$fontSize = $_COOKIE['font_size'] ?? 'normal';


require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
// ketu kemi me rujt theme tu perdorr cookies
$success=null;

$selectedSmall = '';
$selectedNormal = '';
$selectedLarge = '';
$selectedXLarge = '';

if ($fontSize == 'small') {
    $selectedSmall = 'selected';
} elseif ($fontSize == 'normal') {
    $selectedNormal = 'selected';
} elseif ($fontSize == 'large') {
    $selectedLarge = 'selected';
} elseif ($fontSize == 'xlarge') {
    $selectedXLarge = 'selected';
}

?>

<main class="container main-content">
    <h1>Settings</h1>

    <?php
    if(isset($success)){
       echo "<p class= 'success-msg' >$success</p>";
    }
 
    
    ?>
    
    <form method="POST" class="settings-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Theme: </label><br><br>
    <select name="theme" onchange="this.form.submit()">
    <option value="light" <?php if($theme == 'light'){ echo 'selected'; } ?>>Light</option>
    <option value="dark" <?php if($theme == 'dark'){ echo 'selected'; } ?>>Dark</option>
    </select>
        <br><br>
       <label>Font Size: </label> <br><br>

<select name="font_size" onchange="this.form.submit()">
    <option value="normal" <?php echo $selectedNormal; ?>>Normal</option>
    <option value="large" <?php echo $selectedLarge; ?>>Large</option>
</select>
        <br><br>
        <button type="submit">Save</button>

    </form>

</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
