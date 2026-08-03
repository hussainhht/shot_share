<?php

require_once __DIR__ . '/../database/db_connect.php';

$posts = [];
$load_error = '';

try {
  $stmt = $conn->prepare("
        SELECT
            p.post_id,
            p.user_id,
            p.title,
            p.post_text,
            p.image_path,
            p.created_at,
            u.full_name
        FROM posts AS p
        INNER JOIN users AS u
            ON p.user_id = u.user_id
        ORDER BY p.created_at DESC
    ");

    $stmt->execute();

    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $load_error = 'Failed to load posts.';
    error_log($e->getMessage());
}

?>

<section class="home-page">

    <div class="home-header">
        <h1>Home</h1>

        <p>Latest posts from the Shot Share community.</p>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'post_deleted'): ?>

        <div class="success-message">
            Post deleted successfully.
        </div>

    <?php endif; ?>

    <?php if ($load_error !== ''): ?>

        <div class="error-message">
            <?= htmlspecialchars($load_error) ?>
        </div>

    <?php elseif (empty($posts)): ?>

        <div class="empty-posts">
            <h2>No posts yet</h2>

            <p>Be the first person to create a post.</p>

            <a href="index.php?page=create-post">
                Create Post
            </a>
        </div>

    <?php else: ?>

        <div class="posts-list">

            <?php foreach ($posts as $post): ?>

                <article class="post-card">

                    <header class="post-header">

                        <div>
                            <h2 class="post-author">
                                <?= htmlspecialchars(
                                    $post['full_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </h2>

                            <time class="post-date">
                                <?= htmlspecialchars(
                                    $post['created_at'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </time>
                        </div>

                    </header>

                   <div class="post-content">

    <h3 class="post-title">
        <?= htmlspecialchars(
            $post['title'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </h3>

    <p>
        <?= nl2br(
            htmlspecialchars(
                $post['post_text'],
                ENT_QUOTES,
                'UTF-8'
            )
        ) ?>
    </p>

</div>
                    <?php if (!empty($post['image_path'])): ?>

                        <div class="post-image">

                            <img
                                src="<?= htmlspecialchars(
                                    $post['image_path'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                alt="Image attached to the post"
                            >

                        </div>

                    <?php endif; ?>

                    <footer class="post-footer">

                        <a
                            class="view-post-link"
                            href="index.php?page=view-post&post_id=<?= (int) $post['post_id'] ?>"
                        >
                            View Post
                        </a>

                    </footer>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>