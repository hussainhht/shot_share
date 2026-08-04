<?php

// Connect to database
require_once __DIR__ . "/../database/db_connect.php";


// Form values
$errors = [];

$full_name = "";
$username = "";
$email = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    |--------------------------------------------------------------------------
    | Get form values
    |--------------------------------------------------------------------------
    */

    $full_name = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");

    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | Validate full name
    |--------------------------------------------------------------------------
    */

    if ($full_name === "") {

        $errors[] = "Full name is required.";

    } elseif (
        mb_strlen($full_name, "UTF-8") < 3 ||
        mb_strlen($full_name, "UTF-8") > 100
    ) {

        $errors[] =
            "Full name must be between 3 and 100 characters long.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validate username
    |--------------------------------------------------------------------------
    */

    if ($username === "") {

        $errors[] = "Username is required.";

    } elseif (
        strlen($username) < 3 ||
        strlen($username) > 50
    ) {

        $errors[] =
            "Username must be between 3 and 50 characters long.";

    } elseif (
        !preg_match(
            "/^[a-zA-Z0-9_]+$/",
            $username
        )
    ) {

        $errors[] =
            "Username can only contain letters, numbers, and underscores.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validate email
    |--------------------------------------------------------------------------
    */

    if ($email === "") {

        $errors[] = "Email is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Invalid email format.";

    } else {

        /*
         * Store emails in lowercase for consistency.
         */
        $email = strtolower($email);
    }


    /*
    |--------------------------------------------------------------------------
    | Check if username already exists
    |--------------------------------------------------------------------------
    */

    if (
        $username !== "" &&
        preg_match("/^[a-zA-Z0-9_]+$/", $username)
    ) {

        $check_username = $conn->prepare(
            "SELECT user_id
             FROM users
             WHERE username = ?
             LIMIT 1"
        );

        $check_username->execute([
            $username
        ]);

        $existing_username =
            $check_username->fetch(PDO::FETCH_ASSOC);

        if ($existing_username) {

            $errors[] =
                "Username already exists.";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Check if email already exists
    |--------------------------------------------------------------------------
    */

    if (
        $email !== "" &&
        filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $check_email = $conn->prepare(
            "SELECT user_id
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $check_email->execute([
            $email
        ]);

        $existing_email =
            $check_email->fetch(PDO::FETCH_ASSOC);

        if ($existing_email) {

            $errors[] =
                "Email already exists.";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate password
    |--------------------------------------------------------------------------
    */

    $password_pattern =
        "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#$!%*_?&])[A-Za-z\d@#$!%*_?&]{8,20}$/";


    if ($password === "") {

        $errors[] =
            "Password is required.";

    } elseif (
        !preg_match(
            $password_pattern,
            $password
        )
    ) {

        $errors[] =
            "Password must be 8-20 characters long, contain no spaces, "
            . "and include at least one uppercase letter, one lowercase "
            . "letter, one number, and one special character (@#$!%*_?&).";
    }


    /*
    |--------------------------------------------------------------------------
    | Validate password confirmation
    |--------------------------------------------------------------------------
    */

    if ($confirm_password === "") {

        $errors[] =
            "Please confirm your password.";

    } elseif ($password !== $confirm_password) {

        $errors[] =
            "Passwords do not match.";
    }


    /*
    |--------------------------------------------------------------------------
    | Create account
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $hashed_password = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        try {

            $stmt = $conn->prepare(
                "INSERT INTO users (
                    username,
                    full_name,
                    email,
                    password
                )
                VALUES (?, ?, ?, ?)"
            );


            $stmt->execute([
                $username,
                $full_name,
                $email,
                $hashed_password
            ]);


            header(
                "Location: login.php?registered=1"
            );

            exit;


        } catch (PDOException $error) {

            /*
             * This protects against duplicate values
             * even if two registrations happen at
             * nearly the same time.
             */

            if ($error->getCode() === "23000") {

                $errors[] =
                    "The email or username is already registered.";

            } else {

                $errors[] =
                    "Registration failed. Please try again.";

                error_log(
                    $error->getMessage()
                );
            }
        }
    }
}

?>

<!doctype html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Register | Shot Share
    </title>


    <link rel="stylesheet" href="../assets/css/style.css">


    <script src="../assets/js/main.js" defer></script>

</head>


<body class="auth-body">


    <div class="auth-page auth-page-register">


        <!-- Top bar -->

        <header class="auth-topbar">

            <a class="auth-brand" href="login.php">

                <span class="auth-brand-mark" aria-hidden="true"></span>

                <span>
                    Shot Share
                </span>

            </a>


            <button class="auth-theme-toggle" id="theme-toggle" type="button" aria-pressed="false" title="Switch theme">

                <span id="theme-toggle-icon" aria-hidden="true">
                    &#9680;
                </span>

                <span id="theme-toggle-label">
                    Dark Mode
                </span>

            </button>

        </header>


        <main class="auth-main">


            <!-- Left side -->

            <section class="auth-intro" aria-labelledby="register-intro-title">

                <p class="auth-eyebrow">
                    Join the community
                </p>


                <h1 id="register-intro-title">

                    Your next moment

                    <span>
                        starts here.
                    </span>

                </h1>


                <p class="auth-intro-text">

                    Create one account to publish posts,
                    share photos, and keep up with the
                    Shot Share community.

                </p>


                <div class="auth-benefits" aria-label="Shot Share features">


                    <div class="auth-benefit">

                        <span aria-hidden="true">
                            01
                        </span>

                        <p>

                            <strong>
                                Create your space
                            </strong>

                            <small>
                                Keep your posts connected
                                to your account.
                            </small>

                        </p>

                    </div>


                    <div class="auth-benefit">

                        <span aria-hidden="true">
                            02
                        </span>

                        <p>

                            <strong>
                                Share your view
                            </strong>

                            <small>
                                Publish text and photos
                                for others to see.
                            </small>

                        </p>

                    </div>


                    <div class="auth-benefit">

                        <span aria-hidden="true">
                            03
                        </span>

                        <p>

                            <strong>
                                Discover more
                            </strong>

                            <small>
                                Search and explore
                                community posts.
                            </small>

                        </p>

                    </div>


                </div>

            </section>


            <!-- Registration form -->

            <section class="auth-card auth-card-register" aria-labelledby="register-title">


                <div class="auth-card-header">

                    <span class="auth-card-mark" aria-hidden="true"></span>


                    <p class="auth-eyebrow">
                        New account
                    </p>


                    <h2 id="register-title">
                        Create your account
                    </h2>


                    <p>
                        Use your details below to get started.
                    </p>

                </div>


                <!-- Errors -->

                <?php if (!empty($errors)): ?>

                    <div class="errors auth-alert" role="alert">

                        <p>
                            Please check the following:
                        </p>


                        <ul>

                            <?php foreach ($errors as $error): ?>

                                <li>

                                    <?= htmlspecialchars(
                                        $error,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>

                                </li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                <?php endif; ?>


                <!-- Form -->

                <form class="auth-form" method="POST" action="">


                    <!-- Full name -->

                    <div class="auth-field">

                        <label for="full_name">
                            Full name
                        </label>


                        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars(
                            $full_name,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>" placeholder="Your full name" minlength="3" maxlength="100" autocomplete="name" required>

                    </div>


                    <!-- Username -->

                    <div class="auth-field">

                        <label for="username">
                            Username
                        </label>


                        <input type="text" id="username" name="username" value="<?= htmlspecialchars(
                            $username,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>" placeholder="Choose a username" minlength="3" maxlength="50" autocomplete="username"
                            required>


                        <p class="auth-help">

                            Letters, numbers, and underscores only.

                        </p>

                    </div>


                    <!-- Email -->

                    <div class="auth-field">

                        <label for="email">
                            Email address
                        </label>


                        <input type="email" id="email" name="email" value="<?= htmlspecialchars(
                            $email,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>" placeholder="name@example.com" autocomplete="email" required>

                    </div>


                    <!-- Password -->

                    <div class="auth-field">

                        <label for="password">
                            Password
                        </label>


                        <input type="password" id="password" name="password" placeholder="Create a strong password"
                            minlength="8" maxlength="20" autocomplete="new-password"
                            aria-describedby="password-requirements" required>


                        <p class="auth-help" id="password-requirements">

                            8-20 characters with uppercase,
                            lowercase, number, and special character.

                        </p>

                    </div>


                    <!-- Confirm password -->

                    <div class="auth-field">

                        <label for="confirm_password">
                            Confirm password
                        </label>


                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="Enter the password again" minlength="8" maxlength="20"
                            autocomplete="new-password" required>

                    </div>


                    <!-- Submit -->

                    <button class="auth-submit" type="submit" name="register">

                        Create Account

                    </button>


                </form>


                <p class="auth-switch">

                    Already have an account?

                    <a href="login.php">
                        Sign in
                    </a>

                </p>


            </section>


        </main>


    </div>


</body>

</html>