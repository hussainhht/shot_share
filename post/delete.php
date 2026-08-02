
<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_login(); // only logged-in users can access this

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If request method is not POST, redirect to home (or another page)
    header('Location: ../index.php');
    exit;
}

$post_id = $_POST['post_id'] ?? null;

if (!$post_id) {
    die('Post ID is required.');
}

// 1) Fetch the post and verify it exists
$stmt = $pdo->prepare("SELECT * FROM posts WHERE post_id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die('Post not found.');
}

// 2) Verify that the current user is the owner of this post
if ($post['user_id'] != $_SESSION['user_id']) {
    die('You are not allowed to delete this post.');
}

// 3) Delete the image file from the server if it exists
if (!empty($post['image_path'])) {
    $file_path = __DIR__ . '/../' . $post['image_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// 4) Delete the post record from the database
$del = $pdo->prepare("DELETE FROM posts WHERE post_id = ?");
$del->execute([$post_id]);

// 5) Redirect to home page (or another page) with a message
header('Location: ../index.php?msg=post_deleted');
exit;