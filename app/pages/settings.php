<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';

require_login();

$pageTitle = 'Settings';
$extraCss = '../../assets/css/settings.css';
if($_SERVER['REQUEST_METHOD']=='POST'){
    if (isset($_POST['theme'])){
        $theme = $_POST['theme'];
    }
    else{
        $theme = $_COOKIE['theme'] ?? 'light';

    }
    if (isset($_POST['font_size'])){
    $fontSize = $_POST['font_size'];
    } 
    else {
    $fontSize = $_COOKIE['font_size'] ?? 'normal';
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
        <label>Theme: </label><br><br>
    <select name="theme">
    <option value="light" <?php if($theme == 'light'){ echo 'selected'; } ?>>Light</option>
    <option value="dark" <?php if($theme == 'dark'){ echo 'selected'; } ?>>Dark</option>
    </select>
        <br><br>
       <label>Font Size: </label> <br><br>

<select name="font_size">
    <option value="normal" <?php echo $selectedNormal; ?>>Normal</option>
    <option value="large" <?php echo $selectedLarge; ?>>Large</option>
</select>
        <br><br>
        <button type="submit">Save</button>

    </form>

</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
