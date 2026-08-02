<?php
session_start();
require_once "../database/db_connect.php";
if(isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password =($_POST['password']);
    $errors = [];

     $estracture="/^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z]{2,}$/";
      $pstracture = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#$!%*_?&])[A-Za-z\d@#$!%*_?&]{8,20}$/";
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } 
    else if (!preg_match($estracture, $email)) {
        $errors[] = "Invalid email format.";
    }

    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required.";
    } 
    else if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    //password pattern
    else if (!preg_match($pstracture, $password)) {
        $errors[] = "Password must be  8-20 characters long  with no spaces and contain at least one uppercase letter, one lowercase letter, one number, and one special character(@#$!%*_?&).";
    }

    // check email and password matching
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            //successful login 
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            header("Location: ../index.php");
            exit();
           
        } 
        else {
            $errors[] = "Invalid email or password.";
        }
    
}
}





?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    </head>


    <body>
        <div class="first">
      <form method="POST" action="">
        <label for="email">Email: </label>
        <input type="email" id="email" name="email" required />
        <br />
        <label for="password">Password: </label>
        <input type="password" id="password" name="password" required />
        <br />
    
      <br />
        <button type="submit" id="login" name="login">Login</button>
      </form>


      <p>
        Don't have an account? <a href="register.php">Register here</a>
      </p>

      <?php
                if (!empty($errors))  {

                foreach ($errors as $error) {
                    echo "<p style='color: red;'>$error</p>";
                }
            }
            ?>


      </div>
    </body>




</html>