<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';

require_login();

$pageTitle = 'Profile';
$extraCss = '../../assets/css/profile.css';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';


// ne kete pjes kemi me perdor: Session user, form, RegEx validation

$currentUser=$_SESSION['user'];

?>
<main class="container main-content">
    <h1>Profile</h1>

    <?php
    $errors=[];

    if($_SERVER['REQUEST_METHOD']=="POST"){

    if(isset($_POST['username'])){
        $username=trim($_POST['username']);

    }
    else{
        $username='';

    }
    if($username==''){
        $errors[]="Username is required!";
    }
    else if(preg_match("/^\d+$/", $username)){
        $errors[]="Username cannot be only numbers!";
    }
    else if(!preg_match("/^[a-zA-Z0-9_]+$/", $username)){
        $errors[]="Username can contain only letters, numbers, and underscore!";

    }

    if(isset($_POST['email'])){
        $email=$_POST['email'];

    }
    else{
        $email='';

    }
  if (!preg_match("/^[\w\.-]+@[\w\.-]+\.\w+$/", $email)) {
        $errors[] = "The email its not valid!";
    }

        if(count($errors)==0){
            $_SESSION['user']['email']=$email;
        if(isset($_POST['username'])){
            $_SESSION['user']['username']=$_POST['username'];

        }
        $currentUser=$_SESSION['user'];

        $success="The Profile is Updated!";
       
       }
    }

    ?>
<?php
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p class='invalid-msg'>$error</p>";
    }
}

if (isset($success)) {
    echo "<p class='success-msg'>$success</p>";
}
?>

   <form method="POST" class="profile-form">
    <div>
        <label class="color-label">Username: </label><br>
        <input type="text" name="username" 
        value= "<?php
        if(isset($currentUser['username'])){
            echo $currentUser['username'];
        }
        ?>">
        <br><br>

        <label>Email: </label><br>
        <input type="text" name="email" 
        value="<?php
        if(isset($currentUser['email'])){
            echo $currentUser['email'];

        }
        ?>">
        <br><br>
        <button type="submit"> Update Profile</button>
        <?php
        ?>
    </div>
   </form>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
