<?php

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/../database/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}


$user_id = $_SESSION['user_id'];
$errors = [];

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

$full_name = $user['full_name'];
$username = $user['username'];
$email = $user['email'];
$password = $user['password'];

$pstracture = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#$!%*_?&])[A-Za-z\d@#$!%*_?&]{8,20}$/";
//$estracture = "/^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z]{2,}$/";
$username_structure = "/^[a-zA-Z0-9_]+$/";

if (isset($_POST['save_changes'])) {

    $new_name = trim($_POST['new_name']);
    $new_username = trim($_POST['new_username']);
    //$new_email = trim($_POST['new_email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_name)) {
        $new_name = $full_name;
    }

    if (empty($new_username)) {
        $new_username = $username;
    }

    //if (empty($new_email)) {
   //     $new_email = $email;
    //}

    // Validate full name
    if (empty($new_name)) {
        $errors[] = "Full name is required.";
    } else if (strlen($new_name) < 3 || strlen($new_name) > 100) {
        $errors[] = "Full name must be between 3 and 100 characters long.";
    }

    // Validate username
    if (empty($new_username)) {
        $errors[] = "Username is required.";
    } else if (
        strlen($new_username) < 3 ||
        strlen($new_username) > 50
    ) {
        $errors[] = "Username must be between 3 and 50 characters long.";
    } else if (!preg_match($username_structure, $new_username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    } else {

        // Check if username already exists for other users
        $check_username = $conn->prepare(
            "SELECT * FROM users
             WHERE username = ?
             AND user_id != ?"
        );

        $check_username->execute([
            $new_username,
            $user_id
        ]);

        if ($check_username->rowCount() > 0) {
            $errors[] = "Username already exists.";
        }
    }

    // Validate email
    //if (!empty($new_email)) {

       // if (!preg_match($estracture, $new_email)) {
         //   $errors[] = "Invalid email format.";
       // } else {

            // Check if email already exists for other users
         //   $check_email = $conn->prepare(
          //      "SELECT * FROM users
           //      WHERE email = ?
          //       AND user_id != ?"
          //  );

          //  $check_email->execute([
           //     $new_email,
           //     $user_id
          //  ]);

          //  if ($check_email->rowCount() > 0) {
           //     $errors[] = "Email already exists.";
          //  }
       // }
   // }

    if (empty($current_password)) {
        $errors[] = "Current password is required.";
    } elseif (!password_verify($current_password, $user['password'])) {
        $errors[] = "Current password is incorrect.";
    }


    if (!empty($new_password) || !empty($confirm_password)) {

        if (empty($new_password)) {
            $errors[] = "New password is required.";
        } elseif (!preg_match($pstracture, $new_password)) {
            $errors[] = "New password does not meet the requirements.";
        } elseif (empty($confirm_password)) {
            $errors[] = "Please confirm the new password.";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
    }

    // If no errors, update the user's information
    if (empty($errors)) {

        $update_query =
            "UPDATE users
             SET full_name = ?,
                 username = ?";

        $new_profile_data = [
            $new_name,
            $new_username
        ];

        if (!empty($new_password)) {

            $hashed_password = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );

            $update_query .= ", password = ?";
            $new_profile_data[] = $hashed_password;
        }

        $update_query .= " WHERE user_id = ?";
        $new_profile_data[] = $user_id;

        $stmt = $conn->prepare($update_query);

        if ($stmt->execute($new_profile_data)) {

            $_SESSION['full_name'] = $new_name;
            $_SESSION['username'] = $new_username;

        } else {

            $errors[] = "Failed to update profile. Please try again.";
        }
    }
}

?>

<section class="profile-edit">

    <div class="edit">

        <h1>Edit Profile</h1>

        <form method="POST" action="">

            <p>
                Full Name:
                <?php echo $full_name; ?>
            </p>

            <label for="new_name">
                New Name:
            </label>

            <input
                type="text"
                id="new_name"
                name="new_name"
                value=""
            >

            <br><br>


            <p>
                Username:
                <?php echo $username; ?>
            </p>

            <label for="new_username">
                New Username:
            </label>

            <input
                type="text"
                id="new_username"
                name="new_username"
                value=""
            >

            <br><br>


            <p>
                Email:
                <?php echo $email; ?>
            </p>


            <br><br>


            <label for="current_password">
                Current Password:
            </label>

            <input
                type="password"
                id="current_password"
                name="current_password"
                required
            >

            <br><br>


            <label for="new_password">
                New Password:
            </label>

            <input
                type="password"
                id="new_password"
                name="new_password"
            >

            <br><br>


            <label for="confirm_password">
                Confirm Password:
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
            >

            <br><br>


            <button type="submit" name="save_changes">
                Save Changes
            </button>

        </form>

        <?php

        if (!empty($errors)) {

            foreach ($errors as $error) {

                echo "<p style=\"color: red;\">"
                    . htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        'UTF-8'
                    )
                    . "</p>";
            }
        }

        ?>

    </div>

</section>