# Shot Share

Shot Share is a PHP + MySQL community blog / photo-sharing app. Signed-in users post a title, caption and an optional image, browse a global feed, search posts, like and comment, and get notified when someone interacts with their posts.

Built as the course project for **ITCS 333 — Internet Software Development**, University of Bahrain.

---

## Features

- **Authentication** — register, log in, log out, session-protected pages (`auth/`)
- **Profile editing** — update full name and password (`profile/edit.php`)
- **Posts** — create a post with an optional image, view a post's details, delete your own posts (`post/`)
- **Likes & comments** — like a post and add comments (`post/like.php`, `post/comment_add.php`)
- **Notifications / Inbox** — get a like/comment notification and see them on the Inbox page (`pages/inbox.php`)
- **Home feed** — all posts, newest first (`pages/home.php`)
- **Search** — find posts by keyword (`pages/search.php`)
- **Cats** — a bonus gallery page that pulls random cat images through a same-origin PHP proxy to [The Cat API](https://thecatapi.com/) (`pages/cat.php`, `api/cats.php`)
- **Light / dark theme** — toggle from the sidebar, preference saved in `localStorage`
- **Responsive layout** — collapsible sidebar on desktop, bottom navigation on mobile

## Tech stack

- **Backend:** PHP (no framework) with PDO + prepared statements
- **Database:** MySQL / MariaDB (`database/schema.sql`)
- **Frontend:** Server-rendered PHP views, vanilla JavaScript, hand-written CSS (design tokens sourced from `style-beta/*.scss`)
- **Server:** Apache via XAMPP

## Project structure

```
shot_share/
│
├── index.php                 # Entry point + router + sidebar shell
│
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
│
├── database/
│   ├── db_connect.php        # PDO connection (edit credentials here)
│   └── schema.sql            # Database + table definitions
│
├── pages/
│   ├── home.php               # Global feed
│   ├── search.php             # Post search
│   ├── cat.php                # Cats gallery
│   ├── inbox.php              # Notifications
│   ├── profile.php
│   └── not-found.php          # 404
│
├── post/
│   ├── create.php             # Create post + image upload
│   ├── view.php                # Post details
│   ├── delete.php              # Delete own post
│   ├── like.php                 # Like/unlike a post
│   └── comment_add.php          # Add a comment
│
├── profile/
│   └── edit.php                # Edit name / password
│
├── api/
│   └── cats.php                 # Authenticated proxy for The Cat API
│
├── assets/
│   ├── css/style.css            # Shared stylesheet (light + dark theme)
│   └── js/
│       ├── main.js               # Theme + sidebar behavior
│       ├── search.js
│       ├── cat.js
│       ├── image-preview.js
│       └── delete-confirmation.js
│
├── uploads/
│   ├── posts/                    # Uploaded post images
│   └── users/                    # Uploaded profile photos
│
├── style-beta/                   # Design reference (SCSS palette + HTML mockups)
│
└── docs/                         # Feature write-ups and project notes
```

## Database

`database/schema.sql` creates the `shot_share` database and these tables:

| Table             | Purpose                                                                  |
| ----------------- | ------------------------------------------------------------------------ |
| `users`         | Account info, hashed password, profile photo,`has_created_post` flag   |
| `posts`         | Title, text, optional image, author, timestamp                           |
| `comments`      | Comments on a post                                                       |
| `likes`         | One row per user like on a post                                          |
| `notifications` | Like/comment notifications, linked to actor, post and (optional) comment |

All foreign keys cascade on delete, so removing a user or post cleans up its related rows automatically.

## Getting started (XAMPP)

1. **Get the code into `htdocs`.** If you're already in `c:\xampp\htdocs\shot_share`, you're set.
2. **Start Apache and MySQL** from the XAMPP control panel.
3. **Create the database.** Open phpMyAdmin (or the `mysql` CLI) and import `database/schema.sql`. This creates the `shot_share` database and all tables.
4. **Check the DB credentials** in `database/db_connect.php`. Defaults match a stock XAMPP install (`host: localhost`, `user: root`, no password). Update them if your setup differs.
5. **Browse to the app:** `http://localhost/shot_share/`
6. **Register an account**, then log in — most pages require an authenticated session and redirect to `auth/login.php` otherwise.

No build step, package manager, or Composer/npm install is required — everything runs as plain PHP/JS/CSS served by Apache.

## How routing works

There's a single entry point, `index.php`. It reads `?page=` from the query string and maps it to a file through an allow-list (see `$routes` in `index.php`):

```
index.php?page=home          -> pages/home.php
index.php?page=create-post   -> post/create.php
index.php?page=view-post     -> post/view.php
index.php?page=search        -> pages/search.php
index.php?page=cats          -> pages/cat.php
index.php?page=inbox         -> pages/inbox.php
index.php?page=profile_edit  -> profile/edit.php
```

Anything not in the allow-list renders `pages/not-found.php` with a 404 status. `index.php` also renders the shared sidebar (branding, nav, theme toggle, logout) around whichever page file is included.

## Docs

More detailed, feature-specific notes live in `docs/`:

- `docs/CATS_FEATURE.md` — how the Cats gallery and its API proxy work
- `docs/STYLE_IMPLEMENTATION.md` — the color system, theming, and responsive breakpoints
- `docs/TEAM_WORK_DISTRIBUTION.md` — how work was split across contributors

## Team

| Name               | GitHub                                                    |
| ------------------ | --------------------------------------------------------- |
| Hussain Ali H. Ali | [@hussainhht](https://github.com/hussainhht)               |
| Abdulaziz Hassan   | [@AbdulazizHassan03](https://github.com/AbdulazizHassan03) |
| yusef              | [@yalkhedri0](https://github.com/yalkhedri0)               |

See `docs/TEAM_WORK_DISTRIBUTION.md` for how responsibilities were split (auth/profile, posts/uploads, feed/search/design).
