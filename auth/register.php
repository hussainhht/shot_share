<?php
//connect database
require_once "../database/db_connect.php";




if (isset($_POST['register'])) {
    //get variables 
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = ($_POST['password']);
    $confirm_password = ($_POST['confirm_password']);
    $errors = [];


    $check_email = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check_email->execute([$email]);


    //check full name
    if (empty($full_name)) {
    $errors[] = "Full name is required.";
    }
    else if(strlen($full_name) < 3 || strlen($full_name) > 100){
        $errors[] = "Full name must be between 3 and 100 characters long.";
    }

    //check email
    //email pattern
    $estracture="/^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z]{2,}$/";
    if (empty($email)) {
        $errors[] = "Email is required.";
   
    }
    else if (!preg_match($estracture, $email)) {
        $errors[] = "Invalid email format.";
    }
    //chek if email already exists
    
    
        else if ($check_email->rowCount() > 0) {
            $errors[] = "Email already exists.";
        }
    

    //check password
      $pstracture = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#$!%*_?&])[A-Za-z\d@#$!%*_?&]{8,20}$/";
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

    //check confirm password
    else if (empty($confirm_password)) {
        $errors[] = "Please confirm your password.";
    } 
    else if ($password !== $confirm_password ) {
        $errors[] = "Passwords do not match.";
    }
//----------------------------------end of validation----------------------------------


    //if no errors, insert into database
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$full_name, $email, $hashed_password]);
        header("Location: login.php");
        exit();
    }

}//end of register button click

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
