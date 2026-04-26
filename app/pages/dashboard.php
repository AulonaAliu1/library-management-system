<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

$user=null;
if(isset($_SESSION['user'])){
    $user=$_SESSION['user'];
}


require_once __DIR__ . '/../helpers/auth_guard.php';

require_login();

$pageTitle = 'Dashboard';
?>

<?php 




require_once __DIR__ . '/../includes/header.php';

require_once __DIR__ . '/../includes/navbar.php';
?>
<?php 

require_once __DIR__ . '/../services/BookService.php';
require_once __DIR__ . '/../services/RequestService.php';
require_once __DIR__ . '/../services/UserService.php';

$userService= new UserService();
$bookService=new BookService();
$requestService=new RequestService();


?>
<link rel="stylesheet" href="../../assets/css/dashboard.css">


<main class="container main-content">
    <h1>Dashboard</h1>

<?php

if ($user['role'] == 'admin') {

    $borrowings = $requestService->getAllBorrowings();
    $activeBorrowings = 0;

    foreach ($borrowings as $b) {
        if ($b['status'] == 'active') {
            $activeBorrowings++;
        }
    }

    $totalUsers = count($userService->getAllUsers());
    $totalBooks = count($bookService->getAllBooks());
    $totalRequests = count($requestService->getAllRequests());

    echo '<h2>Admin Overview</h2>';

    echo '<div class="dashboard-cards">';

    echo '<div class="card">';
    echo '<h3>Total Users</h3>';
    echo '<p>' . $totalUsers . '</p>';
    echo '</div>';

    echo '<div class="card">';
    echo '<h3>Total Books</h3>';
    echo '<p>' . $totalBooks . '</p>';
    echo '</div>';

    echo '<div class="card">';
    echo '<h3>Total Requests</h3>';
    echo '<p>' . $totalRequests . '</p>';
    echo '</div>';

    echo '<div class="card">';
    echo '<h3>Active Borrowings</h3>';
    echo '<p>' . $activeBorrowings . '</p>';
    echo '</div>';

    echo '</div>';

} else {

$myRequests = $requestService->getRequestsByUserId((int)$user['id']);

    $borrowings = $requestService->getAllBorrowings();
    $myBorrowings = [];

    foreach ($borrowings as $b) {
        if ($b['user_id'] == $user['id'] && $b['status'] == 'active') {
            $myBorrowings[] = $b;
        }
    }

    echo '<h2>Welcome, ' . $user['name'] . '</h2>';

    echo '<div class="dashboard-cards">';

    echo '<div class="card">';
    echo '<h3>Your Requests</h3>';
    echo '<p>' . count($myRequests) . '</p>';
    echo '</div>';

    echo '<div class="card">';
    echo '<h3>Your Borrowed Books</h3>';
    echo '<p>' . count($myBorrowings) . '</p>';
    echo '</div>';

    echo '</div>';
}

?>

</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
