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

if (!$post_id || $post_id <= 0) {
    http_response_code(400);
    exit('Invalid post ID.');
}

$user_id = (int) $_SESSION['user_id'];

try {
    // Check if user already liked this post
    $check = $conn->prepare(
        'SELECT like_id
         FROM likes
         WHERE post_id = ?
           AND user_id = ?
         LIMIT 1'
    );
    $check->execute([$post_id, $user_id]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Unlike
        $delete = $conn->prepare(
            'DELETE FROM likes
             WHERE like_id = ?'
        );
        $delete->execute([$existing['like_id']]);

    } else {
        // Add like
        $insert = $conn->prepare(
            'INSERT INTO likes (
                post_id,
                user_id
            )
            VALUES (?, ?)'
        );
        $insert->execute([$post_id, $user_id]);
    }
    $stmt = $conn->prepare(
        "SELECT user_id
     FROM posts
     WHERE post_id = ?"
    );

    $stmt->execute([$post_id]);

    $postOwner = $stmt->fetch(PDO::FETCH_ASSOC);
    $owner_id = $postOwner['user_id'];
    $actor_id = $_SESSION['user_id'];

    if ($owner_id != $actor_id) {

        $notification = $conn->prepare(
            "INSERT INTO notifications
        (user_id, actor_id, post_id, type)
        VALUES (?, ?, ?, 'like')"
        );

        $notification->execute([
            $owner_id,
            $actor_id,
            $post_id
        ]);
    }

    header('Location: ../index.php?page=view-post&post_id=' . (int) $post_id);
    exit;

} catch (PDOException $e) {
    error_log('Error toggling like: ' . $e->getMessage());
    http_response_code(500);
    exit('Failed to update like. Please try again.');
}