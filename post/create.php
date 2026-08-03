<?php

require_once __DIR__ . '/../database/db_connect.php';

$errors = [];
$success = '';
$post_text = '';
$image_path = null;

/*
|--------------------------------------------------------------------------
| Verify that the user is logged in
|--------------------------------------------------------------------------
|
| index.php should already start the session before loading this file.
|
*/

if (!isset($_SESSION['user_id'])) {
    echo '<p style="color:red;">You must log in before creating a post.</p>';
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $post_text = trim($_POST['post_text'] ?? '');
    $user_id = (int) $_SESSION['user_id'];

    /*
    |--------------------------------------------------------------------------
    | Validate post text
    |--------------------------------------------------------------------------
    */

    if ($post_text === '') {
        $errors[] = 'Post text cannot be empty.';

    } elseif (mb_strlen($post_text) > 2000) {
        $errors[] = 'Post text cannot exceed 2000 characters.';
    }

    /*
    |--------------------------------------------------------------------------
    | Handle optional image upload
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        $image = $_FILES['image'];

        // Confirm that PHP received the upload successfully
        if ($image['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'An error occurred while uploading the image.';

        } else {
            $max_size = 2 * 1024 * 1024; // 2 MB

            if ($image['size'] > $max_size) {
                $errors[] = 'Image is too large. Maximum size is 2MB.';
            }

            /*
             * Detect the real MIME type from the uploaded file.
             * Do not trust $_FILES["image"]["type"] because the user
             * can manipulate that value.
             */
            if (empty($errors)) {
                $file_info = new finfo(FILEINFO_MIME_TYPE);

                $mime_type = $file_info->file(
                    $image['tmp_name']
                );

                $allowed_types = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png'
                ];

                if (!isset($allowed_types[$mime_type])) {
                    $errors[] =
                        'Invalid image type. Only JPG, JPEG, and PNG are allowed.';

                } else {
                    $extension = $allowed_types[$mime_type];

                    /*
                    |--------------------------------------------------------------------------
                    | Prepare upload directory
                    |--------------------------------------------------------------------------
                    */

                    $upload_dir =
                        __DIR__ . '/../uploads/posts/';

                    if (
                        !is_dir($upload_dir) &&
                        !mkdir($upload_dir, 0755, true) &&
                        !is_dir($upload_dir)
                    ) {
                        $errors[] =
                            'Could not create the image upload directory.';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Generate and store the image
                    |--------------------------------------------------------------------------
                    */

                    if (empty($errors)) {
                        $new_name =
                            'post_' .
                            bin2hex(random_bytes(16)) .
                            '.' .
                            $extension;

                        $destination =
                            $upload_dir . $new_name;

                        if (
                            !move_uploaded_file(
                                $image['tmp_name'],
                                $destination
                            )
                        ) {
                            $errors[] = 'Failed to upload image.';

                        } else {
                            /*
                             * Store a path relative to the project root
                             * inside the database.
                             */
                            $image_path =
                                'uploads/posts/' . $new_name;
                        }
                    }
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Insert post into the database
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        try {
            $stmt = $conn->prepare(
                'INSERT INTO posts (
                    user_id,
                    post_text,
                    image_path
                )
                VALUES (?, ?, ?)'
            );

            $stmt->execute([
                $user_id,
                $post_text,
                $image_path
            ]);

            $success = 'Post created successfully.';
            $post_text = '';

        } catch (PDOException $e) {

            /*
             * Remove the uploaded image when database insertion fails,
             * otherwise an unused image remains on the server.
             */
            if ($image_path !== null) {
                $uploaded_file =
                    __DIR__ . '/../' . $image_path;

                if (is_file($uploaded_file)) {
                    unlink($uploaded_file);
                }
            }

            $errors[] =
                'Failed to create the post. Please try again.';
        }
    }
}

?>

<section class="create-post-page">

    <h1>Create Post</h1>

    <?php if (!empty($errors)): ?>

        <div class="errors">

            <?php foreach ($errors as $error): ?>

                <p style="color: red;">
                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <?php if ($success !== ''): ?>

        <p style="color: green;">
            <?= htmlspecialchars(
                $success,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

    <?php endif; ?>

    <form
        method="post"
        action="index.php?page=create-post"
        enctype="multipart/form-data"
    >

        <div>
            <label for="post_text">
                Post Text
            </label>

            <br>

            <textarea
                id="post_text"
                name="post_text"
                rows="5"
                cols="40"
                maxlength="2000"
                required
            ><?= htmlspecialchars(
                $post_text,
                ENT_QUOTES,
                'UTF-8'
            ) ?></textarea>
        </div>

        <br>

        <div>
            <label for="image-input">
                Image (JPG, JPEG, PNG — maximum 2MB)
            </label>

            <br>

            <input
                type="file"
                name="image"
                id="image-input"
                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
            >

            <br><br>

            <img
                id="image-preview"
                alt="Selected image preview"
                style="
                    display: none;
                    max-width: 200px;
                    height: auto;
                "
            >
        </div>

        <br>

        <button type="submit">
            Create
        </button>

    </form>

</section>

<script src="assets/js/image-preview.js"></script>