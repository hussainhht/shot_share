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
                username,
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
            $_SESSION['username'] = $user['username'];

            header('Location: ../index.php');
            exit;

        } else {
            $errors[] = 'Invalid email or password.';
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

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <script
        src="../assets/js/main.js"
        defer
    ></script>
</head>

<body class="auth-body">

<div class="auth-page">

    <header class="auth-topbar">

        <a class="auth-brand" href="login.php">
            <span class="auth-brand-mark" aria-hidden="true"></span>
            <span>Shot Share</span>
        </a>

        <button
            class="auth-theme-toggle"
            id="theme-toggle"
            type="button"
            aria-pressed="false"
            title="Switch theme"
        >
            <span id="theme-toggle-icon" aria-hidden="true">&#9680;</span>
            <span id="theme-toggle-label">Dark Mode</span>
        </button>

    </header>

    <main class="auth-main">

        <section class="auth-intro" aria-labelledby="login-intro-title">
            <p class="auth-eyebrow">Welcome to Shot Share</p>

            <h1 id="login-intro-title">
                Share moments.
                <span>Connect people.</span>
            </h1>

            <p class="auth-intro-text">
                A simple place to share photos, ideas, and everyday moments
                with your community.
            </p>

            <div class="auth-benefits" aria-label="Shot Share features">
                <div class="auth-benefit">
                    <span aria-hidden="true">01</span>
                    <p>
                        <strong>Share simply</strong>
                        <small>Publish a thought or photo in a few steps.</small>
                    </p>
                </div>

                <div class="auth-benefit">
                    <span aria-hidden="true">02</span>
                    <p>
                        <strong>Stay connected</strong>
                        <small>See the latest posts from the community.</small>
                    </p>
                </div>

                <div class="auth-benefit">
                    <span aria-hidden="true">03</span>
                    <p>
                        <strong>Keep it personal</strong>
                        <small>Your account keeps your activity together.</small>
                    </p>
                </div>
            </div>
        </section>

        <section class="auth-card" aria-labelledby="login-title">

            <div class="auth-card-header">
                <span class="auth-card-mark" aria-hidden="true"></span>
                <p class="auth-eyebrow">Your account</p>
                <h2 id="login-title">Welcome back</h2>
                <p>Sign in to continue to Shot Share.</p>
            </div>

            <?php if (
                isset($_GET['registered']) &&
                $_GET['registered'] === '1'
            ): ?>

                <div class="success-message auth-alert" role="status">
                    Account created successfully. You can sign in now.
                </div>

            <?php endif; ?>

            <?php if (!empty($errors)): ?>

                <div class="errors auth-alert" role="alert">
                    <p>Please check the following:</p>

                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li>
                                <?= htmlspecialchars(
                                    $error,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            <?php endif; ?>

            <form class="auth-form" method="POST" action="">

                <div class="auth-field">
                    <label for="email">Email address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars(
                            $email,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="name@example.com"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button class="auth-submit" type="submit">
                    Sign In
                </button>

            </form>

            <p class="auth-switch">
                Don't have an account?
                <a href="register.php">Create one</a>
            </p>

        </section>

    </main>

</div>

</body>
</html>
