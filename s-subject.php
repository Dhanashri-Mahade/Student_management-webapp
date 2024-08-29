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

// Fetch student details
$select = mysqli_query($conn, "SELECT * FROM `student` WHERE prn = '$user_id'") or die('query failed');
if (mysqli_num_rows($select) > 0) {
    $fetch = mysqli_fetch_assoc($select);

    // Fetch subjects for the student based on department and semester
    $department = $fetch['department'];
    $semester = $fetch['semester'];

    $subjectsQuery = mysqli_query($conn, "SELECT subject.subject_code, subject.name as subject_name, 
                                                 COALESCE(faculty.name, 'Not Assigned') as faculty_name
                                          FROM subject_mappings
                                          JOIN subject ON subject_mappings.subject_code = subject.subject_code
                                          LEFT JOIN faculty ON subject.teacher_prn = faculty.prn
                                          WHERE subject_mappings.department = '$department'
                                          AND subject_mappings.semester = $semester") or die('query failed');
}
// Handle form submission for selecting elective subjects
if (isset($_POST['select-elective'])) {
    $semester = $_POST['semester'];
    $elective1 = isset($_POST['elective1']) ? $_POST['elective1'][0] : NULL;
    $elective2 = isset($_POST['elective2']) ? $_POST['elective2'][0] : NULL;
    $elective3 = isset($_POST['elective3']) ? $_POST['elective3'][0] : NULL;

    $query = "INSERT INTO elective (stud_prn, elective1, elective2, elective3, semester) 
              VALUES ('$user_id', '$elective1', '$elective2', '$elective3', '$semester')";

    $result = mysqli_query($conn, $query) or die('Query failed: ' . mysqli_error($conn));

    if ($result) {
        $message = 'Elective subjects selected successfully!';
    } else {
        $message = 'Failed to select elective subjects!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects List</title>
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
                    <a href="s-dash.php" ><span><i class="las la-chalkboard"></i></span>Dashboard</a>
                </li>
                <li>
                    <a href="s-subject.php" class="active"><span><i class="las la-stream"></i></span>Subjects</a>
                </li>
                <li>
                    <a href="s-result.php" ><span><i class="las la-clipboard-list"></i></span>Result</a>
                </li>
                <li>
                    <a href="s-viewatd.php"><span><i class="las la-list-alt"></i></i></span>View Attendance</a>
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
        <?php 
                if(isset($message)){
                    echo '<div class="message">'.$message.'</div>';
                }
            ?>
        <div class="content">

        <h3>Your Subjects</h3>
        <table>
            <thead>
                <tr>
                    <td>Subject Code</td>
                    <td>Subject Name</td>
                    <td>Faculty Assigned</td>
                </tr>
            </thead>
            <tbody>
            <?php
            // Array to hold elective subjects
                $electiveSubjects = [];
                while ($subject = mysqli_fetch_assoc($subjectsQuery)) {
                    echo '<tr>';
                        echo '<td>' . $subject['subject_code'] . '</td>';
                        echo '<td>' . $subject['subject_name'] . '</td>';
                        echo '<td>' . $subject['faculty_name'] . '</td>';
                    echo '</tr>';
        
                    // Check if the subject is an elective
                    if (preg_match('/^([A-Za-z0-9]+)\(\w+\)$/', $subject['subject_code'], $matches)) {
                        $baseCode = $matches[1];
                        if (!isset($electiveSubjects[$baseCode])) {
                            $electiveSubjects[$baseCode] = [];
                        }
                        $electiveSubjects[$baseCode][] = $subject;
                        }
                    }
            ?>
            </tbody>
        </table>

        <?php
        // Check if there are any elective subjects available
            if (!empty($electiveSubjects)) {
                // Check if the student has already selected elective subjects
                $electiveCheckQuery = mysqli_query($conn, "SELECT * FROM elective WHERE stud_prn = '$user_id' AND semester = '$semester'");
                if(mysqli_num_rows($electiveCheckQuery) > 0) {
                    // If elective subjects are already selected, display their names
                    echo '<h4>Your Selected Elective Subjects</h4>';
                    echo '<ul class = "selectd-elective">';
                    $electiveData = mysqli_fetch_assoc($electiveCheckQuery);
                    $elective1 = $electiveData['elective1'];
                    $elective2 = $electiveData['elective2'];
                    $elective3 = $electiveData['elective3'];

                    // Fetch and display elective subject names
                    $electiveSubjectsQuery = mysqli_query($conn, "SELECT * FROM subject WHERE subject_code IN ('$elective1', '$elective2', '$elective3')");
                    while($row = mysqli_fetch_assoc($electiveSubjectsQuery)) {
                        echo '<li>' . $row['name'] . '</li>';
                    }
                    echo '</ul>';
                } else {
        ?>
        <h4>Select Elective Subjects</h4> 
        <form id="elective" action="" method="post" enctype="multipart/form-data">
            <div class="add-elective">
                <div class="subjects">
                    <?php
                    $electiveIndex = 1;
                    foreach ($electiveSubjects as $baseCode => $subjects) {
                        echo "<h4>Electives for $baseCode</h4>";
                        foreach ($subjects as $subject) {
                            echo '<div>';
                            echo '<input type="radio" name="elective' . $electiveIndex . '[]" value="' . $subject['subject_code'] . '">';
                            echo '<label for="' . $subject['subject_code'] . '">' . $subject['subject_name'] . ' (' . $subject['subject_code'] . ')</label>';
                            echo '</div>';
                        }
                        $electiveIndex++;
                    }
                    ?>
                </div>
                <input type="hidden" name="semester" value="<?php echo $semester; ?>">
                <input type="submit" value="Select" name="select-elective" class="box">
            </div>
        </form>
        <?php
            }
        }
        ?>
        </div>
    </main>
    </div>
    
</body>
</html>