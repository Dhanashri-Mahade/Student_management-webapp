<?php
include 'config.php';
session_start();

if (isset($_POST['submit'])) {
    $prn = $_POST["prn"];
    $pass = $_POST["password"];

    // Check if it's the admin
    if ($prn == "admin" && $pass == "987654321") {
        $_SESSION['user_id'] = "admin";
        $_SESSION['user_type'] = "admin";
        header('location:admin-dashboard.php');
        exit();
    }

    // Check if it's a student
    $selectStudent = mysqli_query($conn, "SELECT * FROM `student` WHERE prn = '$prn' AND password = '$pass'") or die('query failed');
    if (mysqli_num_rows($selectStudent) > 0) {
        $row = mysqli_fetch_assoc($selectStudent);
        $_SESSION['user_id'] = $row['prn'];
        $_SESSION['user_type'] = "student";
        header('location:s-dash.php');
        exit();
    }

    // Check if it's a faculty
    $selectFaculty = mysqli_query($conn, "SELECT * FROM `faculty` WHERE prn = '$prn' AND password = '$pass'") or die('query failed');
    if (mysqli_num_rows($selectFaculty) > 0) {
        $row = mysqli_fetch_assoc($selectFaculty);
        $_SESSION['user_id'] = $row['prn'];
        $_SESSION['user_type'] = "faculty";
        header('location:f-dash.php');
        exit();
    }

    // If none of the above conditions are met, it's an incorrect login
    $message = 'Incorrect ID or password!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>login</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/register.css">

</head>
<body>
   
<div class="form-container">

   <form action="" method="post" enctype="multipart/form-data">
      <h3>login now</h3>
      <?php
         if (isset($message)) {
            echo '<div class="toast-container" id="toastContainer"></div>';
            echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast('$message'); });</script>";
        }
      ?>
      <input type="text" name="prn" placeholder="enter usrrname/prn" class="box" required>
      <input type="password" name="password" placeholder="enter password" class="box" required>
      <input type="submit" name="submit" value="login now" class="btn">
      <p>don't have an account? <a href="register.php">regiser now</a></p>
   </form>

</div>
<script src="tost.js"></script>
</body>
</html>