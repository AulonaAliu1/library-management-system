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
$allBooks = $service->getAllBooks();
$categories = $service->getCategories($allBooks);

$search = trim((string) ($_GET['search'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$sort = trim((string) ($_GET['sort'] ?? 'title'));

$books = $service->searchBooks($allBooks, $search);
$books = $service->filterByCategory($books, $category);
$books = $service->sortBooks($books, $sort);

$role = current_role() ?? ($_SESSION['role'] ?? 'member');
$isAdmin = $role === 'admin';
$isMember = $role === 'member';

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
                Placeholder controls for catalog management are shown here. CRUD logic is intentionally not implemented in this task.
            </p>
            <div class="books-actions">
                <button type="button" class="btn btn-primary">Add New Book</button>
                <button type="button" class="btn btn-secondary">Bulk Update Quantity</button>
            </div>
        </section>
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
                                <a class="btn btn-primary" href="requests.php?book_id=<?= $book->getId() ?>&action=request">
                                    Request Book
                                </a>
                                <p class="text-muted" style="margin-bottom: 0;">
                                    Placeholder entry point only. Approval logic will be handled in the requests flow.
                                </p>
                            <?php else: ?>
                                <p class="text-muted" style="margin: 0;">
                                    This title is currently unavailable for member requests.
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($isAdmin): ?>
                        <div class="role-panel">
                            <div class="books-actions">
                                <button type="button" class="btn btn-primary">Edit</button>
                                <button type="button" class="btn btn-secondary">Update Quantity</button>
                                <button type="button" class="btn btn-danger">Delete</button>
                            </div>
                            <p class="text-muted" style="margin-bottom: 0;">
                                Admin action buttons are placeholders for future catalog management workflows.
                            </p>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
