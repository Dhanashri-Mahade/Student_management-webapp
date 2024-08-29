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
// Fetch subjects outside the form submission condition
$facultySubjects = mysqli_query($conn, "SELECT * FROM subject WHERE teacher_prn = '$user_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Marks</title>
    <link rel="stylesheet" href="css/s-result.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
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
                    <a href="f-subject.php"><span><i class="las la-stream"></i></span>Subjects</a>
                </li>
                <li>
                    <a href="f-add-marks.php" ><span><i class="las la-edit"></i></span>Add Marks</a>
                </li>
                <li>
                    <a href="f-view-marks.php" class="active"><span><i class="las la-clipboard-list"></i></span>View Marks</a>
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
                $dep = $fetch['department'];
                $hodPrn = mysqli_fetch_assoc(mysqli_query($conn, "SELECT hod FROM `department` WHERE de_name = '$dep'"))['hod'];
                $hod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM `faculty` WHERE prn = '$hodPrn'"))['name'] ?? 'HOD not selected';
                }
            ?>
            <div class="header-title">
                <h2>
                    <span id="toggle-sidebar"><i class="las la-bars"></i></span>
                </h2>
                View Marks
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
                <h3>Select Subject</h3>
                <form class="select-sub" action="" method="post" enctype="multipart/form-data">
                    <select name="subject_code" class="box" required>
                    <?php
                    // Fetch the subjects assigned to the logged-in faculty
                    while ($subject = mysqli_fetch_assoc($facultySubjects)) {
                        echo '<option value="' . $subject['subject_code'] . '">' . $subject['name'] . '</option>';
                    }
                    ?>
                    </select>
                    <input type="submit" value="Select Subject" name="select_sub" class="box">
                </form>
            <!-- Display student list only if the "Select Subject" button is clicked and subject_code is set -->
                <?php if (isset($_POST['select_sub']) && isset($_POST['subject_code'])) : ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="subject_code" value="<?php echo $_POST['subject_code']; ?>">

                    <div class="print-container">
                        <!-- Display subject name at the top -->
                        <h4 class="print-only">Internal Marks of</h4>
                        <?php
                            // Fetch the subject name based on the subject code
                            $subjectCode = $_POST['subject_code'];
                            $subjectQuery = mysqli_query($conn, "SELECT name FROM subject WHERE subject_code = '$subjectCode'");
                            $subject = mysqli_fetch_assoc($subjectQuery);
                            $subjectName = $subject['name'];
                        ?>
                        

                        <table width="100%">
                            <h3><?php echo $subjectName; ?></h3>
                            <h4><?php echo $_POST['subject_code']; ?></h4>
                            <thead>
                                <tr>
                                    <td>Student Name</td>
                                    <td>Student Prn</td>
                                    <td>CA1</td>
                                    <td>CA2</td>
                                    <td>MSE</td>
                                    <td>ESE</td>
                                    <td>TOTAL</td>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $selectedSubjectCode = $_POST['subject_code'];

                            // Fetch student details and marks for the selected subject
                            $marksQuery = mysqli_query($conn, "SELECT s.name AS student_name, s.prn, m.CA1, m.CA2, m.MID_sem ,m.END_sem
                            FROM student s
                            LEFT JOIN marks m ON s.prn = m.student_prn 
                            LEFT JOIN subject_mappings sm ON m.subject_code = sm.subject_code
                            WHERE m.subject_code = '$selectedSubjectCode'
                            AND s.department = sm.department
                            AND s.semester = sm.semester");

                            while ($row = mysqli_fetch_assoc($marksQuery)) {
                                echo '<tr>';
                                echo '<td>' . $row['student_name'] . '</td>';
                                echo '<td>' . $row['prn'] . '</td>';
                                echo '<td>' . $row['CA1'] . '</td>';
                                echo '<td>' . $row['CA2'] . '</td>';
                                echo '<td>' . $row['MID_sem'] . '</td>';
                                echo '<td>' . $row['END_sem'] . '</td>';

                                // Calculate and display the total
                                $total = $row['CA1'] + $row['CA2'] + $row['MID_sem']+ $row['END_sem'];
                                echo '<td>' . $total . '</td>';

                                echo '</tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                        <h4 class="bottom"><?php echo $fetch['name']; ?></h4>
                        <h4 class="bottom">HOD: <?php echo $hod; ?></h4>
                    </div>
                </form>
            
        <?php endif; ?>
        <button class="print-btn" onclick="window.print();">Print result</button>
    </main>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        new DataTable('#example')({
        });
    </script>
    <script src="tost.js"></script>
</body>
</html>