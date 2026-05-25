<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../services/BookService.php';

require_login();

$pageTitle = 'Books';
$extraCss = '../../assets/css/books.css'; 

$service = new BookService();

$role = current_role() ?? ($_SESSION['role'] ?? 'member');
$isAdmin = $role === 'admin';
$isMember = $role === 'member';

$errorMessage = $_SESSION['flash_error'] ?? null;
$successMessage = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$action = $_POST['action'] ?? '';
$selectedBookId = (int)($_POST['book_id'] ?? 0);

$showUpdateQuantityForm = $action === 'show_update_quantity';

// POST ACTIONS (DELETE & QUANTITY ONLY)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $token = (string)($_POST['csrf_token'] ?? '');
    if (!csrf_check($token)) {
        die("Unauthorized request. Invalid CSRF token.");
    }

    if ($action === 'update_quantity') {
        $newQuantity = (int)($_POST['total_quantity'] ?? 0);
        if ($newQuantity < 0) {
            $errorMessage = "Quantity cannot be negative.";
            $showUpdateQuantityForm = true;
        } else {
            if ($service->updateBook($selectedBookId, ['total_quantity' => $newQuantity])) {
                $successMessage = "Stock quantity updated successfully!";
            } else {
                $errorMessage = "Failed to update quantity.";
            }
        }
    }

    if ($action === 'delete_book') {
        if ($service->deleteBook($selectedBookId)) {
            $successMessage = "Book deleted successfully from database!";
        } else {
            $errorMessage = "Failed to delete the book.";
        }
    }
}

// FETCH & FILTER DATA
$allBooks = $service->getAllBooks();
$categories = $service->getCategories($allBooks);

$selectedBook = ($selectedBookId > 0) ? $service->getBookById($selectedBookId) : null;

$search = trim((string)($_GET['search'] ?? ''));
$category = trim((string)($_GET['category'] ?? ''));
$sort = trim((string)($_GET['sort'] ?? 'title'));

$books = $service->searchBooks($allBooks, $search);
$books = $service->filterByCategory($books, $category);
$books = $service->sortBooks($books, $sort);

