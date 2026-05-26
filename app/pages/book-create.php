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

$pageTitle = 'Add new Book';
$extraCss = './../../assets/css/books.css';

$uploadService = new FileUploadService();

$errorMessage = null;
$successMessage = null;

try {
    $bookService = new BookService();
} catch (Throwable $exception) {
    error_log('Book create database error: ' . $exception->getMessage());
    $bookService = null;
    $errorMessage = 'Books module is temporarily unavailable. Please try again later.';
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && $bookService !== null){
    $token = (string)($_POST['csrf_token'] ?? '');
    if(!csrf_check($token)){
        $errorMessage = 'Security check failed. Please try again.';
    }
    $title = trim((string)($_POST['title'] ?? ''));
    $author = trim((string)($_POST['author'] ?? ''));
    $category = trim((string)($_POST['category'] ?? ''));
    $isbn = trim((string)($_POST['isbn'] ?? ''));
    $total_quantity = (int)($_POST['total_quantity'] ?? 0);
    $description = trim((string)($_POST['description'] ?? ''));

    if($errorMessage === null && (!is_filled($title) || !is_filled($author))){
        $errorMessage = "Title and Author fields are required.";
    } elseif ($errorMessage === null) {
        try{
            $imagePath = null;

            if(isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK){
                $imagePath = $uploadService->uploadImage($_FILES['book_image']);
            }

            $bookData = [
                'title' => $title,
                'author' => $author,
                'category' => $category,
                'isbn' => $isbn,
                'total_quantity' => $total_quantity,
                'description' => $description,
                'image_path' => $imagePath
            ];

            if($bookService->createBook($bookData)){
                $_SESSION['flash_success'] = "Book '{$title}' was added successfully!";
                header("Location: books.php");
                exit;
            }else{
                $errorMessage = "Failed to save the book. The ISBN code might already exist.";
            }
        }catch(Exception $e){
            error_log('Book create error: ' . $e->getMessage());
            $errorMessage = 'Unable to save the book right now.';
        }
    }
}
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container main-content">
    <section class="card">
        <h1>Add New Book</h1>
        <p class="text-muted">Fill out the form details below or use the automatic lookup via ISBN code.</p>

        <?php if ($errorMessage): ?>
            <div class="flash flash-error"><?= e($errorMessage) ?></div>
        <?php endif; ?>

        <div class="form-group" style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
            <div style="flex: 1;">
                <label for="isbn_lookup"><strong>Autofill via ISBN (External API):</strong></label>
                <input type="text" id="isbn_lookup" placeholder="Enter ISBN (e.g., 9780132350884)">
            </div>
            <button type="button" id="btn_api_lookup" class="btn btn-secondary" style="height: 42px;">Lookup API</button>
        </div>
        <small id="api_status" style="display: block; margin-top: -20px; margin-bottom: 20px; font-weight: bold;"></small>

        <form action="book-create.php" method="POST" enctype="multipart/form-data" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="form-group">
                <label for="title">Book Title *</label>
                <input type="text" id="title" name="title" required placeholder="e.g., Clean Code">
            </div>

            <div class="form-group">
                <label for="author">Author *</label>
                <input type="text" id="author" name="author" required placeholder="e.g., Robert C. Martin">
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category_input" name="category" placeholder="e.g., Software Engineering">
            </div>

            <div class="form-group">
                <label for="isbn">ISBN Code</label>
                <input type="text" id="isbn" name="isbn" placeholder="e.g., 9780132350884">
            </div>

            <div class="form-group">
                <label for="total_quantity">Quantity (Total Copies)</label>
                <input type="number" id="total_quantity" name="total_quantity" min="1" value="5">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" placeholder="Short description of the book..."></textarea>
            </div>

            <div class="form-group">
                <label for="book_image">Book Cover (Image: JPG, PNG, WEBP - Max 2MB)</label>
                <input type="file" id="book_image" name="book_image" accept="image/*">
            </div>

            <div class="form-actions" style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Save Book</button>
                <a href="books.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../../assets/js/books.js"></script>
