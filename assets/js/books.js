document.addEventListener("DOMContentLoaded", () => {
  const isbnInput = document.getElementById("isbn");
  if (!isbnInput) return;

  isbnInput.addEventListener("change", async () => {
    const isbn = isbnInput.value.trim();
    if (!isbn) return;

    try {
      const response = await fetch(`/public/api/book-lookup.php?isbn=${isbn}`);

      const result = await response.json();

      if (result.success) {
        document.getElementById("title").value = result.data.title || "";

        document.getElementById("publish_date").value =
          result.data.publish_date || "";

        document.getElementById("author").value = result.data.author || "";

        const descriptionInput = document.getElementById("description");
        if (descriptionInput) {
          descriptionInput.value = result.data.description || "";
        }

        const coverInput = document.getElementById("book_cover");
        if (coverInput) {
          coverInput.value = result.data.cover || "";
        }
      } else {
        alert(result.message);
      }
    } catch (error) {
      alert("API reques failed. ");
    }
  });
});
