<?php
http_response_code(404);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Page Not Found | Shot Share</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>
        .not-found-page {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 20px;
            background:
                radial-gradient(
                    circle at top,
                    #27364d 0%,
                    #121a26 45%,
                    #090e16 100%
                );
        }

        .not-found-card {
            width: min(600px, 100%);
            padding: 50px 30px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            background: rgba(19, 29, 43, 0.92);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
        }

        .not-found-code {
            margin: 0;
            font-size: clamp(90px, 20vw, 170px);
            line-height: 0.9;
            font-weight: 900;
            color: #4da3ff;
            letter-spacing: -8px;
        }

        .not-found-title {
            margin: 25px 0 12px;
            font-size: clamp(26px, 5vw, 38px);
            color: #ffffff;
        }

        .not-found-message {
            max-width: 450px;
            margin: 0 auto;
            color: #b7c2d0;
            font-size: 17px;
            line-height: 1.7;
        }

        .not-found-actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 32px;
        }

        .not-found-button {
            display: inline-block;
            padding: 13px 24px;
            border: 1px solid transparent;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            transition:
                transform 0.2s ease,
                background-color 0.2s ease,
                border-color 0.2s ease;
        }

        .not-found-button:hover {
            transform: translateY(-3px);
        }

        .not-found-button.primary {
            color: #ffffff;
            background-color: #2388ff;
        }

        .not-found-button.primary:hover {
            background-color: #0875e8;
        }

        .not-found-button.secondary {
            color: #dce7f5;
            border-color: rgba(255, 255, 255, 0.2);
            background-color: transparent;
        }

        .not-found-button.secondary:hover {
            border-color: #4da3ff;
            background-color: rgba(77, 163, 255, 0.1);
        }

        @media (max-width: 480px) {
            .not-found-card {
                padding: 40px 20px;
            }

            .not-found-code {
                letter-spacing: -4px;
            }

            .not-found-actions {
                flex-direction: column;
            }

            .not-found-button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <main class="not-found-page">
        <section class="not-found-card">
            <p class="not-found-code">404</p>

            <h1 class="not-found-title">
                Page Not Found
            </h1>

            <p class="not-found-message">
                Sorry, the page you are looking for does not exist,
                may have been deleted, or the URL may be incorrect.
            </p>

            <div class="not-found-actions">
                <a
                    class="not-found-button primary"
                    href="../SHOT_SHARE/index.php"
                >
                    Back to Home
                </a>

                <a
                    class="not-found-button secondary"
                    href="search.php"
                >
                    Search Posts
                </a>
            </div>
        </section>
    </main>
</body>
</html>