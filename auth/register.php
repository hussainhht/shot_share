<?php

// Connect to the database
require_once __DIR__ . "/../database/db_connect.php";

// Define variables before displaying the HTML form
// This prevents "undefined variable" warnings
$errors = [];
$full_name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get submitted values safely
    $full_name = trim($_POST["full_name"] ?? "");
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

    } elseif (strlen($full_name) < 3 || strlen($full_name) > 100) {
        $errors[] = "Full name must be between 3 and 100 characters long.";
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
    }

    /*
    |--------------------------------------------------------------------------
    | Check whether the email already exists
    |--------------------------------------------------------------------------
    |
    | This query only runs when the email is not empty and its format is valid.
    |
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

        $check_email->execute([$email]);

        $existing_user = $check_email->fetch(PDO::FETCH_ASSOC);

        if ($existing_user) {
            $errors[] = "Email already exists.";
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
        $errors[] = "Password is required.";

    } elseif (!preg_match($password_pattern, $password)) {
        $errors[] =
            "Password must be 8-20 characters long, contain no spaces, "
            . "and include at least one uppercase letter, one lowercase "
            . "letter, one number, and one special character (@#$!%*_?&).";
    }

    /*
    |--------------------------------------------------------------------------
    | Validate confirm password
    |--------------------------------------------------------------------------
    |
    | This validation is separate from the password validation.
    | Therefore, all relevant errors can be displayed together.
    |
    */

    if ($confirm_password === "") {
        $errors[] = "Please confirm your password.";

    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    /*
    |--------------------------------------------------------------------------
    | Insert user into the database
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        // Never store the original password in the database
        $hashed_password = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        try {
            $stmt = $conn->prepare(
                "INSERT INTO users (
                    full_name,
                    email,
                    password
                )
                VALUES (?, ?, ?)"
            );

            $stmt->execute([
                $full_name,
                $email,
                $hashed_password
            ]);

            header("Location: login.php?registered=1");
            exit;

        } catch (PDOException $e) {

            /*
             * SQLSTATE 23000 usually means a UNIQUE constraint
             * was violated, such as inserting an existing email.
             */
            if ($e->getCode() === "23000") {
                $errors[] = "Email already exists.";
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
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

    <title>Register | Shot Share</title>
</head>

<body>

<div class="first">

    <h1>Create Account</h1>

    <?php if (!empty($errors)): ?>

        <div class="errors">

            <?php foreach ($errors as $error): ?>

                <p style="color: red;">
                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </p>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <form method="POST" action="">

        <label for="full_name">
            Full Name:
        </label>

        <input
            type="text"
            id="full_name"
            name="full_name"
            value="<?= htmlspecialchars(
                $full_name,
                ENT_QUOTES,
                "UTF-8"
            ) ?>"
            minlength="3"
            maxlength="100"
            autocomplete="name"
            required
        >

        <br><br>

        <label for="email">
            Email:
        </label>

        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars(
                $email,
                ENT_QUOTES,
                "UTF-8"
            ) ?>"
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
            minlength="8"
            maxlength="20"
            autocomplete="new-password"
            required
        >

        <p>
            Password must contain 8-20 characters, one uppercase
            letter, one lowercase letter, one number, and one special
            character.
        </p>

        <label for="confirm_password">
            Confirm Password:
        </label>

        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            minlength="8"
            maxlength="20"
            autocomplete="new-password"
            required
        >

        <br><br>

        <button type="submit" name="register">
            Register
        </button>

    </form>

    <p id="last">
        Already have an account?
        <a href="login.php">Log in</a>
    </p>

</div>

</body>
</html>

<!--
===============================================================================
CHANGES MADE TO THIS FILE
===============================================================================

1. Defined $errors, $full_name, and $email before processing the form.
   Reason: Prevents undefined variable warnings when the page first opens.

2. Changed the form check to:
   $_SERVER["REQUEST_METHOD"] === "POST"
   Reason: Detects every POST submission, including submitting with Enter.

3. Added the null coalescing operator (??) when reading $_POST values.
   Reason: Prevents "Undefined array key" warnings when a value is missing.

4. Replaced the custom email regular expression with FILTER_VALIDATE_EMAIL.
   Reason: The original regex rejected valid emails containing dots,
   underscores, plus signs, or multiple domain sections.

5. Moved the duplicate-email query after basic email validation.
   Reason: There is no need to query the database when the email is empty
   or invalid.

6. Replaced rowCount() with fetch() when checking whether an email exists.
   Reason: PDO rowCount() is unreliable for SELECT queries with MySQL.

7. Selected only user_id instead of SELECT * when checking the email.
   Reason: Only the user's existence is needed, so retrieving every column
   is unnecessary.

8. Added LIMIT 1 to the email query.
   Reason: The query can stop after finding the first matching user.

9. Separated confirm-password validation from password validation.
   Reason: Both password and confirmation errors can now be detected and
   displayed during the same request.

10. Kept password_hash() before inserting the password.
    Reason: The original password must never be stored directly.

11. Added try/catch around the INSERT query.
    Reason: Handles database errors and duplicate-email constraints safely.

12. Preserved the user's full name and email after a validation error.
    Reason: The user does not need to type those fields again.

13. Did not preserve password values in the HTML.
    Reason: Password fields should be cleared after an unsuccessful request.

14. Escaped displayed values and error messages using htmlspecialchars().
    Reason: Prevents HTML or JavaScript injection when output is displayed.

15. Added lang, charset, viewport, autocomplete, minlength, and maxlength.
    Reason: Improves HTML structure, mobile support, usability, and browser
    validation.

16. Redirected successful registration to:
    login.php?registered=1
    Reason: The login page can display a successful-registration message.

IMPORTANT:
The database column is currently named "password". It stores a hashed password,
not the original password. If the database column is named "password_hash",
change "password" to "password_hash" in both register.php and login.php.
===============================================================================
-->