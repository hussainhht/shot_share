<?php

session_start();

// Check whether the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Get requested page
$page = $_GET['page'] ?? 'home';

// Allowed pages only
$routes = [
    'home' => __DIR__ . '/pages/home.php',
    'create-post' => __DIR__ . '/post/create.php',
    'profile' => __DIR__ . '/pages/profile.php',
    'search' => __DIR__ . '/pages/search.php',
    'view-post' => __DIR__ . '/post/view.php'
];

// Select page file
if (isset($routes[$page])) {
    $page_file = $routes[$page];
} else {
    http_response_code(404);
    $page_file = __DIR__ . '/pages/not-found.php';
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

    <title>Shot Share</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >
</head>

<body>

<div class="app-layout">

    <!-- Fixed Sidebar -->
    <aside class="sidebar">

        <h1 class="logo">
            Shot Share
        </h1>

        <nav class="sidebar-nav">

            <a href="index.php?page=home">
                Home
            </a>

            <a href="index.php?page=create-post">
                Create Post
            </a>

            <a href="index.php?page=search">
                Search
            </a>

            <a href="index.php?page=profile">
                Profile
            </a>

            <a href="auth/logout.php">
                Logout
            </a>

        </nav>

    </aside>

    <!-- Selected page appears here -->
    <main class="content">

        <?php require $page_file; ?>

    </main>

</div>

</body>
</html>