<?php


require_once '../core/Database.php';

$pdo = Database::connection();
$pdo = Database::connection();

if (!$pdo) {
    die(Database::lastError());
}

$extraCss='../../assets/css/contact.css';
include '../includes/header.php';
include '../includes/navbar.php';

$message="";
if($_SERVER['REQUEST_METHOD']=='POST'){
    $name=$_POST['name'];
    $email=$_POST['email'];
    $subject=$_POST['subject'];
    $text=$_POST['message'];

    if(
        empty($name)||empty($email)||empty($subject) ||
        empty($text)
    ){
$message="All Fields are required!";

    }
    else{ 

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

        <p class="message">
            <?= htmlspecialchars($message) ?>
        </p>

        <form method="POST">

            <input
                type="text"
                name="name"
                placeholder="Name"
            >

            <input
                type="email"
                name="email"
                placeholder="Email"
            >

            <input
                type="text"
                name="subject"
                placeholder="Subject"
            >

            <textarea
                name="message"
                placeholder="Message"
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

