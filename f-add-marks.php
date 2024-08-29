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
// Handle form submission to add marks
if (isset($_POST['add_marks'])) {
    $subjectCode = mysqli_real_escape_string($conn, $_POST['subject_code']);
    $subjectTypeQuery = mysqli_query($conn, "SELECT type FROM subject WHERE subject_code = '$subjectCode'");
    $subjectTypeRow = mysqli_fetch_assoc($subjectTypeQuery);
    $subjectType = $subjectTypeRow['type'];

    // Define maximum marks for different subject types
    $maxMarks = [
        'regular' => ['CA1' => 10, 'CA2' => 10, 'MID_sem' => 20, 'END_sem' => 60],
        'elective' => ['CA1' => 10, 'CA2' => 10, 'MID_sem' => 20, 'END_sem' => 60],
        'lab' => ['CA1' => 30, 'CA2' => 30, 'END_sem' => 40]
    ];

    $error = false;
    foreach ($_POST['marks'] as $studentPrn => $examMarks) {
        foreach ($examMarks as $examType => $marks) {
            if ($marks > $maxMarks[$subjectType][$examType]) {
                $error = true;
                $message = "Please insert valid marks. Maximum marks for $examType is " . $maxMarks[$subjectType][$examType];
                break 2; // Break out of both loops if an error is found
            }
        }
    }

    if (!$error) {
        foreach ($_POST['marks'] as $studentPrn => $examMarks) {
            $checkExistingQuery = mysqli_query($conn, "SELECT * FROM marks WHERE student_prn = '$studentPrn' AND subject_code = '$subjectCode'");
            $existingMarks = mysqli_fetch_assoc($checkExistingQuery);

            if ($existingMarks) {
                $updateQuery = "UPDATE marks SET ";
                foreach ($examMarks as $examType => $marks) {
                    $updateQuery .= "$examType = '$marks', ";
                }
                $updateQuery = rtrim($updateQuery, ', ');
                $updateQuery .= " WHERE student_prn = '$studentPrn' AND subject_code = '$subjectCode'";

                mysqli_query($conn, $updateQuery);
            } else {
                $insertQuery = "INSERT INTO marks (student_prn, subject_code, ";
                $insertValues = "VALUES ('$studentPrn', '$subjectCode', ";
                foreach ($examMarks as $examType => $marks) {
                    $insertQuery .= "$examType, ";
                    $insertValues .= "'$marks', ";
                }
                $insertQuery = rtrim($insertQuery, ', ');
                $insertValues = rtrim($insertValues, ', ');
                $insertQuery .= ") ";
                $insertValues .= ") ";

                mysqli_query($conn, $insertQuery . $insertValues);
            }
        }
        $message = "Marks added successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Marks</title>
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
                    <a href="f-add-marks.php" class="active"><span><i class="las la-edit"></i></span>Add Marks</a>
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
                Add Marks
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
            <?php
                $subjectCode = $_POST['subject_code'];
                $subjectQuery = mysqli_query($conn, "SELECT name, type FROM subject WHERE subject_code = '$subjectCode'");
                $subject = mysqli_fetch_assoc($subjectQuery);
                $subjectName = $subject['name'];
                $subjectType = $subject['type'];
            ?>
            <table >
                <h4><?php echo $subjectName; ?></h4>
                <h4><?php echo $_POST['subject_code']; ?></h4>
                <thead>
                    <tr>
                        <td>Student Name</td>
                        <td>Student Prn</td>
                        <?php if ($subjectType == 'regular' || $subjectType == 'elective') : ?>
                            <td>CA1</td>
                            <td>CA2</td>
                            <td>Mid Sem</td>
                            <td>End Sem</td>
                        <?php elseif ($subjectType == 'lab') : ?>
                            <td>CA1</td>
                            <td>CA2</td>
                            <td>End Sem</td>
                        <?php elseif ($subjectType == 'Audit') : ?>
                            <td>End Sem</td>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if ($subjectType == 'elective') {
                            $studentsQuery = mysqli_query($conn, "SELECT s.* FROM student s JOIN elective e ON s.prn = e.stud_prn WHERE (e.elective1 = '$subjectCode' OR e.elective2 = '$subjectCode' OR e.elective3 = '$subjectCode') AND s.status = 'verified'");
                        } else {
                            $subjectMappingQuery = mysqli_query($conn, "SELECT * FROM subject_mappings WHERE subject_code = '$subjectCode'");
                            $subjectMapping = mysqli_fetch_assoc($subjectMappingQuery);
                            $studentsQuery = mysqli_query($conn, "SELECT * FROM student WHERE semester = '{$subjectMapping['semester']}' AND department = '{$subjectMapping['department']}' AND status = 'verified'");
                        }

                        while ($student = mysqli_fetch_assoc($studentsQuery)) {
                        echo '<tr>';
                        echo '<td>' . $student['name'] . '</td>';
                        echo '<td>' . $student['prn'] . '</td>';

                        
                            $marksQuery = mysqli_query($conn, "SELECT * FROM marks WHERE student_prn = '{$student['prn']}' AND subject_code = '$subjectCode'");
                            $marks = mysqli_fetch_assoc($marksQuery);

                            if ($subjectType == 'regular' || $subjectType == 'elective') {
                                echo '<td><input type="text" name="marks[' . $student['prn'] . '][CA1]" value="' . ($marks['CA1'] ?? '') . '" /></td>';
                                echo '<td><input type="text" name="marks[' . $student['prn'] . '][CA2]" value="' . ($marks['CA2'] ?? '') . '" /></td>';
                                echo '<td><input type="text" name="marks[' . $student['prn'] . '][MID_sem]" value="' . ($marks['MID_sem'] ?? '') . '" /></td>';
                                echo '<td><input type="text" name="marks[' . $student['prn'] . '][END_sem]" value="' . ($marks['END_sem'] ?? '') . '" /></td>';
                            } elseif ($subjectType == 'lab') {
                                echo '<td><input type="text" name="marks[' . $student['prn'] . '][CA1]" value="' . ($marks['CA1'] ?? '') . '" /></td>';
                                echo '<td><input type="text" name="marks[' . $student['prn'] . '][CA2]" value="' . ($marks['CA2'] ?? '') . '" /></td>';
                                echo '<td><input type="text" name="marks[' . $student['prn'] . '][END_sem]" value="' . ($marks['END_sem'] ?? '') . '" /></td>';
                            } elseif ($subjectType == 'Audit') {
                                echo '<td><input type="text" name="marks[' . $student['prn'] . '][END_sem]" value="' . ($marks['END_sem'] ?? '') . '" /></td>';
                            }
                        
                        echo '</tr>';
                        }
                    ?>
            </tbody>
        </table>
        <input type="submit" value="Add Marks" name="add_marks" class="btn-addmark">
    </form>
    <?php endif; ?>
    </div>
</main>
    </div>
    <script src="tost.js"></script>
</body>
</html>