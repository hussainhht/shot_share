<?php
session_start();
require_once "../database/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}


$user_id = $_SESSION['user_id'];
$errors = [];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

$full_name = $user['full_name'];
$email = $user['email'];
$password = $user['password'];
$pstracture = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#$!%*_?&])[A-Za-z\d@#$!%*_?&]{8,20}$/";
$estracture="/^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z]{2,}$/";
if(isset($_POST['save_changes'])) {
    $new_name = trim($_POST['new_name']);
    $new_email = trim($_POST['new_email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_name)) {
    $new_name = $full_name;
}

if (empty($new_email)) {
    $new_email = $email;
}
    // Validate full name
    if (empty($new_name)) {
        $errors[] = "Full name is required.";
    } else if(strlen($new_name) < 3 || strlen($new_name) > 100){
        $errors[] = "Full name must be between 3 and 100 characters long.";
    }

    // Validate email
   if(!empty($new_email)){
    if (!preg_match($estracture, $new_email)) {
        $errors[] = "Invalid email format.";
    } else {
        // Check if email already exists for other users
        $check_email = $conn->prepare("SELECT * FROM users WHERE email = ? AND id != ?");
        $check_email->execute([$new_email, $user_id]);
        if ($check_email->rowCount() > 0) {
            $errors[] = "Email already exists.";
        }
    }
   }
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
        $update_query = "UPDATE users SET full_name = ?, email = ?";
        $new_profile_data = [$new_name, $new_email];

        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query .= ", password = ?";
            $new_profile_data[] = $hashed_password;
        }

        $update_query .= " WHERE id = ?";
        $new_profile_data[] = $user_id;

        $stmt = $conn->prepare($update_query);
        if ($stmt->execute($new_profile_data)) {
            $_SESSION['full_name'] = $new_name;
            $_SESSION['email'] = $new_email;
            header("Location: ../auth/login.php");
            exit();
        } else {
            $errors[] = "Failed to update profile. Please try again.";
        }
    }
}


?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Profile</title>
</head>


<body>

<div class="edit">
    <h1>Edit Profile</h1>
    <form method="POST" action="">
       
            <p>Full Name: <?php echo $full_name; ?></p>
        <label for="new_name">New Name:</label>
        <input type="text" id="new_name" name="new_name" value="" ><br><br>
        <p>Email: <?php echo $email; ?></p>
        <label for="new_email">New Email:</label>
        <input type="email" id="new_email" name="new_email" value="" ><br><br>
        <label for="current_password">Current Password:</label>
        <input type="password" id="current_password" name="current_password" required><br><br>
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password"><br><br>
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" ><br><br>
        <button type="submit" name="save_changes">Save Changes</button>
    </form>
    <p style="color: red;">
            <?php
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo $error . "<br>";
                }
            }
            ?>
            </div>


</body>


</html>