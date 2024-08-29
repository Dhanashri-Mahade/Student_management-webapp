<?php

include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
   exit(); // Ensure to exit after redirect
}
if (isset($_GET['logout'])) {
   // Clear all session variables
   session_unset();
   // Destroy the session
   session_destroy();
   // Redirect to the login page
   header('location:index.php');
   exit();
}

if (isset($_POST['update_profile'])) {

    $update_name = mysqli_real_escape_string($conn, $_POST['update_name']);
    $update_email = mysqli_real_escape_string($conn, $_POST['update_email']);
    $update_mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    mysqli_query($conn, "UPDATE `student` SET name = '$update_name', email = '$update_email', mobile = '$update_mobile' WHERE prn = '$user_id'") or die('query failed');

    $new_pass = mysqli_real_escape_string($conn, ($_POST['new_pass']));
    $confirm_pass = mysqli_real_escape_string($conn, ($_POST['confirm_pass']));

    if (!empty($new_pass) || !empty($confirm_pass)) {
        if ($new_pass != $confirm_pass) {
            $message = 'Confirm password not matched!';
        } else {
            mysqli_query($conn, "UPDATE `student` SET password = '$confirm_pass' WHERE prn = '$user_id'") or die('query failed');
            $message = 'Password updated successfully!';
        }
    }
    $update_image = $_FILES['update_image']['name'];
    $update_image_size = $_FILES['update_image']['size'];
    $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
    $update_image_folder = 'uploaded_img/' . $update_image;

    if (!empty($update_image)) {
        if ($update_image_size > 150000) {
            $message = 'Image is too large';
        } else {
            // selecting old image for deleting
            $select_img_query = mysqli_query($conn, "SELECT image FROM `student` WHERE prn = '$user_id'") or die('query failed');
            $selected_img = mysqli_fetch_assoc($select_img_query);

            if (!empty($selected_img['image']) && file_exists("uploaded_img/" . $selected_img['image'])) {
            // deleting old image
            unlink("uploaded_img/" . $selected_img['image']);
            }

            $image_update_query = mysqli_query($conn, "UPDATE `student` SET image = '$update_image' WHERE prn = '$user_id'") or die('query failed');
            if ($image_update_query) {
                move_uploaded_file($update_image_tmp_name, $update_image_folder);
            }
            $message = 'Image updated successfully!';
        }
    }
        
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="css/s-update.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">

    <script>
            document.addEventListener('DOMContentLoaded', function () {
                var toggleSidebar = document.getElementById('toggle-sidebar');
                var body = document.body;
    
                toggleSidebar.addEventListener('click', function () {
                    body.classList.toggle('sidebar-closed');
                });
            });
        </script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-title">
            <h1><span><i class="las la-university"></i></span>DBATU</h1>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li>
                    <a href="s-dash.php" ><span><i class="las la-chalkboard"></i></span>Dashboard</a>
                </li>
                <li>
                    <a href="s-subject.php"><span><i class="las la-stream"></i></span>Subjects</a>
                </li>
                <li>
                    <a href="s-result.php"><span><i class="las la-clipboard-list"></i></span>Result</a>
                </li>
                <li>
                    <a href="s-viewatd.php"><span><i class="las la-list-alt"></i></i></span>View Attendance</a>
                </li>
                <li>
                    <a href="s-update.php" class="active"><span><i class="las la-user-edit"></i></span>Update Profile</a>
                </li>
                <li>
                    <a href="index.php"><span><i class="las la-sign-out-alt"></i></span>Logout</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <header>
            <?php
                $select = mysqli_query($conn, "SELECT * FROM `student` WHERE prn = '$user_id'") or die('query failed');
                if(mysqli_num_rows($select) > 0){
                $fetch = mysqli_fetch_assoc($select);
                }
            ?>
            <div class="header-title">
                <h2>
                <span id="toggle-sidebar"><i class="las la-bars"></i></span>
                </h2>
                Update Profile
            </div>
            <div class="user-wrapper">
            <?php
                  if($fetch['image'] == ''){
                     echo '<img src="images/default-avatar.png" width="40px" alt="" height="40px">';
                 }else{
                     echo '<img src="uploaded_img/'.$fetch['image'].'" width="40px" alt="" height="40px">';
                 }
                ?>
                <div>
                    <h4><?php echo $fetch['name']; ?></h4>
                    <small><?php echo $fetch['prn']; ?></small>
                </div>
            </div>
        </header>

        <main>
    <div class="content">
            <div class="update-profile">

            <?php
                $select = mysqli_query($conn, "SELECT * FROM `student` WHERE prn = '$user_id'") or die('query failed');
                if(mysqli_num_rows($select) > 0){
                $fetch = mysqli_fetch_assoc($select);
                }
            ?>

            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    if($fetch['image'] == ''){
                        echo '<img src="images/default-avatar.png">';
                    }else{
                        echo '<img src="uploaded_img/'.$fetch['image'].'">';
                    }
                    if (isset($message)) {
                        echo '<div class="toast-container" id="toastContainer"></div>';
                        echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast('$message'); });</script>";
                    }
                ?>

                <h2><?php echo $fetch['prn']; ?></h2>
                <div class="flex">
                    <div class="inputBox">
                        <span>username :</span>
                        <input type="text" name="update_name" value="<?php echo $fetch['name']; ?>" class="box">
                        <span>your email :</span>
                        <input type="email" name="update_email" value="<?php echo $fetch['email']; ?>" class="box">
                        <span>update your pic :</span>
                        <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png" class="box">
                    </div>
                    <div class="inputBox">
                        <span>Mobile no :</span>
                        <input type="text" name="mobile" value="<?php echo $fetch['mobile']; ?>" class="box">
                        <span>new password :</span>
                        <input type="password" name="new_pass" placeholder="enter new password" class="box">
                        <span>confirm password :</span>
                        <input type="password" name="confirm_pass" placeholder="confirm new password" class="box">
                    </div>
                </div>
                <input type="submit" value="update profile" name="update_profile" class="btn">
            </form>

            </div>
        </div>
    </main>
    </div>   

    <script src="tost.js"></script>
</body>
</html>