<section
    class="cats-page"
    id="cats-page"
    data-cats-endpoint="api/cats.php"
    data-initial-limit="12"
    data-more-limit="10"
>

    <header class="cats-header">

        <div>
            <h1>Cats</h1>

            <p>Discover random cats from around the world.</p>
        </div>

        <button
            class="button-secondary cats-refresh-button"
            id="cats-refresh"
            type="button"
        >
            New Cats
        </button>

    </header>

    <p
        class="cats-status visually-hidden"
        id="cats-status"
        role="status"
        aria-live="polite"
    >
        Loading cats.
    </p>

    <div
        class="cats-gallery"
        id="cats-gallery"
        aria-busy="true"
        aria-label="Random cat gallery"
    ></div>

    <div class="cats-actions">
        <button
            id="cats-load-more"
            type="button"
            disabled
        >
            Load More Cats
        </button>
    </div>

</section>

<script src="assets/js/cat.js" defer></script>
