<?php
declare(strict_types=1);

session_start();

define('LMS_ENTRY', 'pages');

require_once __DIR__ . '/../helpers/auth_guard.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../services/BookService.php';
require_once __DIR__ . '/../services/FileUploadService.php';

require_admin();

$pageTitle = 'Edit Book';
$extraCss = './../../assets/css/books.css';

$uploadService = new FileUploadService();

$errorMessage = null;
$successMessage = null;

$bookId = (int)($_GET['id'] ?? 0);

try {
    $bookService = new BookService();
    $book = $bookService->getBookById($bookId);
} catch (Throwable $exception) {
    error_log('Book edit database error: ' . $exception->getMessage());
    $bookService = null;
    $book = null;
    $errorMessage = 'Books module is temporarily unavailable. Please try again later.';
}

if ($book === null) {
    $_SESSION['flash_error'] = $errorMessage ?? "The requested book was not found.";
    header("Location: books.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $bookService !== null) {

    $token = (string)($_POST['csrf_token'] ?? '');
    if (!csrf_check($token)) {
        $errorMessage = 'Security check failed. Please try again.';
    }

    $title = trim((string)($_POST['title'] ?? ''));
    $author = trim((string)($_POST['author'] ?? ''));
    $category = trim((string)($_POST['category'] ?? ''));
    $isbn = trim((string)($_POST['isbn'] ?? ''));
    $total_quantity = (int)($_POST['total_quantity'] ?? 0);
    $description = trim((string)($_POST['description'] ?? ''));

    if ($errorMessage === null && (!is_filled($title) || !is_filled($author))) {
        $errorMessage = "Title and Author fields are required.";
    } elseif ($errorMessage === null) {
        try {
            $imagePath = $book->getImagePath(); 

            if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $uploadService->uploadImage($_FILES['book_image']);
            }
            
                $updateData = [
                    'title' => $title,
                    'author' => $author,
                    'category' => $category,
                    'isbn' => $isbn,
                    'total_quantity' => $total_quantity,
                    'description' => $description,
                    'image_path' => $imagePath
                ];

                if($imagePath !== null){
                    $updateData['image_path'] = $imagePath;
                }

                $updated = $bookService->updateBook($bookId, $updateData);

                if ($updated) {
                    $_SESSION['flash_success'] = "Book '{$title}' updated successfully!";
                    header("Location: books.php");
                    exit;
                }
            }
            catch (Exception $e) {
            error_log('Book edit error: ' . $e->getMessage());
            $errorMessage = 'Unable to update the book right now.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <section class="card">
        <h1>Edit Book Details</h1>
        <p class="text-muted">Modify the fields below to update the book inventory information.</p>

        <?php if ($errorMessage): ?>
            <div class="flash flash-error"><?= e($errorMessage) ?></div>
        <?php endif; ?>

        <form action="book-edit.php?id=<?= $bookId ?>" method="POST" enctype="multipart/form-data" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="form-group">
                <label for="title">Book Title *</label>
                <input type="text" id="title" name="title" value="<?= e($book->getTitle()) ?>" required>
            </div>

            <div class="form-group">
                <label for="author">Author *</label>
                <input type="text" id="author" name="author" value="<?= e($book->getAuthor()) ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" value="<?= e($book->getCategory()) ?>">
            </div>

            <div class="form-group">
                <label for="isbn">ISBN Code</label>
                <input type="text" id="isbn" name="isbn" value="<?= e($book->getIsbn()) ?>">
            </div>

            <div class="form-group">
                <label for="total_quantity">Quantity (Total Copies)</label>
                <input type="number" id="total_quantity" name="total_quantity" min="0" value="<?= $book->getTotalQuantity() ?>">
            </div>

            <div class="form-group">
                <label for="description">Description / Notes</label>
                <textarea id="description" name="description" rows="5"><?= e($book->getDescription()) ?></textarea>
            </div>

            <div class="form-group">
                <label>Current Cover Image</label>
                <div style="margin-top: 5px; margin-bottom: 10px;">
                    <?php $currentImage = $book->getImagePath();
                          if($currentImage): ?>
                        <img src="../../<?= e($currentImage) ?>" alt="Current Cover" style="max-height: 120px; border: 1px solid #ddd; padding: 5px;">
                    <?php else: ?>
                        <span class="text-muted">No custom image uploaded. Using system default cover.</span>
                    <?php endif; ?>
                </div>
                <label for="book_image">Upload New Cover (Leave empty to keep current image)</label>
                <input type="file" id="book_image" name="book_image" accept="image/*">
            </div>

            <div class="form-actions" style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Update Book</button>
                <a href="books.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
