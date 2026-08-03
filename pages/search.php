<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// require_once __DIR__ . "/../database/db_connect.php";
$searchQuery = "";
$posts = [];
$errorMessage = "";

if (isset($_GET["q"])) {
    $searchQuery = trim($_GET["q"]);
}

if ($searchQuery !== "") {
    $searchPattern = "%" . $searchQuery . "%";

    $sql = "
        SELECT
            posts.id,
            posts.title,
            posts.content,
            posts.image,
            posts.created_at,
            users.username
        FROM posts
        INNER JOIN users
            ON posts.user_id = users.id
        WHERE posts.title LIKE ?
           OR posts.content LIKE ?
           OR users.username LIKE ?
        ORDER BY posts.created_at DESC
        LIMIT 50
    ";

    $statement = $conn->prepare($sql);

    if ($statement) {
        $statement->bind_param(
            "sss",
            $searchPattern,
            $searchPattern,
            $searchPattern
        );

        $statement->execute();

        $result = $statement->get_result();

        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }

        $statement->close();
    } else {
        $errorMessage = "Something went wrong while searching.";
    }
}

function escapeOutput($value)
{
    return htmlspecialchars(
        $value ?? "",
        ENT_QUOTES,
        "UTF-8"
    );
}

function shortenContent($content, $length = 180)
{
    $content = strip_tags($content);

    if (mb_strlen($content) <= $length) {
        return $content;
    }

    return mb_substr($content, 0, $length) . "...";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Search Posts | Shot Share</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

<div class="app-layout">

    <main class="search-page">

        <header class="search-header">
            <h2 class="search-title">Search Posts</h2>

            <p class="search-description">
                Search by post title, post content, or username.
            </p>
        </header>

        <form
            class="search-form"
            action="search.php"
            method="GET"
        >
            <label
                for="search-input"
                hidden
            >
                Search posts
            </label>

            <input
                class="search-input"
                id="search-input"
                type="search"
                name="q"
                placeholder="Search for posts..."
                value="<?= escapeOutput($searchQuery) ?>"
                autocomplete="off"
            >

            <button
                class="search-button"
                type="submit"
            >
                Search
            </button>
        </form>

        <?php if ($errorMessage !== ""): ?>

            <div class="error-message">
                <?= escapeOutput($errorMessage) ?>
            </div>

        <?php elseif ($searchQuery === ""): ?>

            <section class="empty-state">
                <h2>What are you looking for?</h2>

                <p>
                    Enter a title, keyword, or username in the search box.
                </p>
            </section>

        <?php elseif (count($posts) === 0): ?>

            <section class="empty-state">
                <h2>No results found</h2>

                <p>
                    We could not find any posts matching
                    “<?= escapeOutput($searchQuery) ?>”.
                </p>
            </section>

        <?php else: ?>

            <p class="search-information">
                Found
                <strong><?= count($posts) ?></strong>
                result<?= count($posts) === 1 ? "" : "s" ?>
                for
                “<?= escapeOutput($searchQuery) ?>”
            </p>

            <section class="search-results">

                <?php foreach ($posts as $post): ?>

                    <article class="post-card">

                        <div class="post-image-wrapper">

                            <?php if (!empty($post["image"])): ?>

                                <img
                                    class="post-image"
                                    src="../uploads/posts/<?= escapeOutput($post["image"]) ?>"
                                    alt="<?= escapeOutput($post["title"]) ?>"
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
                                    href="../post/view.php?id=<?= (int) $post["id"] ?>"
                                >
                                    <?= escapeOutput($post["title"]) ?>
                                </a>
                            </h2>

                            <div class="post-meta">
                                Posted by
                                <?= escapeOutput($post["username"]) ?>

                                <?php if (!empty($post["created_at"])): ?>
                                    ·
                                    <?= escapeOutput(
                                        date(
                                            "F j, Y",
                                            strtotime($post["created_at"])
                                        )
                                    ) ?>
                                <?php endif; ?>
                            </div>

                            <p class="post-description">
                                <?= escapeOutput(
                                    shortenContent($post["content"])
                                ) ?>
                            </p>

                            <a
                                class="view-post-link"
                                href="../post/view.php?id=<?= (int) $post["id"] ?>"
                            >
                                View post →
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </section>

        <?php endif; ?>

    </main>

</div>

</body>
</html>