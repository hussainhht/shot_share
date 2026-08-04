<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../database/db_connect.php';

// Read the post ID from the request.
$post_id = filter_input(INPUT_GET, 'post_id', FILTER_VALIDATE_INT);

if (!$post_id || $post_id <= 0) {
    http_response_code(400);
    echo '<p style="color: red;">Invalid Post ID.</p>';
    return;
}

// Load the post and its author.
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

// Load comments for the post.
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

// Load the like count and the current user's like state.
$likes_count_stmt = $conn->prepare(
    'SELECT COUNT(*) AS like_count
     FROM likes
     WHERE post_id = ?'
);
$likes_count_stmt->execute([$post_id]);
$likes_row = $likes_count_stmt->fetch(PDO::FETCH_ASSOC);
$likes_count = (int) ($likes_row['like_count'] ?? 0);

$user_liked = false;

if (isset($_SESSION['user_id'])) {
    $like_check_stmt = $conn->prepare(
        'SELECT like_id
         FROM likes
         WHERE post_id = ?
           AND user_id = ?
         LIMIT 1'
    );
    $like_check_stmt->execute([
        $post_id,
        (int) $_SESSION['user_id']
    ]);
    $user_liked = (bool) $like_check_stmt->fetch(PDO::FETCH_ASSOC);
}

// Presentation-only helpers for avatar initials and readable dates.
$build_initials = static function (string $name): string {
    $name_parts = preg_split(
        '/\s+/u',
        trim($name),
        -1,
        PREG_SPLIT_NO_EMPTY
    );

    if (empty($name_parts)) {
        return 'SS';
    }

    $initials = mb_substr($name_parts[0], 0, 1, 'UTF-8');

    if (count($name_parts) > 1) {
        $initials .= mb_substr(
            $name_parts[count($name_parts) - 1],
            0,
            1,
            'UTF-8'
        );
    }

    return mb_strtoupper($initials, 'UTF-8');
};

$format_date = static function (string $date, bool $include_time = false): string {
    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return $date;
    }

    return date(
        $include_time ? 'M j, Y \a\t g:i A' : 'M j, Y',
        $timestamp
    );
};

$post_initials = $build_initials($post['full_name']);
$comments_count = count($comments);

?>

<section class="post-details">

    <header class="post-view-header">
        <div class="post-author-avatar" aria-hidden="true">
            <?= htmlspecialchars($post_initials, ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="post-author-identity">
            <strong class="post-author-name">
                <?= htmlspecialchars(
                    $post['full_name'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>

            <?php if (!empty($post['username'])): ?>
                <span class="post-author-handle">
                    @<?= htmlspecialchars(
                        $post['username'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </span>
            <?php endif; ?>
        </div>

        <time
            class="post-published-date"
            datetime="<?= htmlspecialchars(
                $post['created_at'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >
            <?= htmlspecialchars(
                $format_date($post['created_at']),
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </time>
    </header>

    <div class="post-view-content">
        <h1 class="post-view-title">
            <?= htmlspecialchars(
                $post['title'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h1>

        <div class="post-view-text">
            <?= nl2br(
                htmlspecialchars(
                    $post['post_text'],
                    ENT_QUOTES,
                    'UTF-8'
                )
            ) ?>
        </div>

        <?php if (!empty($post['image_path'])): ?>
            <div class="post-image">
                <img
                    src="<?= htmlspecialchars(
                        $post['image_path'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    alt="Image attached to <?= htmlspecialchars(
                        $post['title'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >
            </div>
        <?php endif; ?>
    </div>

    <div class="post-action-row" aria-label="Post actions">

        <?php if (isset($_SESSION['user_id'])): ?>
            <form
                class="post-like-form"
                method="post"
                action="post/like.php"
            >
                <input
                    type="hidden"
                    name="post_id"
                    value="<?= (int) $post['post_id'] ?>"
                >

                <button
                    class="post-action-button post-like-button<?= $user_liked ? ' is-active' : '' ?>"
                    type="submit"
                    aria-label="<?= $user_liked ? 'Unlike this post' : 'Like this post' ?>"
                >
                    <span class="post-action-icon" aria-hidden="true">
                        <?= $user_liked ? '&#9829;' : '&#9825;' ?>
                    </span>
                    <span><?= $user_liked ? 'Unlike' : 'Like' ?></span>
                    <span class="post-action-count"><?= $likes_count ?></span>
                </button>
            </form>
        <?php else: ?>
            <span
                class="post-action-button"
                aria-label="<?= $likes_count ?> likes"
            >
                <span class="post-action-icon" aria-hidden="true">&#9825;</span>
                <span>Like</span>
                <span class="post-action-count"><?= $likes_count ?></span>
            </span>
        <?php endif; ?>

        <a class="post-action-button" href="#post-comments">
            <span class="post-action-icon" aria-hidden="true">&#128172;</span>
            <span>Comments</span>
            <span class="post-action-count"><?= $comments_count ?></span>
        </a>
    </div>

    <section class="post-comments" id="post-comments">
        <header class="post-comments-header">
            <h2>Comments</h2>
            <span><?= $comments_count ?></span>
        </header>

        <?php if (empty($comments)): ?>
            <p class="post-comments-empty">
                No comments yet. Be the first to comment.
            </p>
        <?php else: ?>
            <div class="post-comment-list">

                <?php foreach ($comments as $comment): ?>
                    <?php
                    $comment_initials = $build_initials(
                        $comment['full_name']
                    );
                    ?>

                    <article class="post-comment">
                        <div class="comment-avatar" aria-hidden="true">
                            <?= htmlspecialchars(
                                $comment_initials,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </div>

                        <div class="comment-content">
                            <header class="comment-header">
                                <strong>
                                    <?= htmlspecialchars(
                                        $comment['full_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>

                                <?php if (!empty($comment['username'])): ?>
                                    <span class="comment-handle">
                                        @<?= htmlspecialchars(
                                            $comment['username'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>
                                <?php endif; ?>

                                <time
                                    datetime="<?= htmlspecialchars(
                                        $comment['created_at'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                                    <?= htmlspecialchars(
                                        $format_date(
                                            $comment['created_at'],
                                            true
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </time>
                            </header>

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
                    </article>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form
                class="post-comment-form"
                method="post"
                action="post/comment_add.php"
            >
                <input
                    type="hidden"
                    name="post_id"
                    value="<?= (int) $post['post_id'] ?>"
                >

                <label class="visually-hidden" for="comment-text">
                    Write a comment
                </label>

                <textarea
                    id="comment-text"
                    name="comment_text"
                    rows="1"
                    maxlength="1000"
                    placeholder="Write a comment..."
                    required
                ></textarea>

                <button class="post-comment-submit" type="submit">
                    Post
                </button>
            </form>
        <?php endif; ?>
    </section>

    <?php if (
        isset($_SESSION['user_id']) &&
        (int) $_SESSION['user_id'] === (int) $post['user_id']
    ): ?>
        <footer class="post-owner-actions">
            <form
                class="post-delete-form"
                method="post"
                action="post/delete.php"
                onsubmit="return confirmDelete();"
            >
                <input
                    type="hidden"
                    name="post_id"
                    value="<?= (int) $post['post_id'] ?>"
                >

                <button class="post-delete-button" type="submit">
                    Delete Post
                </button>
            </form>
        </footer>
    <?php endif; ?>

</section>

<script src="assets/js/delete-confirmation.js"></script>
