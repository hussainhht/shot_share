(() => {
  "use strict";

  console.log("search.js loaded");

  const searchInput = document.getElementById("search-input");
  const posts = document.querySelectorAll(".search-post");
  const resultPrefix = document.getElementById("result-prefix");
  const resultCount = document.getElementById("result-count");
  const resultLabel = document.getElementById("result-label");
  const noResults = document.getElementById("no-search-results");
  const clearButton = document.getElementById("search-clear");

  console.log("Posts found:", posts.length);

  if (!searchInput) {
    console.log("Search input not found");
    return;
  }

  const updateResults = () => {
    const query = searchInput.value.trim().toLowerCase();

    let visibleCount = 0;

    posts.forEach((post) => {
      const searchableText =
        (post.dataset.search || "").toLowerCase();

      const matches =
        query === "" || searchableText.includes(query);

      if (matches) {
        post.style.removeProperty("display");
        visibleCount++;
      } else {
        post.style.setProperty("display", "none", "important");
      }
    });

    if (resultPrefix) {
      resultPrefix.textContent =
        query === "" ? "Showing" : "Found";
    }

    if (resultCount) {
      resultCount.textContent = visibleCount;
    }

    if (resultLabel) {
      if (query === "") {
        resultLabel.textContent =
          visibleCount === 1 ? "post" : "posts";
      } else {
        resultLabel.textContent =
          visibleCount === 1 ? "result" : "results";
      }
    }

    if (noResults) {
      if (visibleCount === 0) {
        noResults.removeAttribute("hidden");
        noResults.style.removeProperty("display");
      } else {
        noResults.setAttribute("hidden", "");
        noResults.style.setProperty(
          "display",
          "none",
          "important"
        );
      }
    }

    if (clearButton) {
      clearButton.hidden = query === "";
    }
  };

  searchInput.addEventListener("input", updateResults);

  if (clearButton) {
    clearButton.addEventListener("click", () => {
      searchInput.value = "";
      updateResults();
      searchInput.focus();
    });
  }

  updateResults();
})();