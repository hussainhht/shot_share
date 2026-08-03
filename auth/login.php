<?php

session_start();

require_once __DIR__ . '/../database/db_connect.php';

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate email
    if ($email === '') {
        $errors[] = 'Email is required.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // Validate password
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    // Check email and password
    if (empty($errors)) {

        $stmt = $conn->prepare("
            SELECT
                user_id,
                full_name,
                email,
                password
            FROM users
            WHERE email = ?
        ");

        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            $user &&
            password_verify($password, $user['password'])
        ) {
            // Prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            header("Location: ../profile/edit.php");
            exit();
           
        } 
        else {
            $errors[] = "Invalid email or password.";
        }
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Shot Share</title>
</head>

<body>

<div class="first">

    <h1>Login</h1>

    <form method="POST" action="">

        <label for="email">
            Email:
        </label>

        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($email) ?>"
            autocomplete="email"
            required
        >

        <br><br>

        <label for="password">
            Password:
        </label>

        <input
            type="password"
            id="password"
            name="password"
            autocomplete="current-password"
            required
        >

        <br><br>

        <button type="submit">
            Login
        </button>

    </form>

    <?php if (!empty($errors)): ?>

        <?php foreach ($errors as $error): ?>

            <p style="color: red;">
                <?= htmlspecialchars($error) ?>
            </p>

        <?php endforeach; ?>

    <?php endif; ?>

    <p>
        Don't have an account?
        <a href="register.php">Register here</a>
    </p>

</div>

</body>
</html>