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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    // Get the selected semester value from the form
    $selectedSemester = $_POST['semester'];

    // Update the user's semester in the database
    $updateQuery = "UPDATE student SET semester = '$selectedSemester' WHERE prn = '$user_id'";
    $result = mysqli_query($conn, $updateQuery);

    if ($result) {
        // Update successful
        $message ="Semester Updated Successfully!";
        header('Location: s-dash.php');
        exit();
    } else {
        // Update failed
        $message= 'Error updating semester. Please try again.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/s-dash.css">
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
                    <a href="#" class="active"><span><i class="las la-chalkboard"></i></span>Dashboard</a>
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
                    <a href="s-update.php"><span><i class="las la-user-edit"></i></span>Update Profile</a>
                </li>
                <li>
                    <a href="s-dash.php?logout=<?php echo $user_id; ?>"><span><i class="las la-sign-out-alt"></i></span>Logout</a>
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
                Dashboard
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
        <?php
          if (isset($message)) {
            echo '<div class="toast-container" id="toastContainer"></div>';
            echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast('$message'); });</script>";
        }  
        ?>
        <div class="content">
            <div class="user-info">
                <h3>Name = <?php echo $fetch['name']; ?></h3>
                <h3>PRN no. = <?php echo $fetch['prn']; ?></h3>
                <h3>Department = <?php echo $fetch['department']; ?></h3>
                <h3>Email = <?php echo $fetch['email']; ?></h3>
                <h3>Mobile No. = <?php echo $fetch['mobile']; ?></h3>
                <?php
            // Check if the semester value is null
            if ($fetch['semester'] === null) {
                echo '<h3>Semester = Please select your semester:</h3>';
                echo '<form action="s-dash.php" method="post">';
                echo '<select name="semester" class="box" required>';
                echo '<option value="1">First Semester</option>';
                echo '<option value="2">Second Semester</option>';
                echo '<option value="3">Third Semester</option>';
                echo '<option value="4">Fourth Semester</option>';
                echo '<option value="5">Fifth Semester</option>';
                echo '<option value="6">Sixth Semester</option>';
                echo '<option value="7">Seventh Semester</option>';
                echo '<option value="8">Eighth Semester</option>';
                echo '</select>';
                echo '<input type="submit" value="Update Profile" name="update_profile" class="btn">';
                echo '</form>';
            } else {
                echo '<h3>Semester = ' . $fetch['semester'] . '</h3>';
            }
            ?>

            <br>

            <?php
            // Check verification status
                if ($fetch['status'] == "pending") {
                    echo '<h4>Your verification status is currently pending. Please wait for it to be verified.</h4>';
                }
            ?>
            </div>
        </div>
        </main>
    </div>
    <script src="tost.js"></script>
</body>
</html>