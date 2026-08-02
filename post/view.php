
<?php
session_start();
require_once '../config/database.php';

$post_id = $_GET['post_id'] ?? null;

if (!$post_id) {
    die('Post ID is required.');
}

// Fetch the post along with the author's full name
$stmt = $pdo->prepare("
    SELECT p.*, u.full_name 
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    WHERE p.post_id = ?
");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die('Post not found.');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Post Details</title>
</head>
<body>
<h1>Post Details</h1>

<p><strong>Author:</strong> <?= htmlspecialchars($post['full_name']) ?></p>
<p><strong>Created At:</strong> <?= htmlspecialchars($post['created_at']) ?></p>
<p><strong>Text:</strong><br><?= nl2br(htmlspecialchars($post['post_text'])) ?></p>

<?php if (!empty($post['image_path'])): ?>
    <p>
        <!-- Note: ../ because uploads folder is at project root level -->
        <img src="../<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image" style="max-width:400px;">
    </p>
<?php endif; ?>

<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
    <!-- Show delete button only for the owner of the post -->
    <form method="post" action="delete.php" onsubmit="return confirmDelete();">
        <input type="hidden" name="post_id" value="<?= (int)$post['post_id'] ?>">
        <button type="submit" style="color:red;">Delete Post</button>
    </form>
<?php endif; ?>

<!-- JavaScript confirm dialog -->
<script src="../assets/js/delete-confirmation.js"></script>
</body>
</html>