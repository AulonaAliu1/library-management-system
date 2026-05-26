<?php

declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/auth_guard.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../services/MailService.php';

require_member();

$pdo = Database::connection();

if (!$pdo) {
    error_log('Contact database error: ' . (Database::lastError() ?? 'Unknown database error'));
}

$pageTitle = 'Contact Us';
$extraCss = '../../assets/css/contact.css';
$message = '';
$isSuccess = false;

if (isset($_GET['success'])) {
    $message = 'Message sent successfully!';
    $isSuccess = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $text = trim((string) ($_POST['message'] ?? ''));

    if (! csrf_check((string) ($_POST['csrf_token'] ?? ''))) {
        $message = 'Security check failed. Please try again.';

    }
    elseif (! ($pdo instanceof PDO)) {
        $message = 'Contact form is temporarily unavailable. Please try again later.';

    }
    elseif(
        ! is_filled($name) ||
        ! is_filled($email) ||
        ! is_filled($subject) ||
        ! is_filled($text)
    ){
        $message = 'All fields are required!';

    }
    elseif (! is_valid_email($email)) {
        $message = 'Invalid email format!';

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



    $mailService = new MailService();

        $emailBody = "New Contact Message\n\n"
            . "Name: {$name}\n"
            . "Email: {$email}\n"
            . "Subject: {$subject}\n"
            . "Message:\n{$text}";

        $mailSent = $mailService->send(
            'lmskosove@gmail.com',
            'New Contact Message',
            $emailBody
        );
        if ($mailSent) {
            redirect('contact.php?success=1');

        } else {
            error_log('Contact email failed: ' . ($mailService->getLastError() ?? 'Unknown mail error'));
            $message = 'Message saved, but email failed to send.';

        }

    }catch(Throwable $e) {

        $message = 'Contact form is temporarily unavailable. Please try again later.';
        }

}

}



?>
<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="main-content">

    <h1>Contact Us</h1>

    <div class="contact-form">
        <?php if(!empty($message)): ?>


        <p class="<?= $isSuccess?'success-message':'error-message' ?>">
            <?= htmlspecialchars($message) ?>
        </p>

        <?php  endif;?>


        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

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
require_once __DIR__ . '/../includes/footer.php';
?>