$totalBooks = count($allBooks);
$visibleBooks = count($books);
$availableCopies = array_sum(array_map(fn($b) => $b->getAvailableQuantity(), $allBooks));
$borrowedCopies = array_sum(array_map(fn($b) => $b->getBorrowedQuantity(), $allBooks));

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <section class="books-hero">
        <div><h1>Books</h1></div>
        <span class="badge <?= $isAdmin ? 'badge-warning' : 'badge-neutral' ?>">
            <?= $isAdmin ? 'Admin View' : 'Member View' ?>
        </span>
    </section>

    <section class="books-summary" aria-label="Books summary">
        <article class="card"><span class="books-summary-value"><?= $totalBooks ?></span><span class="text-muted">Total titles</span></article>
        <article class="card"><span class="books-summary-value"><?= $availableCopies ?></span><span class="text-muted">Available copies</span></article>
        <article class="card"><span class="books-summary-value"><?= $borrowedCopies ?></span><span class="text-muted">Borrowed copies</span></article>
        <article class="card"><span class="books-summary-value"><?= $visibleBooks ?></span><span class="text-muted">Visible results</span></article>
    </section>

    <?php if ($errorMessage): ?><div class="flash flash-error"><?= e($errorMessage) ?></div><?php endif; ?>
    <?php if ($successMessage): ?><div class="flash flash-success"><?= e($successMessage) ?></div><?php endif; ?>

    <section class="card books-toolbar">
        <form method="get" class="form-stack">
            <div class="books-filter-grid">
                <div class="form-group">
                    <label for="search">Search books</label>
                    <input id="search" type="text" name="search" value="<?= e($search) ?>" placeholder="Title, author, ISBN...">
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sort">Sort by</label>
                    <select id="sort" name="sort">
                        <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Title (A-Z)</option>
                        <option value="author" <?= $sort === 'author' ? 'selected' : '' ?>>Author (A-Z)</option>
                        <option value="category" <?= $sort === 'category' ? 'selected' : '' ?>>Category (A-Z)</option>
                        <option value="available_quantity_desc" <?= $sort === 'available_quantity_desc' ? 'selected' : '' ?>>Availability</option>
                    </select>
                </div>
            </div>
            <div class="books-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a class="btn btn-secondary" href="books.php">Reset</a>
            </div>
        </form>
    </section>

    <?php if ($isAdmin): ?>
        <section class="card" style="margin-bottom: 1.5rem;">
            <h2 style="margin-bottom: 0.5rem;">Admin Tools</h2>
            <a href="book-create.php" class="btn btn-primary">Add New Book</a>
        </section>

        <?php if ($showUpdateQuantityForm && $selectedBook !== null): ?>
            <section class="card" style="margin-bottom: 1.5rem;">
                <h2>Update Quantity for <?= e($selectedBook->getTitle()) ?></h2>
                <form method="post" class="form-stack">
                    <input type="hidden" name="action" value="update_quantity">
                    <input type="hidden" name="book_id" value="<?= $selectedBook->getId() ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="form-group">
                        <label for="single_quantity">New Total Quantity</label>
                        <input id="single_quantity" type="number" name="total_quantity" min="0" value="<?= $selectedBook->getTotalQuantity() ?>" required>
                    </div>
                    <div class="books-actions">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="books.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($books === []): ?>
        <section class="card books-empty"><h2>No books found</h2></section>
    <?php else: ?>
        <section class="books-grid">
            <?php foreach ($books as $book): ?>
                <article class="card book-card">
                    <div class="book-card-header">
                        <div>
                            <span class="badge badge-neutral"><?= e($book->getCategory() ?: 'General') ?></span>
                            <h2><?= e($book->getTitle()) ?></h2>
                            <p class="book-meta">By <?= e($book->getAuthor()) ?></p>
                        </div>
                        <span class="badge <?= $book->getAvailableQuantity() > 0 ? 'badge-success' : 'badge-warning' ?>">
                            <?= $book->getAvailableQuantity() > 0 ? 'Available' : 'Out of Stock' ?>
                        </span>
                    </div>
                    <div class="book-cover-container">
                        <?php if ($book->getImagePath()): ?>
                            <img src="../../<?= e($book->getImagePath()) ?>" alt="<?= e($book->getTitle()) ?>">
                        <?php else: ?>
                            <div class="no-cover-placeholder">
                                No Cover Available
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="book-description"><?= e($book->getDescription()) ?></p>
                    <p class="book-meta">ISBN: <?= e($book->getIsbn() ?: 'N/A') ?></p>
                    <div class="book-quantities">
                        <div><span>Total</span><strong><?= $book->getTotalQuantity() ?></strong></div>
                        <div><span>Available</span><strong><?= $book->getAvailableQuantity() ?></strong></div>
                        <div><span>Borrowed</span><strong><?= $book->getBorrowedQuantity() ?></strong></div>
                    </div>
                    <div class="role-panel">
                        <form method="post" class="books-actions">
                            <input type="hidden" name="book_id" value="<?= $book->getId() ?>">
                            <?php if ($isAdmin): ?>
                                <a href="book-edit.php?id=<?= $book->getId() ?>" class="btn btn-primary">Edit</a>
                                <button type="submit" name="action" value="show_update_quantity" class="btn btn-secondary">Quantity</button>
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" name="action" value="delete_book" class="btn btn-danger" onclick="return confirm('Delete this book?');">Delete</button>
                            <?php elseif ($isMember && $book->getAvailableQuantity() > 0): ?>
                                <button type="submit" name="action" value="request_book" class="btn btn-primary">Request</button>
                                <button type="submit" name="action" value="borrow_book" class="btn btn-secondary">Borrow</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../../assets/js/books.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>