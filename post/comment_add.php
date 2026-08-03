
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../database/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=home');
    exit;
}

$post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
$comment_text = trim($_POST['comment_text'] ?? '');

if (!$post_id || $post_id <= 0) {
    http_response_code(400);
    exit('Invalid post ID.');
}

if ($comment_text === '') {
    header('Location: ../index.php?page=view-post&post_id=' . (int) $post_id);
    exit;
}

if (mb_strlen($comment_text) > 1000) {
    $comment_text = mb_substr($comment_text, 0, 1000);
}

$user_id = (int) $_SESSION['user_id'];

try {
    $stmt = $conn->prepare(
        'INSERT INTO comments (
            post_id,
            user_id,
            comment_text
        )
        VALUES (?, ?, ?)'
    );

    $stmt->execute([
        $post_id,
        $user_id,
        $comment_text
    ]);

    header('Location: ../index.php?page=view-post&post_id=' . (int) $post_id);
    exit;

} catch (PDOException $e) {
    error_log('Error inserting comment: ' . $e->getMessage());
    http_response_code(500);
    exit('Failed to add comment. Please try again.');
}