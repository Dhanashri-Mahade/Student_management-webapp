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
//function for adding department
if (isset($_POST['add_department'])) {
    // Retrieve form data
    $name = $_POST['name'];
    $code = $_POST['code'];

    $insertdepartment = $conn->prepare("INSERT INTO department(id, de_name, hod) VALUES (?, ?, NULL) 
    ON DUPLICATE KEY UPDATE de_name = VALUES(de_name)");
    $insertdepartment->bind_param("ss", $code, $name);

    if ($insertdepartment->execute()) {
        // Successfully added department
        $message = "Subject added successfully!";
    } else {
        $message = "Error adding subject: " . $insertdepartment->error;
    }

    $insertdepartment->close();
}

// Functionality for changing HODs
if (isset($_POST['select_hod'])) {
    foreach ($_POST['department'] as $department_id => $hod_prn) {
        $update_query = "UPDATE department SET hod = '$hod_prn' WHERE id = $department_id";
        mysqli_query($conn, $update_query);
    }
    $message = "HODs updated successfully!";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments</title>
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
                    <a href="a-subjects.php"  class=""><span><i class="las la-stream"></i></span>Subjects</a>
                </li>
                <li>
                    <a href="a-department.php"  class="active"><span><i class="las la-stream"></i></span>Departments</a>
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
                Departments
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
                <h3 class="topic">Add New Department</h3>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="part">
                        <span>Department Name :</span>
                        <input type="text" placeholder="Enter Department Name" name="name" class="box" required>
                      
                    </div>
                    <div class="part">
                        <span>Department Code:</span>
                        <input type="text" placeholder="Enter Department Code" name="code" class="box" required>
                        <input type="submit" value="Add Department" name="add_department" class="btn">
                    </div>
                </form>
            </div>
            <div class="card">
            <?php
            // Display table with selectable faculty members
            $departments_query = "SELECT department.id AS id, de_name, faculty.prn AS hod_prn, faculty.name AS hod
                      FROM department
                      LEFT JOIN faculty ON department.hod = faculty.prn";
                        $departments_result = mysqli_query($conn, $departments_query);
            ?>

            <form class="add-hod" action="" method="post" enctype="multipart/form-data">
                <div class="card-header">
                    <h3>Departments</h3>
                    </div>
                        <div class="card-body">
                        
                <table id="example" width="100%">
                    <thead>
                        <tr>
                            <td>Department Id</td>
                            <td>Department Name</td>
                            <td>Select HOD</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            while ($department = mysqli_fetch_assoc($departments_result)) {
                        ?>
                        <tr>
                            <td><?php echo $department['id']; ?></td>
                            <td><?php echo $department['de_name']; ?></td>
                            <td>
                                <select name="department[<?php echo $department['id']; ?>]" class="box" required>
                                    <option value="">Select HOD</option>
                                    <?php
                                    // Fetch faculty members for the department
                                    $faculty_query = "SELECT * FROM faculty WHERE department = '{$department['de_name']}' AND status = 'verified'";
                                    $faculty_result = mysqli_query($conn, $faculty_query);
                                    while ($faculty = mysqli_fetch_assoc($faculty_result)) {
                                        $selected = ($faculty['prn'] == $department['hod_prn']) ? 'selected' : '';
                                        echo '<option value="' . $faculty['prn'] . '" ' . $selected . '>' . $faculty['name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                
                    </div>
                    
                </div>
                <input type="submit" value="Update HODs" name="select_hod" class="btn">
            </form>
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