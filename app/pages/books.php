<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';

require_login();

$pageTitle = 'Books';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../services/BookService.php';

$service = new BookService();

$role = current_role() ?? ($_SESSION['role'] ?? 'member');
$isAdmin = $role === 'admin';
$isMember = $role === 'member';

$action = $_POST['action'] ?? '';
$selectedBookId = (int) ($_POST['book_id'] ?? 0);

$showAddForm = $action === 'show_add';
$showBulkForm = $action === 'show_bulk';
$showEditForm = $action === 'show_edit';
$showUpdateQuantityForm = $action === 'show_update_quantity';

$dataFile = __DIR__ . '/../data/books-data.php';
$requestsFile = __DIR__ . '/../data/requests-data.php';
$borrowingsFile = __DIR__ . '/../data/borrowings-data.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $booksData = require $dataFile;

    if ($action === 'add_book') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $author = trim((string) ($_POST['author'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $isbn = trim((string) ($_POST['isbn'] ?? ''));
        $quantity = (int) ($_POST['quantity'] ?? 0);

        if ($title !== '' && $author !== '' && $quantity >= 1) {
            $ids = array_column($booksData, 'id');
            $newId = empty($ids) ? 1 : max($ids) + 1;

            $booksData[] = [
                'id' => $newId,
                'title' => $title,
                'author' => $author,
                'category' => $category,
                'description' => $description,
                'isbn' => $isbn,
                'total_quantity' => $quantity,
                'available_quantity' => $quantity,
                'borrowed_quantity' => 0,
            ];

            $export = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($booksData, true) . ";\n";
            file_put_contents($dataFile, $export);
        } else {
            $showAddForm = true;
        }
    }

    if ($action === 'edit_book') {
        foreach ($booksData as &$bookData) {
            if ((int) $bookData['id'] === $selectedBookId) {
                $bookData['title'] = trim((string) ($_POST['title'] ?? $bookData['title']));
                $bookData['author'] = trim((string) ($_POST['author'] ?? $bookData['author']));
                $bookData['category'] = trim((string) ($_POST['category'] ?? $bookData['category']));
                $bookData['description'] = trim((string) ($_POST['description'] ?? $bookData['description']));
                $bookData['isbn'] = trim((string) ($_POST['isbn'] ?? $bookData['isbn']));
                break;
            }
        }
        unset($bookData);

        $export = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($booksData, true) . ";\n";
        file_put_contents($dataFile, $export);
    }

    if ($action === 'update_quantity') {
        $newQuantity = (int) ($_POST['quantity'] ?? 0);

        foreach ($booksData as &$bookData) {
            if ((int) $bookData['id'] === $selectedBookId) {
                $borrowed = (int) ($bookData['borrowed_quantity'] ?? 0);
                $bookData['total_quantity'] = $newQuantity;
                $bookData['available_quantity'] = max(0, $newQuantity - $borrowed);
                break;
            }
        }
        unset($bookData);

        $export = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($booksData, true) . ";\n";
        file_put_contents($dataFile, $export);
    }

    if ($action === 'bulk_update') {
        $newQuantity = (int) ($_POST['quantity'] ?? 0);

        foreach ($booksData as &$bookData) {
            $borrowed = (int) ($bookData['borrowed_quantity'] ?? 0);
            $bookData['total_quantity'] = $newQuantity;
            $bookData['available_quantity'] = max(0, $newQuantity - $borrowed);
        }
        unset($bookData);

        $export = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($booksData, true) . ";\n";
        file_put_contents($dataFile, $export);
    }

    if ($action === 'delete_book') {
        $booksData = array_values(array_filter($booksData, function (array $bookData) use ($selectedBookId): bool {
            return (int) $bookData['id'] !== $selectedBookId;
        }));

        $export = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($booksData, true) . ";\n";
        file_put_contents($dataFile, $export);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isMember) {
    $booksData = require $dataFile;
    $bookId = (int) ($_POST['book_id'] ?? 0);
    $userId = (int) ($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

    if ($action === 'request_book') {
        $requestsData = file_exists($requestsFile) ? require $requestsFile : [];

        $ids = array_column($requestsData, 'id');
        $newId = empty($ids) ? 1 : max($ids) + 1;

        $requestsData[] = [
            'id' => $newId,
            'user_id' => $userId,
            'book_id' => $bookId,
            'status' => 'pending',
            'request_date' => date('Y-m-d'),
        ];

        $export = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($requestsData, true) . ";\n";
        file_put_contents($requestsFile, $export);
    }

    if ($action === 'borrow_book') {
        $canBorrow = false;

        foreach ($booksData as &$bookData) {
            if ((int) $bookData['id'] === $bookId && (int) $bookData['available_quantity'] > 0) {
                $bookData['available_quantity'] = (int) $bookData['available_quantity'] - 1;
                $bookData['borrowed_quantity'] = (int) $bookData['borrowed_quantity'] + 1;
                $canBorrow = true;
                break;
            }
        }
        unset($bookData);

        if ($canBorrow) {
            $export = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($booksData, true) . ";\n";
            file_put_contents($dataFile, $export);

            $borrowingsData = file_exists($borrowingsFile) ? require $borrowingsFile : [];

            $ids = array_column($borrowingsData, 'id');
            $newId = empty($ids) ? 1 : max($ids) + 1;

            $borrowingsData[] = [
                'id' => $newId,
                'user_id' => $userId,
                'book_id' => $bookId,
                'borrow_date' => date('Y-m-d'),
                'return_date' => date('Y-m-d', strtotime('+14 days')),
                'status' => 'active',
            ];

            $export = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($borrowingsData, true) . ";\n";
            file_put_contents($borrowingsFile, $export);
        }
    }
}

$allBooks = $service->getAllBooks();
$categories = $service->getCategories($allBooks);

$selectedBook = null;

foreach ($allBooks as $book) {
    if ($book->getId() === $selectedBookId) {
        $selectedBook = $book;
        break;
    }
}

$search = trim((string) ($_GET['search'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$sort = trim((string) ($_GET['sort'] ?? 'title'));

$books = $service->searchBooks($allBooks, $search);
$books = $service->filterByCategory($books, $category);
$books = $service->sortBooks($books, $sort);

$totalBooks = count($allBooks);
$visibleBooks = count($books);
$availableCopies = array_sum(array_map(fn (Book $book): int => $book->getAvailableQuantity(), $allBooks));
$borrowedCopies = array_sum(array_map(fn (Book $book): int => $book->getBorrowedQuantity(), $allBooks));
?>

<main class="container main-content">
    <style>
        .books-hero {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .books-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.85rem;
            margin: 1.25rem 0 1.5rem;
        }

        .books-summary .card {
            padding: 1rem;
        }

        .books-summary-value {
            display: block;
            font-size: 1.45rem;
            font-weight: 700;
            color: #0f172a;
        }

        .books-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .books-toolbar {
            margin-bottom: 1.5rem;
        }

        .books-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            margin-top: 1rem;
        }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
        }

        .book-card {
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }

        .book-card-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
        }

        .book-card h2 {
            font-size: 1.2rem;
            margin-bottom: 0.35rem;
        }

        .book-meta {
            margin: 0;
            color: #475569;
            font-size: 0.95rem;
        }

        .book-description {
            margin: 0;
            color: #334155;
        }

        .book-quantities {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
            padding: 0.85rem;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .book-quantities span {
            display: block;
            font-size: 0.8rem;
            color: #64748b;
        }

        .book-quantities strong {
            display: block;
            font-size: 1.1rem;
            color: #0f172a;
        }

        .books-empty {
            text-align: center;
            padding: 2rem 1rem;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .btn-secondary:hover {
            text-decoration: none;
            background: #cbd5e1;
            color: #0f172a;
        }

        .btn-danger {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .btn-danger:hover {
            text-decoration: none;
            background: #fecaca;
            color: #7f1d1d;
        }

        .role-panel {
            border-top: 1px solid #e2e8f0;
            padding-top: 0.85rem;
        }
    </style>

    <section class="books-hero">
        <div>
            <h1>Books</h1>
            <p class="lead">
                Browse the library catalog, explore availability, and use the role-specific actions below.
            </p>
        </div>
        <span class="badge <?= $isAdmin ? 'badge-warning' : 'badge-neutral' ?>">
            <?= $isAdmin ? 'Admin View' : 'Member View' ?>
        </span>
    </section>

    <section class="books-summary" aria-label="Books summary">
        <article class="card">
            <span class="books-summary-value"><?= $totalBooks ?></span>
            <span class="text-muted">Total titles</span>
        </article>
        <article class="card">
            <span class="books-summary-value"><?= $availableCopies ?></span>
            <span class="text-muted">Available copies</span>
        </article>
        <article class="card">
            <span class="books-summary-value"><?= $borrowedCopies ?></span>
            <span class="text-muted">Borrowed copies</span>
        </article>
        <article class="card">
            <span class="books-summary-value"><?= $visibleBooks ?></span>
            <span class="text-muted">Visible results</span>
        </article>
    </section>

    <section class="card books-toolbar">
        <form method="get" class="form-stack">
            <div class="books-filter-grid">
                <div class="form-group">
                    <label for="search">Search books</label>
                    <input
                        id="search"
                        type="text"
                        name="search"
                        placeholder="Title, author, category, ISBN..."
                        value="<?= h($search) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All categories</option>
                        <?php foreach ($categories as $categoryOption): ?>
                            <option value="<?= h($categoryOption) ?>" <?= $category === $categoryOption ? 'selected' : '' ?>>
                                <?= h($categoryOption) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sort">Sort by</label>
                    <select id="sort" name="sort">
                        <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Title (A-Z)</option>
                        <option value="author" <?= $sort === 'author' ? 'selected' : '' ?>>Author (A-Z)</option>
                        <option value="category" <?= $sort === 'category' ? 'selected' : '' ?>>Category (A-Z)</option>
                        <option value="available_quantity_desc" <?= $sort === 'available_quantity_desc' ? 'selected' : '' ?>>
                            Availability (High to Low)
                        </option>
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
            <p class="text-muted" style="margin-top: 0;">
                Admin can add, edit, update quantity, and delete books.
            </p>

            <form method="post" class="books-actions">
                <button type="submit" name="action" value="show_add" class="btn btn-primary">
                    Add New Book
                </button>

                <button type="submit" name="action" value="show_bulk" class="btn btn-secondary">
                    Bulk Update Quantity
                </button>
            </form>
        </section>

        <?php if ($showAddForm): ?>
            <section class="card" style="margin-bottom: 1.5rem;">
                <h2 style="margin-bottom: 0.5rem;">Add New Book</h2>

                <form method="post" class="form-stack">
                    <input type="hidden" name="action" value="add_book">

                    <div class="form-group">
                        <label for="title">Title</label>
                        <input id="title" type="text" name="title" placeholder="Book title" required>
                    </div>

                    <div class="form-group">
                        <label for="author">Author</label>
                        <input id="author" type="text" name="author" placeholder="Book author" required>
                    </div>

                    <div class="form-group">
                        <label for="category_new">Category</label>
                        <input id="category_new" type="text" name="category" placeholder="Book category">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Book description"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="isbn">ISBN</label>
                        <input id="isbn" type="text" name="isbn" placeholder="ISBN">
                    </div>

                    <div class="form-group">
                        <label for="quantity">Total Quantity</label>
                        <input id="quantity" type="number" name="quantity" min="1" placeholder="Quantity" required>
                    </div>

                    <div class="books-actions">
                        <button type="submit" class="btn btn-primary">Save Book</button>
                        <a href="books.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <?php if ($showBulkForm): ?>
            <section class="card" style="margin-bottom: 1.5rem;">
                <h2 style="margin-bottom: 0.5rem;">Bulk Update Quantity</h2>

                <form method="post" class="form-stack">
                    <input type="hidden" name="action" value="bulk_update">

                    <div class="form-group">
                        <label for="bulk_quantity">New Quantity</label>
                        <input id="bulk_quantity" type="number" name="quantity" min="0" placeholder="New quantity" required>
                    </div>

                    <div class="books-actions">
                        <button type="submit" class="btn btn-secondary">Update Quantities</button>
                        <a href="books.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <?php if ($showEditForm && $selectedBook !== null): ?>
            <section class="card" style="margin-bottom: 1.5rem;">
                <h2 style="margin-bottom: 0.5rem;">Edit Book</h2>

                <form method="post" class="form-stack">
                    <input type="hidden" name="action" value="edit_book">
                    <input type="hidden" name="book_id" value="<?= $selectedBook->getId() ?>">

                    <div class="form-group">
                        <label for="edit_title">Title</label>
                        <input id="edit_title" type="text" name="title" value="<?= h($selectedBook->getTitle()) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_author">Author</label>
                        <input id="edit_author" type="text" name="author" value="<?= h($selectedBook->getAuthor()) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_category">Category</label>
                        <input id="edit_category" type="text" name="category" value="<?= h($selectedBook->getCategory()) ?>">
                    </div>

                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea id="edit_description" name="description"><?= h($selectedBook->getDescription()) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit_isbn">ISBN</label>
                        <input id="edit_isbn" type="text" name="isbn" value="<?= h($selectedBook->getIsbn()) ?>">
                    </div>

                    <div class="books-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="books.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <?php if ($showUpdateQuantityForm && $selectedBook !== null): ?>
            <section class="card" style="margin-bottom: 1.5rem;">
                <h2 style="margin-bottom: 0.5rem;">Update Quantity</h2>

                <form method="post" class="form-stack">
                    <input type="hidden" name="action" value="update_quantity">
                    <input type="hidden" name="book_id" value="<?= $selectedBook->getId() ?>">

                    <div class="form-group">
                        <label for="single_quantity">New Quantity</label>
                        <input
                            id="single_quantity"
                            type="number"
                            name="quantity"
                            min="0"
                            value="<?= $selectedBook->getTotalQuantity() ?>"
                            required
                        >
                    </div>

                    <div class="books-actions">
                        <button type="submit" class="btn btn-secondary">Update Quantity</button>
                        <a href="books.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($books === []): ?>
        <section class="card books-empty">
            <h2>No books found</h2>
            <p class="text-muted">
                Try changing the search term, category, or sort option to see more results.
            </p>
        </section>
    <?php else: ?>
        <section class="books-grid" aria-label="Book listings">
            <?php foreach ($books as $book): ?>
                <article class="card book-card">
                    <div class="book-card-header">
                        <div>
                            <span class="badge badge-neutral"><?= h($book->getCategory()) ?></span>
                            <h2><?= h($book->getTitle()) ?></h2>
                            <p class="book-meta">By <?= h($book->getAuthor()) ?></p>
                        </div>

                        <?php if ($book->isAvailable()): ?>
                            <span class="badge badge-success">Available</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Out of Stock</span>
                        <?php endif; ?>
                    </div>

                    <p class="book-description"><?= h($book->getDescription()) ?></p>
                    <p class="book-meta">ISBN: <?= h($book->getIsbn()) ?></p>

                    <div class="book-quantities">
                        <div>
                            <span>Total</span>
                            <strong><?= $book->getTotalQuantity() ?></strong>
                        </div>
                        <div>
                            <span>Available</span>
                            <strong><?= $book->getAvailableQuantity() ?></strong>
                        </div>
                        <div>
                            <span>Borrowed</span>
                            <strong><?= $book->getBorrowedQuantity() ?></strong>
                        </div>
                    </div>

                    <?php if ($isMember): ?>
                        <div class="role-panel">
                            <?php if ($book->isAvailable()): ?>
                                <form method="post" class="books-actions">
                                    <input type="hidden" name="book_id" value="<?= $book->getId() ?>">

                                    <button type="submit" name="action" value="request_book" class="btn btn-primary">
                                        Request Book
                                    </button>

                                    <button type="submit" name="action" value="borrow_book" class="btn btn-secondary">
                                        Borrow Book
                                    </button>
                                </form>
                            <?php else: ?>
                                <p class="text-muted" style="margin: 0;">
                                    This title is currently unavailable.
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($isAdmin): ?>
                        <div class="role-panel">
                            <form method="post" class="books-actions">
                                <input type="hidden" name="book_id" value="<?= $book->getId() ?>">

                                <button type="submit" name="action" value="show_edit" class="btn btn-primary">
                                    Edit
                                </button>

                                <button type="submit" name="action" value="show_update_quantity" class="btn btn-secondary">
                                    Update Quantity
                                </button>

                                <button type="submit" name="action" value="delete_book" class="btn btn-danger">
                                    Delete
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';