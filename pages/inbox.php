<?php

require_once __DIR__ . '/../database/db_connect.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT
        notifications.notification_id,
        notifications.type,
        notifications.post_id,
        notifications.is_read,
        notifications.created_at,
        users.full_name,
        users.username
    FROM notifications

    INNER JOIN users
        ON notifications.actor_id = users.user_id

    WHERE notifications.user_id = ?

    ORDER BY notifications.created_at DESC"
);

$stmt->execute([$user_id]);

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<section class="notifications-page">
    <header class="notifications-header">
        <div>
            <h1>Notifications</h1>
            <p>See the latest activity on your posts.</p>
        </div>

        <?php if (!empty($notifications)): ?>
            <span class="notifications-count" aria-label="<?= count($notifications) ?> notifications">
                <?= count($notifications) ?>
            </span>
        <?php endif; ?>
    </header>

    <?php if (empty($notifications)): ?>

        <div class="empty-state notifications-empty-state">
            <span class="notifications-empty-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" focusable="false">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0" />
                </svg>
            </span>

            <h2>No notifications yet</h2>

            <p>
                Likes and comments on your posts will appear here.
            </p>
        </div>

    <?php else: ?>

        <div class="notifications-list" aria-label="Recent notifications">

        <?php foreach ($notifications as $notification): ?>
            <?php
            $name_parts = preg_split('/\s+/', trim($notification['full_name']));
            $initials = '';

            foreach (array_slice($name_parts, 0, 2) as $name_part) {
                preg_match('/^./u', $name_part, $initial_match);
                $first_character = $initial_match[0] ?? substr($name_part, 0, 1);
                $initials .= function_exists('mb_strtoupper')
                    ? mb_strtoupper($first_character, 'UTF-8')
                    : strtoupper($first_character);
            }

            $initials = $initials !== '' ? $initials : '?';
            $is_like = $notification['type'] === 'like';
            $action_text = $is_like ? 'liked your post.' : 'commented on your post.';
            $created_at_timestamp = strtotime($notification['created_at']);
            $display_date = $created_at_timestamp
                ? date('M j, Y \a\t g:i A', $created_at_timestamp)
                : $notification['created_at'];
            ?>

            <a
                class="notification-item<?= (int) $notification['is_read'] === 0 ? ' is-unread' : '' ?>"
                href="index.php?page=view-post&post_id=<?= (int) $notification['post_id'] ?>"
            >
                <span class="notification-avatar" aria-hidden="true">
                    <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>

                    <span class="notification-type-icon<?= $is_like ? ' is-like' : ' is-comment' ?>">
                        <?php if ($is_like): ?>
                            <svg viewBox="0 0 24 24" focusable="false">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z" />
                            </svg>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" focusable="false">
                                <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z" />
                            </svg>
                        <?php endif; ?>
                    </span>
                </span>

                <span class="notification-content">
                    <span class="notification-message">
                        <strong><?= htmlspecialchars($notification['full_name'], ENT_QUOTES, 'UTF-8') ?></strong>

                        <span class="notification-handle">
                            @<?= htmlspecialchars($notification['username'], ENT_QUOTES, 'UTF-8') ?>
                        </span>

                        <span><?= $action_text ?></span>
                    </span>

                    <time
                        class="notification-time"
                        datetime="<?= htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <?= htmlspecialchars($display_date, ENT_QUOTES, 'UTF-8') ?>
                    </time>
                </span>

                <?php if ((int) $notification['is_read'] === 0): ?>
                    <span class="notification-unread-dot" aria-label="Unread notification"></span>
                <?php else: ?>
                    <span class="notification-arrow" aria-hidden="true">&rsaquo;</span>
                <?php endif; ?>

            </a>

        <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>
