<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

$pdo = Database::connection();

session_start();

define('LMS_ENTRY', 'pages');

$user=null;
if(isset($_SESSION['user'])){
    $user=$_SESSION['user'];
}


require_once __DIR__ . '/../helpers/auth_guard.php';

require_login();

$pageTitle = 'Dashboard';
$databaseError = ! ($pdo instanceof PDO)
    ? 'Dashboard is temporarily unavailable until the database connection is restored.'
    : null;
?>

<?php 




require_once __DIR__ . '/../includes/header.php';

require_once __DIR__ . '/../includes/navbar.php';
?>
<?php 

// require_once __DIR__ . '/../services/BookService.php';
// require_once __DIR__ . '/../services/RequestService.php';
// require_once __DIR__ . '/../services/UserService.php';

// $userService= new UserService();
// $bookService=new BookService();
// $requestService=new RequestService();
if ($databaseError === null) {
    $stmt=$pdo->query("SELECT COUNT(*) AS total_books FROM books WHERE status = 'active'");


$result =$stmt->fetch();

$totalBooks = $result['total_books'];



$usersQuery =$pdo->query( "SELECT COUNT(*) AS total_users FROM users") ;


$result =$usersQuery->fetch();


$totalUsers = $result['total_users'];



$requestsQuery = $pdo->query(
    "SELECT COUNT(*) AS total_requests FROM requests"
);

$requests = $requestsQuery->fetch();

$totalRequests = $requests['total_requests'];

$borrowingsQuery = $pdo->query(
    "SELECT COUNT(*) AS active_borrowings
     FROM borrowings
     WHERE status = 'active'"
);

$borrowings = $borrowingsQuery->fetch();

$activeBorrowings = $borrowings['active_borrowings'];
}


?>
<link rel="stylesheet" href="../../assets/css/dashboard.css">



<main class="container main-content">

<h1>Dashboard</h1>

<?php if ($databaseError !== null): ?>
    <p><strong><?= htmlspecialchars($databaseError) ?></strong></p>
<?php elseif ($user['role'] === 'admin') :

?>
 <h2>Admin Overview</h2>


 <div  class="dashboard-cards">
     <div class="card">
                <h3>Total Users</h3>
                <p><?= htmlspecialchars((string)$totalUsers) ?></p>
            </div>

            <div class="card">
                <h3>Total Books</h3>
                <p><?= htmlspecialchars((string)$totalBooks) ?></p>
            </div>

            <div class="card">
                <h3>Total Requests</h3>
                <p><?= htmlspecialchars((string)$totalRequests) ?></p>
            </div>

            <div class="card">
                <h3>Active Borrowings</h3>
                <p><?= htmlspecialchars((string)$activeBorrowings) ?></p>
            </div>



 </div>

    <?php else : ?>
  <h2>
            Welcome,
            <?= htmlspecialchars($user['name']) ?>
        </h2>

         <?php

        $userId = (int)$user['id'];

$myRequestsQuery = $pdo->prepare(
    "SELECT COUNT(*) AS my_requests
     FROM requests
     WHERE user_id = ?"
);

$myRequestsQuery->execute([$userId]);

$myRequests = $myRequestsQuery->fetch();


        $myBorrowingsQuery = $pdo->prepare(
            
            "SELECT COUNT(*) AS my_borrowings
             FROM borrowings
             WHERE user_id = ?
             AND status = 'active'"
        );

        $myBorrowingsQuery->execute([$userId]);
        $myBorrowings = $myBorrowingsQuery->fetch();

        ?>


 <div class="dashboard-cards">

            <div class="card">
                <h3>Your Requests</h3>
                <p><?= htmlspecialchars((string)$myRequests['my_requests']) ?></p>
            </div>

            <div class="card">
                <h3>Your Borrowed Books</h3>
                <p><?= htmlspecialchars((string)$myBorrowings['my_borrowings']) ?></p>
            </div>

        </div>
  <?php endif; ?>

</main>
<!-- 
<main class="container main-content">
    <h1>Dashboard</h1>

<?php

// if ($user['role'] == 'admin') {

//     $borrowings = $requestService->getAllBorrowings();
//     $activeBorrowings = 0;

//     foreach ($borrowings as $b) {
//         if ($b['status'] == 'active') {
//             $activeBorrowings++;
//         }
//     }

//     $totalUsers = count($userService->getAllUsers());
//     $totalBooks = count($bookService->getAllBooks());
//     $totalRequests = count($requestService->getAllRequests());

//     echo '<h2>Admin Overview</h2>';

//     echo '<div class="dashboard-cards">';

//     echo '<div class="card">';
//     echo '<h3>Total Users</h3>';
//     echo '<p>' . $totalUsers . '</p>';
//     echo '</div>';

//     echo '<div class="card">';
//     echo '<h3>Total Books</h3>';
//     echo '<p>' . $totalBooks . '</p>';
//     echo '</div>';

//     echo '<div class="card">';
//     echo '<h3>Total Requests</h3>';
//     echo '<p>' . $totalRequests . '</p>';
//     echo '</div>';

//     echo '<div class="card">';
//     echo '<h3>Active Borrowings</h3>';
//     echo '<p>' . $activeBorrowings . '</p>';
//     echo '</div>';

//     echo '</div>';

// } else {

// $myRequests = $requestService->getRequestsByUserId((int)$user['id']);

//     $borrowings = $requestService->getAllBorrowings();
//     $myBorrowings = [];

//     foreach ($borrowings as $b) {
//         if ($b['user_id'] == $user['id'] && $b['status'] == 'active') {
//             $myBorrowings[] = $b;
//         }
//     }

//     echo '<h2>Welcome, ' . $user['name'] . '</h2>';

//     echo '<div class="dashboard-cards">';

//     echo '<div class="card">';
//     echo '<h3>Your Requests</h3>';
//     echo '<p>' . count($myRequests) . '</p>';
//     echo '</div>';

//     echo '<div class="card">';
//     echo '<h3>Your Borrowed Books</h3>';
//     echo '<p>' . count($myBorrowings) . '</p>';
//     echo '</div>';

//     echo '</div>';
// }

// ?>

</main> -->

<?php
require_once __DIR__ . '/../includes/footer.php';


