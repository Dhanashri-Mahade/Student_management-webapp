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

if (isset($_POST['add_subject'])) {
    // Retrieve form data
    $name = $_POST['name'];
    $code = $_POST['code'];
    $department = $_POST['department'];
    $semester = $_POST['semester'];
    $type = $_POST['type'];

    $insertSubject = $conn->prepare("INSERT INTO subject (subject_code, name, type, teacher_prn) VALUES (?, ?, ?, NULL) 
    ON DUPLICATE KEY UPDATE name = VALUES(name)");
    $insertSubject->bind_param("sss", $code, $name, $type);

    if ($insertSubject->execute()) {
        // Insert into subject_mappings table
        $insertMapping = $conn->prepare("INSERT INTO subject_mappings (subject_code, department, semester) VALUES (?, ?, ?)");
        $insertMapping->bind_param("sss", $code, $department, $semester);

        if ($insertMapping->execute()) {
            // Successfully added subject and mapping
            $message = "Subject added successfully!";
        } else {
            $message = "Error adding subject mapping: " . $insertMapping->error;
        }

        $insertMapping->close();
    } else {
        $message = "Error adding subject: " . $insertSubject->error;
    }

    $insertSubject->close();
}
// Assign Faculty to the subject
if (isset($_POST['assign_faculty'])) {
    foreach ($_POST['subject'] as $subject_code => $faculty_prn) {
        // Sanitize inputs
        $subject_code = mysqli_real_escape_string($conn, $subject_code);
        $faculty_prn = mysqli_real_escape_string($conn, $faculty_prn);

        // Update query
        $update_query = "UPDATE subject SET teacher_prn = '$faculty_prn' WHERE subject_code = '$subject_code'";

        // Execute the query
        if (mysqli_query($conn, $update_query)) {
            // Query executed successfully
            $message = "Faculty assigned to subjects successfully!";
        } else {
            // Query failed
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects Information</title>
    <link rel="stylesheet" href="css/a-faculty.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
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
                    <a href="a-faculty.php" ><span><i class="las la-user-tie"></i></span>Faculty</a>
                </li>
                <li>
                    <a href="a-students.php"><span><i class="las la-graduation-cap"></i></span>Students</a>
                </li>
                <li>
                    <a href="a-subjects.php"  class="active"><span><i class="las la-stream"></i></span>Subjects</a>
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
                Subjects
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
            <div class="add-subject">
                <h3 class="topic">Add New Subject</h3>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="part">
                        <span>Subject Name :</span>
                        <input type="text" placeholder="Enter Subject Name" name="name" class="box" required>
                        <span>Subject Code:</span>
                        <input type="text" placeholder="Enter Subject Code" name="code" class="box" required>
                        <span>Select Type:</span>
                        <select name="type" class="box" required>
                            <option value="regular">Regular</option>
                            <option value="lab">Lab</option>
                            <option value="elective">Elective</option>
                            <option value="Audit">Audit</option>
                        </select>
                    </div>
                    <div class="part">
                        <span>Department:</span>
                        <select name="department" class="box" required>
                            <option value="Computer Engineering">Computer Engineering</option>
                            <option value="IT">IT</option>
                            <option value="Chemical Engineering">Chemical Engineering</option>
                            <option value="Petrochemical Engineering">Petrochemical Engineering</option>
                            <option value="Civil Engineering">Civil Engineering</option>
                            <option value="Electrical Engineering">Electrical Engineering</option>
                        </select>
                        <span>Select Semester:</span>
                        <select name="semester" class="box" required>
                            <option value="1">First Semester</option>
                            <option value="2">Second Semester</option>
                            <option value="3">Third Semester</option>
                            <option value="4">Fourth Semester</option>
                            <option value="5">Fifth Semester</option>
                            <option value="6">Sixth Semester</option>
                            <option value="7">Seventh Semester</option>
                            <option value="8">Eighth Semester</option>
                        </select>
                        <input type="submit" value="Add Subject" name="add_subject" class="btn">
                    </div>
                </form>
            </div>

            <form class="" action="" method="post" enctype="multipart/form-data">
                <select name="department" class="box" required>
                    <option value="Computer Engineering">Computer Engineering</option>
                    <option value="IT">IT</option>
                    <option value="Chemical Engineering">Chemical Engineering</option>
                    <option value="Petrochemical Engineering">Petrochemical Engineering</option>
                    <option value="Civil Engineering">Civil Engineering</option>
                    <option value="Electrical Engineering">Electrical Engineering</option>
                </select>
                <select name="semester" class="box" required>
                    <option value="1">First Semester</option>
                    <option value="2">Second Semester</option>
                    <option value="3">Third Semester</option>
                    <option value="4">Fourth Semester</option>
                    <option value="5">Fifth Semester</option>
                    <option value="6">Sixth Semester</option>
                    <option value="7">Seventh Semester</option>
                    <option value="8">Eighth Semester</option>
                </select>
                <input type="submit" value="Select" name="search" class="box">
            </form>

            <?php if (isset($_POST['search']) && isset($_POST['department']) && isset($_POST['semester'])) : ?>
                <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="department" value="<?php echo $_POST['department']; ?>">
                <input type="hidden" name="semester" value="<?php echo $_POST['semester']; ?>">
                    <table class="card-body">
                    <?php
                        $dep = $_POST['department'];
                        $sem = $_POST['semester'];
                        $filtersub = mysqli_query($conn, "SELECT subject.name AS subject_name, subject.subject_code, faculty.name AS faculty_name, faculty.prn 
                        FROM subject
                        LEFT JOIN faculty ON subject.teacher_prn = faculty.prn
                        INNER JOIN subject_mappings ON subject.subject_code = subject_mappings.subject_code
                        WHERE subject_mappings.department = '$dep' AND subject_mappings.semester = $sem
                                                        ") or die('query failed');
                                                    ?>
                        <thead>
                            <tr>
                                <td>Subjects <?php echo "(".$dep.")Sem-".$sem ?></td>
                                <td>Subject Code</td>
                                <td>Faculty assigned</td>
                            </tr>
                        </thead>

                        <tbody>
                        <?php
                            while($fetch = $filtersub->fetch_assoc())
                            {
                            ?>
                                <tr>
                                    <td><?php echo $fetch['subject_name']; ?></td>
                                    <td><?php echo $fetch['subject_code']; ?></td>
                                    <td>
                                        <select name="subject[<?php echo $fetch['subject_code']; ?>]" class="box" required>
                                            <option value="">Select faculty</option>
                                            <?php
                                            // Fetch faculty for the subject
                                            $faculty_query = "SELECT * FROM faculty WHERE department = '$dep'";
                                    $faculty_result = mysqli_query($conn, $faculty_query);
                                    while ($faculty = mysqli_fetch_assoc($faculty_result)) {
                                        $selected = ($faculty['prn'] == $fetch['prn']) ? 'selected' : '';
                                        echo '<option value="' . $faculty['prn'] . '" ' . $selected . '>' . $faculty['name'] . '</option>';
                                    }
                                    ?>
                                </select>
                                    </td>
                                </tr>
                                
                            <?php
                            } ?>
                            <tr>
                                <td> </td>
                                <td> </td>
                                <td><input type="submit" value="Assign Faculties" name="assign_faculty" class="btn"></td>
                            </tr>
                        </tbody>
                        
                    </table>
                    
                </form>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Subjects</h3>
                </div>
                <div class="card-body">
                    <table id="example" width="100%">
                        <?php
                        $addmissions = mysqli_query($conn, "SELECT subject.name AS subject_name, subject.subject_code, faculty.name AS faculty_name, subject.type 
                                                            FROM subject
                                                        LEFT JOIN faculty ON subject.teacher_prn = faculty.prn") or die('query failed');
                                                    ?>
                            <thead>
                                <tr>
                                    <td>Subject Name</td>
                                    <td>Subject Code</td>
                                    <td>Type</td>
                                    <td>Faculty assigned</td>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            while($fetch = $addmissions->fetch_assoc())
                            {
                            ?>
                                <tr>
                                    <td><?php echo $fetch['subject_name']; ?></td>
                                    <td><?php echo $fetch['subject_code']; ?></td>
                                    <td><?php echo $fetch['type']; ?></td>
                                    <td><?php echo $fetch['faculty_name']; ?></td>
                                </tr>
                                
                            <?php
                            } ?>
                            </tbody>
                    </table>
                </div>
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