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
    'profile_edit' => __DIR__ . '/profile/edit.php',
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

// Prepare display-only sidebar details from the authenticated session.
$sidebar_name = trim((string) ($_SESSION['full_name'] ?? 'Shot Share Member'));

if ($sidebar_name === '') {
    $sidebar_name = 'Shot Share Member';
}

$sidebar_name_parts = preg_split(
    '/\s+/u',
    $sidebar_name,
    -1,
    PREG_SPLIT_NO_EMPTY
);

$sidebar_first_character = static function (string $value): string {
    if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
        return mb_strtoupper(
            mb_substr($value, 0, 1, 'UTF-8'),
            'UTF-8'
        );
    }

    return strtoupper(substr($value, 0, 1));
};

$sidebar_initials = $sidebar_first_character($sidebar_name_parts[0]);

if (count($sidebar_name_parts) > 1) {
    $sidebar_initials .= $sidebar_first_character(
        $sidebar_name_parts[count($sidebar_name_parts) - 1]
    );
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

    <script
        src="assets/js/main.js"
        defer
    ></script>
</head>

<body>

<a class="skip-link" href="#main-content">
    Skip to content
</a>

<div class="app-layout">

    <aside
        class="sidebar"
        id="app-sidebar"
        aria-label="Shot Share sidebar"
    >

        <header class="sidebar-header">

            <a
                class="sidebar-brand"
                href="index.php?page=home"
                title="Shot Share home"
            >
                <span class="sidebar-avatar" aria-hidden="true">
                    <?= htmlspecialchars(
                        $sidebar_initials,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </span>

                <span class="sidebar-copy sidebar-brand-copy">
                    <strong class="sidebar-brand-title">
                        Shot Share
                    </strong>

                    <span class="sidebar-brand-subtitle">
                        <?= htmlspecialchars(
                            $sidebar_name,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>
                </span>
            </a>

            <button
                class="sidebar-collapse-toggle"
                id="sidebar-toggle"
                type="button"
                aria-controls="sidebar-content"
                aria-expanded="true"
                title="Collapse sidebar"
            >
                <span id="sidebar-toggle-icon" aria-hidden="true">
                    &#8249;
                </span>
                <span class="visually-hidden">
                    Collapse sidebar
                </span>
            </button>

        </header>

        <div class="sidebar-content" id="sidebar-content">

            <p class="sidebar-section-label">
                Main
            </p>

            <nav class="sidebar-nav" aria-label="Primary navigation">

                <a
                    class="sidebar-link<?= in_array($page, ['home', 'view-post'], true) ? ' is-active' : '' ?>"
                    href="index.php?page=home"
                    title="Home"
                    <?= in_array($page, ['home', 'view-post'], true) ? 'aria-current="page"' : '' ?>
                >
                    <span class="sidebar-icon" aria-hidden="true">
                        &#8962;
                    </span>
                    <span class="sidebar-copy">Home</span>
                </a>

                <a
                    class="sidebar-link<?= $page === 'create-post' ? ' is-active' : '' ?>"
                    href="index.php?page=create-post"
                    title="Create Post"
                    <?= $page === 'create-post' ? 'aria-current="page"' : '' ?>
                >
                    <span class="sidebar-icon" aria-hidden="true">+</span>
                    <span class="sidebar-copy">Create Post</span>
                </a>

                <a
                    class="sidebar-link<?= $page === 'search' ? ' is-active' : '' ?>"
                    href="index.php?page=search"
                    title="Search"
                    <?= $page === 'search' ? 'aria-current="page"' : '' ?>
                >
                    <span class="sidebar-icon" aria-hidden="true">
                        &#8981;
                    </span>
                    <span class="sidebar-copy">Search</span>
                </a>

                <a
                    class="sidebar-link<?= $page === 'profile_edit' ? ' is-active' : '' ?>"
                    href="index.php?page=profile_edit"
                    title="Profile"
                    <?= $page === 'profile_edit' ? 'aria-current="page"' : '' ?>
                >
                    <span class="sidebar-icon" aria-hidden="true">
                        &#9673;
                    </span>
                    <span class="sidebar-copy">Profile</span>
                </a>

                <a
                    class="sidebar-link<?= $page === 'inbox' ? ' is-active' : '' ?>"
                    href="index.php?page=inbox"
                    title="Inbox"
                    <?= $page === 'inbox' ? 'aria-current="page"' : '' ?>
                >
                    <span class="sidebar-icon" aria-hidden="true">
                        &#9993;
                    </span>
                    <span class="sidebar-copy">Inbox</span>
                </a>

            </nav>

            <a
                class="sidebar-cta"
                href="index.php?page=create-post"
                title="Create a new post"
            >
                <span class="sidebar-cta-icon" aria-hidden="true">+</span>

                <span class="sidebar-copy sidebar-cta-copy">
                    <strong>Share a moment</strong>
                    <small>Post a new shot for the community to see.</small>
                    <span class="sidebar-cta-button">Create Post</span>
                </span>
            </a>

            <footer class="sidebar-footer">

                <button
                    class="sidebar-action sidebar-theme-toggle"
                    id="theme-toggle"
                    type="button"
                    aria-pressed="false"
                    title="Switch theme"
                >
                    <span
                        class="sidebar-icon"
                        id="theme-toggle-icon"
                        aria-hidden="true"
                    >
                        &#9680;
                    </span>
                    <span class="sidebar-copy" id="theme-toggle-label">
                        Dark Mode
                    </span>
                </button>

                <a
                    class="sidebar-action sidebar-logout"
                    href="auth/logout.php"
                    title="Logout"
                >
                    <span class="sidebar-icon" aria-hidden="true">
                        &#8599;
                    </span>
                    <span class="sidebar-copy">Logout</span>
                </a>

            </footer>

        </div>

    </aside>

    <!-- Selected page appears here -->
    <main class="content" id="main-content">

        <?php require $page_file; ?>

    </main>

</div>

</body>
</html>
