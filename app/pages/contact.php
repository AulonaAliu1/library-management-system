<?php

declare(strict_types=1);

require_once '../core/Database.php';

$pdo = Database::connection();

if (!$pdo) {
    die(Database::lastError());
}

$extraCss='../../assets/css/contact.css';
include '../includes/header.php';
include '../includes/navbar.php';

$message="";
$isSuccess=false;

if($_SERVER['REQUEST_METHOD']=='POST'){
    $name=$_POST['name'];
    $email=$_POST['email'];
    $subject=$_POST['subject'];
    $text=$_POST['message'];

    if(
        empty($name)||
        empty($email)||
        empty($subject) ||
        empty($text)
    ){
$message="All Fields are required!";

    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Invalid email format!";

    }
    else{ 
        try{

        $stmt =$pdo->prepare(
         "INSERT INTO contact_messages(name,email,subject,message)
         VALUES (?,?,?,?) "
         );

           $stmt->execute([
      $name,
      $email,
      $subject,
      $text
]);

        $message = "Message sent successfully!";
        $isSuccess = true;



    }catch(PDOException $e) {

        $message = "Something went wrong!";
    }

}

}

?>



<!DOCTYPE html>
<html>
<head>
    <title>Contact Page</title>
    <link rel="stylesheet" href="../../assets/css/contact.css">
</head>
<body>

<div class="main-content">

    <h1>Contact Us</h1>

    <div class="contact-form">
        <?php if(!empty($message)): ?>


        <p class="<?= $isSuccess?'success-message':'error-message' ?>">
            <?= htmlspecialchars($message) ?>
        </p>

        <?php  endif;?>


        <form method="POST">

            <input
                type="text"
                name="name"
                placeholder="Name"
                required
            >

            <input
                type="email"
                name="email"
                placeholder="Email"
                required
            >

            <input
                type="text"
                name="subject"
                placeholder="Subject"
                required
            >

            <textarea
                name="message"
                placeholder="Message"
                required
            ></textarea>

            <button type="submit">
                Send Message
            </button>

        </form>

    </div>

</div>
<?php
include '../includes/footer.php';
?>
</body>
</html>

