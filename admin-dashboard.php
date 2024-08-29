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
$countQuery1 = mysqli_query($conn, "SELECT COUNT(*) AS totalStudents FROM `student` WHERE status = 'verified';") or die('count query failed');
$countResult1 = $countQuery1->fetch_assoc();
$totalStudents = $countResult1['totalStudents'];
$countQuery2 = mysqli_query($conn, "SELECT COUNT(*) AS totalTeachers FROM `faculty` WHERE status = 'verified';") or die('count query failed');
$countResult2 = $countQuery2->fetch_assoc();
$totalTeachers = $countResult2['totalTeachers'];
$countQuery3 = mysqli_query($conn, "SELECT COUNT(*) AS totalSubject FROM `subject`") or die('count query failed');
$countResult3 = $countQuery3->fetch_assoc();
$totalSubject = $countResult3['totalSubject'];
$countQuery4 = mysqli_query($conn, "SELECT
                                (SELECT COUNT(*) FROM `student` WHERE status = 'pending') +
                                (SELECT COUNT(*) FROM `faculty` WHERE status = 'pending') AS totalRows;
                                ");
$countResult4 = $countQuery4->fetch_assoc();
$totalnew = $countResult4['totalRows'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Pannel</title>
    <link rel="stylesheet" href="css/admin.css">
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
                    <a href="a-faculty.php"><span><i class="las la-user-tie"></i></span>Faculty</a>
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
                Dashboard
            </div>
            <div class="user-wrapper">
                <img src="images/default-avatar.png" width="40px" alt="" height="40px">
                <div>
                    <h4> Admin </h4>
                </div>
            </div>
        </header>

        <main>
            <div class="cards">
                <div class="card-single">
                    <div>
                        <h1><?php echo $totalStudents; ?></h1>
                        <span>Students</span>
                    </div>
                    <div><span><i class="las la-graduation-cap"></i></span></div>
                </div>
                <div class="card-single">
                    <div>
                        <h1><?php echo $totalTeachers; ?></h1>
                        <span>Teachers</span>
                    </div>
                    <div><span><i class="las la-user-tie"></i></span></div>
                </div>
                <div class="card-single">
                    <div>
                        <h1><?php echo $totalSubject; ?></h1>
                        <span>Subjects</span>
                    </div>
                    <div><span><i class="las la-stream"></i></span></div>
                </div>
            </div>
            <div class="recent-grid">
                <div class="new-regester">
                    <div class="card">
                        <div class="card-header">
                            <h3>New registrations</h3> <h4>(Total Unverified users = <?php echo $totalnew; ?>)</h4>
                            <a href="a-regestrations.php"><button> See All </button></a>
                        </div>
                        <div class="card-body">
                            <table>
                                <?php
                                $addmissions = mysqli_query($conn, "SELECT name, prn, status FROM (
                                    SELECT name, prn, status FROM `student` WHERE status = 'pending'
                                    UNION
                                    SELECT name, prn, status FROM `faculty` WHERE status = 'pending'
                                ) AS combined_result
                                LIMIT 10;                                
                                ") or die('query failed');
                                ?>
                                <thead>
                                    <tr>
                                        <td>Student/faculty Name</td>
                                        <td>PRN/ID</td>
                                        <td>Enrollment Status</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while($fetch=$addmissions->fetch_assoc())
                                    {
                                    ?>
                                        <tr>
                                            <td><?php echo $fetch['name']; ?></td>
                                            <td><?php echo $fetch['prn']; ?></td>
                                            <td><?php echo $fetch['status']; ?></td>
                                        </tr>
                                    
                                    <?php
                                     } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="faculty">
                    <div class="card">
                        <div class="card-header">
                            <h3>Teachers</h3>
                            <a href="a-faculty.php"><button> See All </button></a>
                        </div>
                        <?php
                            $teachers = mysqli_query($conn, "SELECT * FROM `faculty` WHERE status = 'verified' LIMIT 10;") or die('query failed');
                        ?>
                        <div class="card-body">
                        <?php
                            while($fetch=$teachers->fetch_assoc())
                                {
                            ?>

                            <div class="teacher">
                                <div class="info">
                                <?php
                                if($fetch['image'] == ''){
                                    echo '<img src="images/default-avatar.png" width="40px" alt="" height="40px">';
                                }else{
                                    echo '<img src="uploaded_img/'.$fetch['image'].'" width="40px" alt="" height="40px">';
                                }
                                ?>
                                    <div><h4><?php echo $fetch['name']; ?></h4>
                                    </div>
                                </div>
                                <div class="contact"><span><i class="las la-phone"></i></span>
                                    <span><i class="las la-envelope"></i></span>
                                </div>
                            </div>
                            <?php
                            } ?>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
    
</body>
</html>