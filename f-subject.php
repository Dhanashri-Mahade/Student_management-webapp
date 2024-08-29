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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects</title>
    <link rel="stylesheet" href="css/s-subjects.css">
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
                    <a href="f-dash.php" ><span><i class="las la-chalkboard"></i></span>Dashboard</a>
                </li>
                <li>
                    <a href="f-subject.php" class="active"><span><i class="las la-stream"></i></span>Subjects</a>
                </li>
                <li>
                    <a href="f-add-marks.php" ><span><i class="las la-edit"></i></span>Add Marks</a>
                </li>
                <li>
                    <a href="f-view-marks.php"><span><i class="las la-clipboard-list"></i></span>View Marks</a>
                </li>
                <li>
                    <a href="f-addatd.php"><span><i class="las la-user-edit"></i></span>Add Attendance</a>
                </li>
                <li>
                    <a href="f-viewatd.php"><span><i class="las la-clipboard-list"></i></span>View Attendance</a>
                </li>
                <li>
                    <a href="f-update.php"><span><i class="las la-user-edit"></i></span>Update Profile</a>
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
                $select = mysqli_query($conn, "SELECT * FROM `faculty` WHERE prn = '$user_id'") or die('query failed');
                if(mysqli_num_rows($select) > 0){
                $fetch = mysqli_fetch_assoc($select);
                }
            ?>
            <div class="header-title">
                <h2>
                    <span id="toggle-sidebar"><i class="las la-bars"></i></span>
                </h2>
                Subjects
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
    <?php 
    if (isset($message)) {
        echo '<div class="toast-container" id="toastContainer"></div>';
        echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast('$message'); });</script>";
    }
    ?>

    <div class="sub-display">
        <h3>Your Subjects</h3>
        <form action="" method="post">
            <table>
            <?php
            $mysub = mysqli_query($conn, "SELECT * FROM `subject` WHERE teacher_prn = '$user_id' ") or die('query failed');
            ?>
            <thead>
                <tr>
                    <td>Subject Name</td>
                    <td>Subject Code</td>
                    <td>Subject Type</td>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($fetch = $mysub->fetch_assoc()) {
                    ?>
                    <tr>
                        <td><?php echo $fetch['name']; ?></td>
                        <td><?php echo $fetch['subject_code']; ?></td>
                        <td><?php echo $fetch['type']; ?></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
            </table>
        </form>
        
    </div>
</div>
</main>
    </div>
    <script src="tost.js"></script>
</body>
</html>