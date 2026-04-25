<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';

require_admin();

$pageTitle = 'Members';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__.'/../services/UserService.php';

$userService=new UserService();

$users=$userService->getAllUsers();

?>
<link rel="stylesheet" href="../../assets/css/members.css">
<main class="container main-content">
    <h1>Members</h1>

    <?php
    echo '<div class="dashboard-cards">';

    foreach ($users as $u) {

        echo '<div class="card">';
        echo '<h3>' . $u['username'] . '</h3>';
        echo '<p>Role: ' . $u['role'] . '</p>';
        echo '</div>';
    }

    echo '</div>';
    ?>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
