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


//disply all faculties
$addmissions = mysqli_query($conn, "SELECT * FROM `faculty` WHERE status = 'verified' 
                                       ORDER BY `name` ASC") or die('query failed');


// fuction for deleting the user
if (isset($_GET['delete_prn'])) {
    $delete_prn = $_GET['delete_prn'];

    // Check if there are associated subjects
    $check_subjects_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM `subject` WHERE `teacher_prn` = '$delete_prn'");
    $subject_count = mysqli_fetch_assoc($check_subjects_query)['count'];

    if ($subject_count > 0) {
        // Subjects are associated; set a message
        $message = "Cannot delete the faculty member because they are associated with some subjects.";
    } else {

        // No associated subjects; proceed with deletion

        // selecting old image for deleting
    $select_img_query = mysqli_query($conn, "SELECT image FROM `faculty` WHERE prn = '$delete_prn'") or die('query failed');
    $selected_img = mysqli_fetch_assoc($select_img_query);

    if (!empty($selected_img['image']) && file_exists("uploaded_img/" . $selected_img['image'])) {
    // deleting old image
    unlink("uploaded_img/" . $selected_img['image']);
    }

    // Perform the deletion of the student's data
    $deleteStudent = $conn->prepare("DELETE FROM faculty WHERE prn = ?");
    $deleteStudent->bind_param("s", $delete_prn);

        if ($deleteStudent->execute()) {
            // Successful deletion
            $message = "Faculty member deleted successfully.";
            header('location: a-faculty.php');
            exit();
        } else {
            // Error in deletion
            $message = "Error deleting faculty member: " . $deleteStudent->error;
        }

        $deleteStudent->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Information</title>
    <link rel="stylesheet" href="css/a-faculty.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
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
                    <a href="admin-dashboard.php" ><span><i class="las la-chalkboard"></i></span>Dashboard</a>
                </li>
                <li>
                    <a href="a-faculty.php" class="active"><span><i class="las la-user-tie"></i></span>Faculty</a>
                </li>
                <li>
                    <a href="a-students.php"><span><i class="las la-graduation-cap"></i></span>Students</a>
                </li>
                <li>
                    <a href="a-subjects.php"><span><i class="las la-stream"></i></span>Subjects</a>
                </li>
                <li>
                    <a href="a-department.php"><span><i class="las la-school"></i></span>departments</a>
                </li>
                <li>
                    <a href="a-regestrations.php"><span><i class="las la-address-book"></i></span>New Students</a>
                </li>
                <li>
                    <a href="a-newtecher.php"><span><i class="las la-chalkboard-teacher"></i></span>New Teachers</a>
                </li>
                <li>
                    <a href="s-dash.php?logout=<?php echo $user_id; ?>"><span><i class="las la-sign-out-alt"></i></span>Logout</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <header>
            <div class="header-title">
                <h2>
                <span id="toggle-sidebar"><i class="las la-bars"></i></span>
                </h2>
                Faculty Information
            </div>
            <div class="user-wrapper">
                <img src="images/default-avatar.png" width="40px" alt="" height="40px">
                <div>
                    <h4> Admin </h4>
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
            <div class="card">
                <div class="card-header">
                    <h3>Faculty</h3>
                </div>
                    <div class="card-body">
                            <table id="example" width="100%">
                                <thead>
                                    <tr>
                                        <td>Photo</td>
                                        <td>Name</td>
                                        <td>ID</td>
                                        <td>Department</td>
                                        <td>Email</td>
                                        <td>Mobile No</td>
                                        <td>Delete</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while($fetch=$addmissions->fetch_assoc())
                                    {
                                    ?>
                                        <tr>
                                        <?php
                                        if($fetch['image'] == ''){
                                            echo '<td><img src="images/default-avatar.png" width="60px" alt="" height="60px"></td>';
                                        }else{
                                            echo '<td><img src="uploaded_img/'.$fetch['image'].'" width="60px" alt="" height="60px"</td>';
                                        }
                                        ?>
                                            <td><?php echo $fetch['name']; ?></td>
                                            <td><?php echo $fetch['prn']; ?></td>
                                            <td><?php echo $fetch['department']; ?></td>
                                            <td><?php echo $fetch['email']; ?></td>
                                            <td><?php echo $fetch['mobile']; ?></td>
                                            <td>
                                                    <a href='a-faculty.php?delete_prn=<?php echo $fetch['prn']; ?>' onclick="return confirm('Are you sure you want to delete this faculty?');">
                                                    <i class="las la-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    
                                    <?php
                                     } ?>
                                </tbody>
                            </table>
                    </div>
            </div>
        </main>
    </div> 

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        new DataTable('#example');
    </script>
    <script src="tost.js"></script>
</body>
</html>