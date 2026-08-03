<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../database/db_connect.php';


$post_id = filter_input(INPUT_GET, 'post_id', FILTER_VALIDATE_INT);

if (!$post_id || $post_id <= 0) {
    http_response_code(400);
    echo '<p style="color: red;">Invalid Post ID.</p>';
    return;
}

$post_stmt = $conn->prepare("
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
    WHERE p.post_id = ?
    LIMIT 1
");
$post_stmt->execute([$post_id]);
$post = $post_stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    http_response_code(404);
    echo '<p style="color: red;">Post not found.</p>';
    return;
}


$comments_stmt = $conn->prepare("
    SELECT
        c.comment_id,
        c.comment_text,
        c.created_at,
        u.full_name
    FROM comments AS c
    INNER JOIN users AS u
        ON c.user_id = u.user_id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
");
$comments_stmt->execute([$post_id]);
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<section class="post-details">

    <h1>
        <?= htmlspecialchars(
            $post['title'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </h1>

    <p>
        <strong>Author:</strong>
        <?= htmlspecialchars(
            $post['full_name'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </p>

    <p>
        <strong>Created At:</strong>
        <?= htmlspecialchars(
            $post['created_at'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </p>

    <div>
        <strong>Text:</strong>

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
                style="max-width: 400px; height: auto;"
            >
        </div>
    <?php endif; ?>

    <?php if (
        isset($_SESSION['user_id']) &&
        (int) $_SESSION['user_id'] === (int) $post['user_id']
    ): ?>
        <form
            method="post"
            action="post/delete.php"
            onsubmit="return confirmDelete();"
        >
            <input
                type="hidden"
                name="post_id"
                value="<?= (int) $post['post_id'] ?>"
            >
            <button type="submit" style="color: red;">
                Delete Post
            </button>
        </form>
    <?php endif; ?>

   
    <div class="post-comments">
        <h2>Comments</h2>

        <?php if (empty($comments)): ?>
            <p>No comments yet. Be the first to comment.</p>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <div class="post-comment">
                    <p>
                        <strong>
                            <?= htmlspecialchars(
                                $comment['full_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>
                        <span class="post-meta">
                            ·
                            <?= htmlspecialchars(
                                $comment['created_at'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </span>
                    </p>

                    <p>
                        <?= nl2br(
                            htmlspecialchars(
                                $comment['comment_text'],
                                ENT_QUOTES,
                                'UTF-8'
                            )
                        ) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="post-add-comment">
            <h3>Add a comment</h3>

            <form
                method="post"
                action="post/comment_add.php"
            >
                <input
                    type="hidden"
                    name="post_id"
                    value="<?= (int) $post['post_id'] ?>"
                >
                <textarea
                    name="comment_text"
                    rows="3"
                    maxlength="1000"
                    required
                ></textarea>

                <button type="submit">
                    Post Comment
                </button>
            </form>
        </div>
    <?php endif; ?>
</section>

<script src="assets/js/delete-confirmation.js"></script>