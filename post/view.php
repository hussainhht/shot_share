
<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../database/db_connect.php';

$post_id = $_GET['post_id'] ?? null;

if (!$post_id || $post_id <= 0) {
    http_response_code(400);
    echo '<p style="color: red;">Invalid Post ID.</p>';
    return;
}

// Fetch the post along with the author's full name
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
    WHERE p.post_id = ?
    LIMIT 1
");
$stmt->execute([$post_id]);

$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    http_response_code(404);
    echo '<p style="color: red;">Post not found.</p>';
    return;
}
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

</section>

<script src="assets/js/delete-confirmation.js"></script>