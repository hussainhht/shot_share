<?php
//connect database
require_once "../database/db_connect.php";


if (isset($_POST['register'])) {
    //get variables 
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    //check full name
    if (empty($full_name)) {
    $errors[] = "Full name is required.";
    }

    //check email
    //email pattern
    $estracture="/^[a-zA-Z0-9]+@[a-zA-z0-9]+\.[a-zA-Z]{2,}$/";
    if (empty($email)) {
        $errors[] = "Email is required.";
   
    }
    else if (!preg_match($estracture, $email)) {
        $errors[] = "Invalid email format.";
    }

    //check password
    $empty=false;
    $long_enough = true;
    $inpattern = true;
    if (empty($password)) {
        $errors[] = "Password is required.";
        $empty = true;

    } 
    if (strlen($password) < 8 && !$empty) {
        $errors[] = "Password must be at least 8 characters long.";
        $long_enough = false;
    }
    //password pattern
    $pstracture = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#$!%*_?&])[A-Za-z\d@#$!%*_?&]{8,20}$/";
    if (!preg_match($pstracture, $password) && !$empty && $long_enough) {
        $errors[] = "Password must be  8-20 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
        $inpattern = false;
    }

    //check confirm password
    if (empty($confirm_password) && !$empty && $long_enough && $inpattern) {
        $errors[] = "Please confirm your password.";
    } 
    if ($password !== $confirm_password && !$empty && $long_enough && $inpattern) {
        $errors[] = "Passwords do not match.";
    }

}

?>


<!doctype html>
<html>
  <head>
    <title>Register</title>
  </head>

  <body>
    <div class="first">
      <form method="POST" action="">
        <label for="full_name">Full Name: </label>
        <input type="text" id="full_name" name="full_name" required />
        <br />
        <label for="email">Email: </label>
        <input type="email" id="email" name="email" required />
        <br />
        <label for="password">Password: </label>
        <input type="password" id="password" name="password" required />
        <br />
        <label for="confirm_password">Confirm Password: </label>
        <input
          type="password"
          id="confirm_password"
          name="confirm_password"
          required
        />
        <br />
        <button type="submit" id="register" name="register">Register</button>
      </form>
            <?php
                if (!empty($errors))  {

                foreach ($errors as $error) {
                   echo "<p style='color:red;'>$error</p>";
                                            }

                                      }
            ?>

      <br />
      <p id="last">Already have an account? <a href="login.html">Log in</a></p>
    </div>
  </body>
</html>
