$(document).ready(function () {
  $("#btn_api_lookup").on("click", function () {
    const isbn = $("#isbn_lookup").val().trim();
    const $statusText = $("#api_status");

    if (!isbn) {
      $statusText.text("Please enter an ISBN code.");
      $statusText.css("color", "red");
      return;
    }
    $statusText.text("Searching Open Library API...");
    $statusText.css("color", "#555");

    $.ajax({
      url: "../../public/api/book-lookup.php",
      method: "GET",
      data: { isbn },
      dataType: "json",
      success: function (response) {
        if (response.success && response.data) {
          const bookData = response.data;
          $("#title").val(bookData.title || "");
          $("#isbn").val(bookData.isbn || isbn);
          $("#description").val(bookData.description || "No description available.");
          $("#author").val(bookData.author || "");
          $("#category_input").val(bookData.category || "");

          $statusText.html(
            "<span>Book found successfully! Fields populated.</span>",
          );
          $statusText.css("color", "green");
        } else {
          $statusText.html(
            "<span>Book not found in API. Please fill fields manually.</span>",
          );
          $statusText.css("color", "red");
        }
      },
      error: function (xhr) {
        const response = xhr.responseJSON;
        $statusText.text(response ? response.message : "An error occurred while communicating with the API.");
        $statusText.css("color", "red");
      },
    });
  });

  $(".btn-delete-book").on("click", function (e) {
    e.preventDefault();

    if (!confirm("Archive this book?")) {
      return;
    }

    const $button = $(this);
    const bookId = $button.data("id");
    const csrfToken = $button.data("csrf");
    const $bookCard = $button.closest(".book-item");

    $.ajax({
      url: "../../public/api/delete-book.php",
      method: "POST",
      dataType: "json",
      data: {
        book_id: bookId,
        csrf_token: csrfToken,
      },
      success: function (response) {
        if (response.success) {
          $bookCard.fadeOut(400, function () {
            $(this).remove();
          });
        } else {
          alert("Error: " + response.message);
        }
      },
      error: function (xhr) {
        const response = xhr.responseJSON;
        alert(
          "Failed to archive book: " +
            (response ? response.message : "Unknown error"),
        );
      },
    });
  });
});
