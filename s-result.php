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

// Fetch result from marks table
$StudentResult = mysqli_query($conn, "SELECT subject.name AS subject_name,subject.subject_code, marks.CA1, marks.CA2, marks.MID_sem, marks.END_sem
                                       FROM marks 
                                       LEFT JOIN subject ON marks.subject_code = subject.subject_code
                                       LEFT JOIN subject_mappings ON subject.subject_code = subject_mappings.subject_code
                                       WHERE marks.student_prn = '$user_id'
                                       AND subject_mappings.department = (SELECT department FROM student WHERE prn = '$user_id')
                                       AND subject_mappings.semester = (SELECT semester FROM student WHERE prn = '$user_id')
                                       ORDER BY subject.subject_code ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Result</title>
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
                    <a href="s-result.php" class="active"><span><i class="las la-clipboard-list"></i></span>Result</a>
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
                Result
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
            <td>CA1</td>
            <td>CA2</td>
            <td>MSE</td>
            <td>ESE</td>
            <td>Grace Marks</td>
            <td>Total</td>
            <td>Grade</td>
            <td>Pointer</td>
            <td>Result</td>
        </tr>
    </thead>
    <tbody>
        <?php
        $totalSubjects = 0;
        $totalPointer = 0;
        while ($row = mysqli_fetch_assoc($StudentResult)) {
            echo '<tr>';
            echo '<td>' . $row['subject_name'] . '</td>';
            echo '<td>' . $row['CA1'] . '</td>';
            echo '<td>' . $row['CA2'] . '</td>';
            echo '<td>' . $row['MID_sem'] . '</td>';
            echo '<td>' . $row['END_sem'] . '</td>';
            
            // Calculate total marks
            $totalMarks = $row['CA1'] + $row['CA2'] + $row['MID_sem'] + $row['END_sem'];
            $graceMarks = 0;
            $totalSubjects += 1;

            // Apply grace marks condition
            if ($totalMarks < 40) {
                $neededMarks = 40 - $totalMarks;
                if ($neededMarks <= 5) {
                    $graceMarks = $neededMarks;
                    $totalMarks += $graceMarks;
                }
            }

            echo '<td>' . ($graceMarks == 0 ? '-' : $graceMarks) . '</td>';
            echo '<td>' . $totalMarks . '</td>';
            
            // Determine grade and pointer based on total marks
            if ($totalMarks >= 91 && $totalMarks <= 100) {
                $grade = 'EX';
                $pointer = 10.0;
            } elseif ($totalMarks >= 86 && $totalMarks <= 90) {
                $grade = 'AA';
                $pointer = 9.0;
            } elseif ($totalMarks >= 81 && $totalMarks <= 85) {
                $grade = 'AB';
                $pointer = 8.5;
            } elseif ($totalMarks >= 76 && $totalMarks <= 80) {
                $grade = 'BB';
                $pointer = 8.0;
            } elseif ($totalMarks >= 71 && $totalMarks <= 75) {
                $grade = 'BC';
                $pointer = 7.5;
            } elseif ($totalMarks >= 66 && $totalMarks <= 70) {
                $grade = 'CC';
                $pointer = 7.0;
            } elseif ($totalMarks >= 61 && $totalMarks <= 65) {
                $grade = 'CD';
                $pointer = 6.5;
            } elseif ($totalMarks >= 56 && $totalMarks <= 60) {
                $grade = 'DD';
                $pointer = 6.0;
            } elseif ($totalMarks >= 51 && $totalMarks <= 55) {
                $grade = 'DE';
                $pointer = 5.5;
            } elseif ($totalMarks >= 40 && $totalMarks <= 50) {
                $grade = 'EE';
                $pointer = 5.0;
            } else {
                $grade = 'EF';
                $pointer = 0.0;
            }
            $totalPointer +=$pointer;

            echo '<td>' . $grade . '</td>';
            echo '<td>' . number_format($pointer, 1) . '</td>';
            
            // Determine result
            if ($totalMarks >= 40) {
                echo '<td style="color: green; font-weight: bold;">PASS</td>';
            } else {
                echo '<td style="color: red; font-weight: bold;">FAIL</td>';
            }
            echo '</tr>';

            $SGPA = $totalPointer/$totalSubjects;
        }
        ?>
        <tr>
            <td colspan='8'></td>
            <td style = "font-weight: bold;">SGPA</td>
            <td style = "font-weight: bold;"><?php echo number_format($SGPA, 2); ?></td>
        </tr>
    </tbody>
</table>


        </div>
        <button class="print-btn" onclick="window.print();">Print result</button>
    </main>
    </div>
    


</body>
</html>