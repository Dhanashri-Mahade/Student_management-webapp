<?php
include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
    exit();
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('location:index.php');
    exit();
}

$facultySubjects = mysqli_query($conn, "SELECT * FROM subject WHERE teacher_prn = '$user_id'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
    <link rel="stylesheet" href="css/s-result.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

    <script>
            document.addEventListener('DOMContentLoaded', function () {
                var toggleSidebar = document.getElementById('toggle-sidebar');
                var body = document.body;
    
                toggleSidebar.addEventListener('click', function () {
                    body.classList.toggle('sidebar-closed');
                });
            });
        </script>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src=""></script>
    <script>
        $(document).ready(function() {
            $('#example').DataTable( {
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf',
                ]
            } );
        } );
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
                    <a href="f-add-marks.php"><span><i class="las la-edit"></i></span>Add Marks</a>
                </li>
                <li>
                    <a href="f-view-marks.php"><span><i class="las la-clipboard-list"></i></span>View Marks</a>
                </li>
                <li>
                    <a href="f-addatd.php"><span><i class="las la-user-edit"></i></span>Add Attendance</a>
                </li>
                <li>
                    <a href="f-viewatd.php" class="active"><span><i class="las la-clipboard-list"></i></span>View Attendance</a>
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
                view Attendance
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
                while ($subject = mysqli_fetch_assoc($facultySubjects)) {
                    echo '<option value="' . $subject['subject_code'] . '">' . $subject['name'] . '</option>';
                }
                ?>
            </select>
            <input type="submit" value="Select Subject" name="select_sub" class="box">
        </form>

        <?php if (isset($_POST['select_sub']) && isset($_POST['subject_code'])) : ?>
            <?php
            $selectedSubjectCode = $_POST['subject_code'];

            // Fetch students for the selected subject
            $subjectMappingQuery = mysqli_query($conn, "SELECT * FROM subject_mappings WHERE subject_code = '$selectedSubjectCode'");
            $subjectMapping = mysqli_fetch_assoc($subjectMappingQuery);

            if ($subjectMapping) {
                $fetchstudents = mysqli_query($conn, "SELECT * FROM student WHERE semester = '{$subjectMapping['semester']}' AND department = '{$subjectMapping['department']}' AND status = 'verified'");

                // Fetch unique attendance dates for the selected subject
                $attendanceDatesQuery = mysqli_query($conn, "SELECT DISTINCT attendance_date FROM attendance WHERE subject_code = '$selectedSubjectCode'");
                $attendanceDates = array();

                while ($row = mysqli_fetch_assoc($attendanceDatesQuery)) {
                    $attendanceDates[] = $row['attendance_date'];
                }
                
                // Count total lectures for the selected subject
                $totalLectures = count($attendanceDates);
            }
            ?>

            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="subject_code" value="<?php echo $_POST['subject_code']; ?>">
                
                <div class="print-container">
                    <h4><?php echo $_POST['subject_code']; ?></h4>
                    <h4><?php 
                        // Fetch the subject name based on the subject code
                        $subjectCode = $_POST['subject_code'];
                        $subjectQuery = mysqli_query($conn, "SELECT name FROM subject WHERE subject_code = '$subjectCode'");
                        $subject = mysqli_fetch_assoc($subjectQuery);
                        $subjectName = $subject['name'];
                        echo $subjectName;
                    ?>
                    </h4>
                    <table id="example" style="width:100%">
                        <thead>
                            <tr>
                                <td rowspan='2'>Name</td>
                                <td rowspan='2'>PRN</td>
                                <?php
                                // Display attendance dates
                                foreach ($attendanceDates as $date) {
                                    echo "<td rowspan='2'>" . date("d-m", strtotime($date)) . "</td>";
                                }
                                ?>
                                <td colspan='2'>Summary</td> <!-- Add these two columns -->
                            </tr>
                            <tr>
                                <td>Total</td>
                                <td>%</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($student = $fetchstudents->fetch_assoc()) {
                                $prn = $student['prn'];

                                // Count total lectures attended by the student
                                $attendedLectures = 0;
                                foreach ($attendanceDates as $date) {
                                    $statusQuery = mysqli_query($conn, "SELECT status FROM attendance WHERE subject_code = '$selectedSubjectCode' AND attendance_date = '$date' AND student_prn = '$prn'");
                                    $status = mysqli_fetch_assoc($statusQuery)['status'];

                                    if ($status === 'P') {
                                        $attendedLectures++;
                                    }
                                }

                                // Calculate attendance percentage
                                $attendancePercentage = ($totalLectures > 0) ? number_format(($attendedLectures / $totalLectures) * 100, 1) : 0;

                                // Output the additional columns in the table
                                echo "<tr>";
                                echo "<td>{$student['name']}</td>";
                                echo "<td>{$student['prn']}</td>";

                                // Display attendance status for each date
                                foreach ($attendanceDates as $date) {
                                    $statusQuery = mysqli_query($conn, "SELECT status FROM attendance WHERE subject_code = '$selectedSubjectCode' AND attendance_date = '$date' AND student_prn = '$prn'");
                                    $status = mysqli_fetch_assoc($statusQuery)['status'];
                                    echo "<td>$status</td>";
                                }

                                // Output the additional columns in the table
                                echo "<td>$attendedLectures / $totalLectures</td>";
                                echo "<td>$attendancePercentage%</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>

    </div>
    <script src="tost.js"></script>

</body>
</html>