$(document).ready(function () {
  $("#btn_api_lookup").on("click", function () {
    const isbn = $("#isbn_lookup").val().trim();
    const $statusText = $("#api_status");

    if (!isbn) {
      $statusText.text("Please enter an ISBN code.");
      $statusText.css("color", "red");
      return;
    }
    $statusText.text("Searching Open Library API via jQuery AJAX...");
    $statusText.css("color", "#555");

    $.ajax({
      url: `https://openlibrary.org/api/books?bibkeys=ISBN:${isbn}&format=json&jscmd=data`,
      method: "GET",
      dataType: "json",
      success: function (data) {

        const bookKey = `ISBN:${isbn}`;

        if (data && data[bookKey]) {
          const bookData = data[bookKey];

          $("#title").val(bookData.title || "");
          $("#isbn").val(isbn);
          $("#description").val(bookData.notes || "No description available.");

          if (bookData.authors && bookData.authors.length > 0) {
            $("#author").val(bookData.authors[0].name);
          }

          if (bookData.subjects && bookData.subjects.length > 0) {
            $("#category_input").val(bookData.subjects[0].name);
          }

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
      error: function (xhr, status, error) {
        console.error("jQuery AJAX Error:", error);
        $statusText.text("An error occurred while communicating with the API.");
        $statusText.css("color", "red");
      },
    });
  });

  $(".btn-delete-book").on("click", function (e) {
    e.preventDefault();

    if (!confirm("Are you absolutely sure you want to delete this book?")) {
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
          "Failed to delete book: " +
            (response ? response.message : "Unknown error"),
        );
      },
    });
  });
});
