function Load() {

  const page = document.getElementById("cats-page");

  if (!page) return;

  const gallery = document.getElementById("cats-gallery");
  const status = document.getElementById("cats-status");
  const refreshButton = document.getElementById("cats-refresh");
  const loadMoreButton = document.getElementById("cats-load-more");

  if (!gallery || !status || !refreshButton || !loadMoreButton) return;

  const endpoint = page.dataset.catsEndpoint || "api/cats.php";

  const readLimit = (value, fallback) => {
    const parsedValue = Number.parseInt(value, 10);

    if (Number.isInteger(parsedValue) && parsedValue >= 1 && parsedValue <= 20) {
      return parsedValue;
    }

    return fallback;
  };
 
  const initialLimit = readLimit(page.dataset.initialLimit, 12); 
  const moreLimit = readLimit(page.dataset.moreLimit, 10); 
  let isLoading = false;

  const removeTemporaryStates = () => { 
    gallery
      .querySelectorAll("[data-cats-temporary]")
      .forEach((element) => element.remove());
  };

  const hasCatCards = () => Boolean(gallery.querySelector(".cat-card")); 

  const updateControls = () => { 
    refreshButton.disabled = isLoading;
    loadMoreButton.disabled = isLoading || !hasCatCards();
    loadMoreButton.textContent = isLoading
      ? "Loading..."
      : "Load More Cats";
  }; 

  const createSkeleton = () => { //this function creates a skeleton placeholder for cat cards while loading
    const skeleton = document.createElement("div");
    skeleton.className = "cat-card cat-card-skeleton";
    skeleton.dataset.catsTemporary = "true";
    skeleton.setAttribute("aria-hidden", "true");

    const imageSkeleton = document.createElement("div");
    imageSkeleton.className = "cat-image-skeleton";
    skeleton.append(imageSkeleton);

    return skeleton;
  };

  const showSkeletons = (count, replace) => {
    if (replace) {
      gallery.replaceChildren();
    } else {
      removeTemporaryStates();
    }

    const fragment = document.createDocumentFragment(); // this fragment will hold the skeletons to be added to the gallery

    for (let index = 0; index < count; index += 1) {
      fragment.append(createSkeleton());
    }

    gallery.append(fragment);
  };

  const createCatCard = (cat) => {
    const figure = document.createElement("figure");
    figure.className = "cat-card is-loading";

    const imageFrame = document.createElement("div");
    imageFrame.className = "cat-image-frame";

    const imageLoader = document.createElement("span");
    imageLoader.className = "cat-image-loader";
    imageLoader.setAttribute("aria-hidden", "true");

    const image = document.createElement("img");
    image.className = "cat-image";
    image.src = cat.url;
    image.loading = "lazy";
    image.decoding = "async";

    const breedName = cat.breed?.name?.trim();
    const breedOrigin = cat.breed?.origin?.trim();

    image.alt = breedName
      ? `${breedName} cat${breedOrigin ? ` from ${breedOrigin}` : ""}`
      : "Random cat from The Cat API";

    if (Number.isInteger(cat.width) && cat.width > 0) {
      image.width = cat.width;
    }

    if (Number.isInteger(cat.height) && cat.height > 0) {
      image.height = cat.height;
    }

    const revealImage = () => { // this function reveals the image once it has loaded successfully
      figure.classList.remove("is-loading");
      figure.classList.add("is-loaded");
      imageLoader.remove();
    };

    image.addEventListener("load", revealImage, { once: true });

    image.addEventListener(
      "error",
      () => {
        figure.classList.remove("is-loading");
        figure.classList.add("has-image-error");
        image.remove();
        imageLoader.remove();

        const fallback = document.createElement("span");
        fallback.className = "cat-image-fallback";
        fallback.textContent = "Image unavailable";
        imageFrame.append(fallback);
      },
    );

    imageFrame.append(imageLoader, image); 
    figure.append(imageFrame);

    if (image.complete && image.naturalWidth > 0) {
      revealImage();
    }

    return figure;
  };

  const renderCats = (cats) => {
    const fragment = document.createDocumentFragment();

    cats.forEach((cat) => {
      if (cat && typeof cat.url === "string" && cat.url.startsWith("https://")) {
        fragment.append(createCatCard(cat));
      }
    });

    if (!fragment.childNodes.length) {
      throw new Error("The Cats endpoint returned no usable images.");
    }

    gallery.append(fragment);
  };

  const showError = (replace, limit) => {
    removeTemporaryStates();

    if (replace) {
      gallery.replaceChildren(); 
    }

    const errorState = document.createElement("div");
    errorState.className = "cats-error";
    errorState.dataset.catsTemporary = "true";
    errorState.setAttribute("role", "alert");

    const title = document.createElement("h2");
    title.textContent = "Unable to load cats right now.";

    const message = document.createElement("p");
    message.textContent = "Please try again in a moment.";

    const retryButton = document.createElement("button");
    retryButton.type = "button";
    retryButton.textContent = "Try Again";
    retryButton.addEventListener("click", () => {
      loadCats({ replace, limit });
    });

    errorState.append(title, message, retryButton);
    gallery.append(errorState);
  };

  async function loadCats({ replace = false, limit = moreLimit } = {}) {
    if (isLoading) return;

    isLoading = true;
    gallery.setAttribute("aria-busy", "true");
    status.textContent = "Loading cats.";
    showSkeletons(limit, replace);
    updateControls();

    try {
      const requestUrl = new URL(endpoint, window.location.href);
      requestUrl.searchParams.set("limit", String(limit));

      const response = await fetch(requestUrl, {
        method: "GET",
        headers: {
          Accept: "application/json",
        },
      });

      if (!response.ok) {
        throw new Error(`Cats request failed with status ${response.status}.`);
      }

      const payload = await response.json();

      if (!payload || !Array.isArray(payload.cats)) {
        throw new Error("The Cats endpoint returned an invalid response.");
      }

      removeTemporaryStates();
      renderCats(payload.cats);
      status.textContent = `${payload.cats.length} cats loaded.`;

    } catch (error) {
      console.error("Unable to load cats.", error);
      status.textContent = "Unable to load cats right now.";
      showError(replace, limit);

    } finally {
      isLoading = false;
      gallery.setAttribute("aria-busy", "false");
      updateControls();
    }
  }

  refreshButton.addEventListener("click", () => {
    loadCats({ replace: true, limit: initialLimit });
  });

  loadMoreButton.addEventListener("click", () => {
    loadCats({ replace: false, limit: moreLimit });
  });

  loadCats({ replace: true, limit: initialLimit });
};

Load();
