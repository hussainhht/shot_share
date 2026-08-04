<?php

require_once __DIR__ . '/../database/db_connect.php';

$posts = [];
$errorMessage = '';

try {
    $statement = $conn->prepare(
        'SELECT
            p.post_id,
            p.title,
            p.post_text,
            p.image_path,
            p.created_at,
            u.username,
            u.full_name
        FROM posts AS p
        INNER JOIN users AS u
            ON p.user_id = u.user_id
        ORDER BY p.created_at DESC'
    );

    $statement->execute();
    $posts = $statement->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $error) {
    $errorMessage = 'Something went wrong while loading posts.';
    error_log($error->getMessage());
}

function escapeOutput($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function shortenContent($content, int $length = 180): string
{
    $content = strip_tags((string) $content);

    if (mb_strlen($content, 'UTF-8') <= $length) {
        return $content;
    }

    return mb_substr($content, 0, $length, 'UTF-8') . '...';
}

?>

<section class="search-page">

    <header class="search-header">
        <h1 class="search-title">Search Posts</h1>

        <p class="search-description">
            Search by title, post text, username, or full name.
        </p>
    </header>

    <div class="search-form" role="search">
        <label for="search-input" class="visually-hidden">
            Search posts
        </label>

        <div class="search-input-wrapper">
            <input
                class="search-input"
                id="search-input"
                type="search"
                placeholder="Search posts..."
                autocomplete="off"
                aria-controls="search-results"
            >

            <button
                class="search-clear"
                id="search-clear"
                type="button"
                aria-label="Clear search"
                title="Clear search"
                hidden
            >
                &times;
            </button>
        </div>
    </div>

    <?php if ($errorMessage !== ''): ?>

        <div class="error-message" role="alert">
            <?= escapeOutput($errorMessage) ?>
        </div>

    <?php elseif (empty($posts)): ?>

        <section class="empty-state">
            <h2>No posts yet</h2>
            <p>Posts will appear here when users create them.</p>
        </section>

    <?php else: ?>

        <p
            class="search-information"
            id="search-information"
            aria-live="polite"
        >
            <span id="result-prefix">Showing</span>
            <strong id="result-count"><?= count($posts) ?></strong>
            <span id="result-label">
                <?= count($posts) === 1 ? 'post' : 'posts' ?>
            </span>
        </p>

        <section class="search-results" id="search-results">

            <?php foreach ($posts as $post): ?>

                <?php
                $searchData = implode(
                    ' ',
                    [
                        $post['title'],
                        $post['post_text'],
                        $post['username'],
                        $post['full_name']
                    ]
                );
                ?>

                <article
                    class="post-card search-post"
                    data-search="<?= escapeOutput($searchData) ?>"
                >
                    <div class="post-image-wrapper">

                        <?php if (!empty($post['image_path'])): ?>

                            <img
                                class="post-image"
                                src="<?= escapeOutput($post['image_path']) ?>"
                                alt="Image attached to <?= escapeOutput($post['title']) ?>"
                                loading="lazy"
                            >

                        <?php else: ?>

                            <div class="post-image-placeholder">
                                No image
                            </div>

                        <?php endif; ?>

                    </div>

                    <div class="post-content">
                        <h2 class="post-title">
                            <a
                                href="index.php?page=view-post&id=<?= (int) $post['post_id'] ?>"
                            >
                                <?= escapeOutput($post['title']) ?>
                            </a>
                        </h2>

                        <div class="post-meta">
                            <strong>
                                <?= escapeOutput($post['full_name']) ?>
                            </strong>

                            <span>
                                @<?= escapeOutput($post['username']) ?>
                            </span>

                            <?php if (!empty($post['created_at'])): ?>
                                <span aria-hidden="true">&middot;</span>

                                <time datetime="<?= escapeOutput($post['created_at']) ?>">
                                    <?= escapeOutput(
                                        date(
                                            'F j, Y',
                                            strtotime($post['created_at'])
                                        )
                                    ) ?>
                                </time>
                            <?php endif; ?>
                        </div>

                        <p class="post-description">
                            <?= escapeOutput(
                                shortenContent($post['post_text'])
                            ) ?>
                        </p>

                        <a
                            class="view-post-link"
                            href="index.php?page=view-post&id=<?= (int) $post['post_id'] ?>"
                        >
                            View post &rarr;
                        </a>
                    </div>
                </article>

            <?php endforeach; ?>

        </section>

        <section
            class="empty-state search-no-results"
            id="no-search-results"
            aria-live="polite"
            hidden
        >
            <h2>No results found</h2>
            <p>Try another keyword.</p>
        </section>

    <?php endif; ?>

</section>

<script src="assets/js/search.js?v=5"></script>