<?php

session_start();

require_once __DIR__ . '/../database/db_connect.php';



if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    echo '<p style="color:red;">You must log in before deleting a post.</p>';
    exit;
}



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=home');
    echo '<p style="color:red;">Invalid request method. Please use the delete button.</p>';
    exit;
}



$post_id = filter_input(
    INPUT_POST,
    'post_id',
    FILTER_VALIDATE_INT
);

if (!$post_id || $post_id <= 0) {
    http_response_code(400);
    echo '<p style="color:red;">Invalid Post ID.</p>';
    exit('Invalid Post ID.');
}

$user_id = (int) $_SESSION['user_id'];

try {

    //Fetch the post and verify ownership


    $stmt = $conn->prepare(
        'SELECT post_id, image_path
         FROM posts
         WHERE post_id = ?
         AND user_id = ?'
    );

    $stmt->execute([
        $post_id,
        $user_id
    ]);

    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        http_response_code(404);
        exit('Post not found or you are not allowed to delete it.');
    }



    $delete = $conn->prepare(
        'DELETE FROM posts
         WHERE post_id = ?
         AND user_id = ?'
    );

    $delete->execute([
        $post_id,
        $user_id
    ]);

    if ($delete->rowCount() !== 1) {
        throw new RuntimeException('The post could not be deleted.');
    }



    if (!empty($post['image_path'])) {

        $image_name = basename($post['image_path']);

        $file_path =
            __DIR__ . '/../uploads/posts/' . $image_name;

        if (is_file($file_path)) {

            if (!unlink($file_path)) {
                // Do not expose server information to the user
                error_log(
                    'Failed to delete post image: ' . $file_path
                );
            }
        }
    }



    header(
        'Location: ../index.php?page=home&msg=post_deleted'
    );

    exit;

} catch (PDOException $e) {

    error_log(
        'Database error while deleting post: ' .
        $e->getMessage()
    );

    http_response_code(500);
    exit('Failed to delete the post. Please try again.');

} catch (RuntimeException $e) {

    error_log(
        'Post deletion error: ' .
        $e->getMessage()
    );

    http_response_code(500);
    echo '<p style="color:red;">Failed to delete the post. Please try again.</p>';
    exit('Failed to delete the post. Please try again.');
}