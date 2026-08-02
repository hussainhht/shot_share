
<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php'; // contains require_login()
require_login(); // allow only logged-in users

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_text = trim($_POST['post_text'] ?? '');
    $user_id = $_SESSION['user_id']; // set by Member 1 in login
    $image_path = null;

    // 1) Validate that post text is not empty
    if ($post_text === '') {
        $errors[] = 'Post text cannot be empty.';
    }

    // 2) Handle image upload if provided
    if (!empty($_FILES['image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        $tmp_name  = $_FILES['image']['tmp_name'];

        // Maximum file size example: 2MB
        $max_size = 2 * 1024 * 1024;

        // Validate MIME type
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'Invalid image type. Only JPG, JPEG, and PNG are allowed.';
        }

        // Validate file size
        if ($file_size > $max_size) {
            $errors[] = 'Image is too large. Max size is 2MB.';
        }

        if (empty($errors)) {
            // Get original extension and normalize to lowercase
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $ext = strtolower($ext);

            // Generate a unique file name to avoid conflicts
            $new_name = uniqid('post_', true) . '.' . $ext;

            // Physical upload directory on the server
            $upload_dir = __DIR__ . '/../uploads/posts/';
            if (!is_dir($upload_dir)) {
                // Create directory if it does not exist
                mkdir($upload_dir, 0777, true);
            }
            $dest_path = $upload_dir . $new_name;

            // Move uploaded file to target directory
            if (!move_uploaded_file($tmp_name, $dest_path)) {
                $errors[] = 'Failed to upload image.';
            } else {
                // Relative path stored in the database
                $image_path = 'uploads/posts/' . $new_name;
            }
        }
    }

    // 3) Insert post into database if there are no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, post_text, image_path) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $post_text, $image_path]);

        $success = 'Post created successfully.';
        // Clear textarea after successful submission
        $post_text = '';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Create Post</title>
    <!-- Add CSS or Bootstrap/Tailwind here (Member 3 responsibility) -->
</head>
<body>
<h1>Create Post</h1>

<?php if (!empty($errors)): ?>
    <div style="color:red;">
        <?php foreach ($errors as $e): ?>
            <p><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color:green;">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <div>
        <label for="post_text">Post Text</label><br>
        <textarea id="post_text" name="post_text" rows="5" cols="40"><?= isset($post_text) ? htmlspecialchars($post_text) : '' ?></textarea>
    </div>

    <div>
        <label for="image-input">Image (JPG, JPEG, PNG)</label><br>
        <input type="file" name="image" id="image-input" accept=".jpg,.jpeg,.png">
        <br><br>
        <!-- Image preview element (controlled by JS) -->
        <img id="image-preview" src="#" alt="Preview" style="display:none; max-width:200px;">
    </div>

    <br>
    <button type="submit">Create</button>
</form>

<!-- JavaScript for dynamic image preview -->
<script src="../assets/js/image-preview.js"></script>
</body>
</html>