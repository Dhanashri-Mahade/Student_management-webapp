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

// Fetch student attendance from attendance table for the specific department and semester
$attendanceQuery = "SELECT s.name AS subject_name, sm.semester, COUNT(a.attendance_id) AS total_lectures, 
                    SUM(CASE WHEN a.status = 'P' THEN 1 ELSE 0 END) AS present_lectures,
                    (SUM(CASE WHEN a.status = 'P' THEN 1 ELSE 0 END) / COUNT(a.attendance_id)) * 100 AS percentage
                    FROM attendance a
                    INNER JOIN subject_mappings sm ON a.subject_code = sm.subject_code
                    INNER JOIN subject s ON a.subject_code = s.subject_code
                    WHERE a.student_prn = '$user_id'
                    AND sm.department = (SELECT department FROM student WHERE prn = '$user_id')
                    AND sm.semester = (SELECT semester FROM student WHERE prn = '$user_id')
                    GROUP BY a.subject_code
                    ORDER BY a.subject_code ASC" ;
                    
$attendanceResult = mysqli_query($conn, $attendanceQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
    <link rel="stylesheet" href="css/s-result.css">
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
                    <a href="s-viewatd.php" class="active"><span><i class="las la-list-alt"></i></i></span>View Attendance</a>
                </li>
                <li>
                    <a href="s-update.php"><span><i class="las la-user-edit"></i></span>Update Profile</a>
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
                View Attendance
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
        <div class="content print-container">
            <h3 class="print-only"><?php echo $fetch['name']; ?></h3>
            <h3 class="print-only">PRN: <?php echo $fetch['prn']; ?></h3>
            <table>
                <thead>
                    <tr>
                        <td>Subject Name</td>
                        <td>Total Lecturs</td>
                        <td>Present Lectures</td>
                        <td>Persentage</td>
                    </tr>
                </thead>
                <tbody>
                <?php
                while ($row = mysqli_fetch_assoc($attendanceResult)) {
                    echo "<tr>";
                    echo "<td>" . $row['subject_name'] . "</td>";
                    echo "<td>" . $row['total_lectures'] . "</td>";
                    echo "<td>" . $row['present_lectures'] . "</td>";
                    echo "<td>" . round($row['percentage'], 2) . "%</td>";
                    echo "</tr>";
                }
             ?>
                </tbody>
            </table>
        </div>
        <button class="print-btn" onclick="window.print();">Print Attendance</button>
    </main>
    </div>
    


</body>
</html>