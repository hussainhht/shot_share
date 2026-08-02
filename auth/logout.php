<?php
session_start();
require_once "../database/db_connect.php";
session_unset();
session_destroy();
header("Location: login.php");
exit();






?>

<!doctype html>
<html>
  <head>
    <title>Logout</title>
  </head>

  <body></body>
</html>
