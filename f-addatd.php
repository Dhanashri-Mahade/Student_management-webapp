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

if (isset($_POST['select_sub']) && isset($_POST['subject_code'])) {
    $selectedSubjectCode = $_POST['subject_code'];
    $subjectMappingQuery = mysqli_query($conn, "SELECT * FROM subject_mappings WHERE subject_code = '$selectedSubjectCode'");
    $subjectMapping = mysqli_fetch_assoc($subjectMappingQuery);

    // Check if the subject mapping query was successful
    if ($subjectMapping) {
        $fetchstudents = mysqli_query($conn, "SELECT * FROM student WHERE semester = '{$subjectMapping['semester']}' AND department = '{$subjectMapping['department']}' AND status = 'verified'");
    }
}

date_default_timezone_set('Asia/Kolkata');

if (isset($_POST['addrecord'])) {
    if ($_POST['date'] == NULL) {
        // if date is not selected, set today's date
        $date = date("Y-m-d");
    } else {
        // if date is selected
        $date = $_POST['date'];
    }

    $subjectCode = $_POST['subject_code'];

    // Loop through the submitted data to insert or update attendance records
    $presentStudents = isset($_POST['present']) ? $_POST['present'] : array();
    $absentStudents = isset($_POST['absent']) ? $_POST['absent'] : array();

    // Iterate through present students and insert or update attendance records
foreach ($presentStudents as $prn) {
    $insertQuery = "INSERT INTO attendance (student_prn, subject_code, attendance_date, status) VALUES ('$prn', '$subjectCode', '$date', 'P') 
                    ON DUPLICATE KEY UPDATE status = 'P'";
    mysqli_query($conn, $insertQuery);
}

// Identify absent students (unchecked checkboxes) and insert 'A' for them
$subjectMappingQuery = mysqli_query($conn, "SELECT * FROM subject_mappings WHERE subject_code = '$subjectCode'");
    $subjectMapping = mysqli_fetch_assoc($subjectMappingQuery);

    // Check if the subject mapping query was successful
    if ($subjectMapping) {
        $fetchstudents = mysqli_query($conn, "SELECT * FROM student WHERE semester = '{$subjectMapping['semester']}' AND department = '{$subjectMapping['department']}' AND status = 'verified'");
    }

if (!empty($fetchstudents)) {
    while ($student = $fetchstudents->fetch_assoc()) {
        $prn = $student['prn'];
        if (!in_array($prn, $presentStudents)) {
            $insertQuery = "INSERT INTO attendance (student_prn, subject_code, attendance_date, status) VALUES ('$prn', '$subjectCode', '$date', 'A') 
                            ON DUPLICATE KEY UPDATE status = 'A'";
            mysqli_query($conn, $insertQuery);
        }
    }
}

    $message = "Attendance added successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Attendance</title>
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
                    <a href="f-addatd.php" class="active"><span><i class="las la-user-edit"></i></span>Add Attendance</a>
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
                Add Attendance
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
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="subject_code" value="<?php echo $_POST['subject_code']; ?>">
                    <table>
                        <h4><?php echo $_POST['subject_code']; ?></h4>
                        <h4><?php 
                            // Fetch the subject name based on the subject code
                            $subjectCode = $_POST['subject_code'];
                            $subjectQuery = mysqli_query($conn, "SELECT name, type FROM subject WHERE subject_code = '$subjectCode'");
                            $subject = mysqli_fetch_assoc($subjectQuery);
                            $subjectName = $subject['name'];
                            $subjectType = $subject['type'];
                            echo $subjectName;
                        ?>
                        </h4>
                        <thead>
                            <tr>
                                <td>Select Date (optional)</td>
                                <td colspan="2"><input type="date" name="date"></td>
                            </tr>
                            <tr>
                                <td>Student Name</td>
                                <td>Prn</td>
                                <td>P</td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        while ($student = $fetchstudents->fetch_assoc()){ ?>
                            <tr>
                                <td><?php echo $student['name']; ?></td>
                                <td><?php echo $student['prn']; ?></td>
                                <td><input type="checkbox" name="present[]" value="<?php echo $student['prn']; ?>" checked></td>
                            </tr>
                        <?php } ?>
                            <tr>
                                <td colspan="3"><input type="submit" name="addrecord" value="Add Attendance"></td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            <?php endif; ?>
        </div>
    </main>
    </div>
    <script src="tost.js"></script>
</body>
</html>