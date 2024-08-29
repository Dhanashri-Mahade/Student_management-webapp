<?php
include 'config.php';
session_start();

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function isValidMobileNumber($mobile_number) {
    // Validate that the mobile number contains only numeric digits
    if (!preg_match('/^\+?[0-9]+$/', $mobile_number)) {
        return false;
    }

    $length = strlen($mobile_number);
    if ($length < 10 || $length > 13) {
        return false;
    }

    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST["name"];
    $email = $_POST["email"];
    $prn = $_POST["prn"];
    $password = $_POST["password"];
    $cpassword = $_POST["cpassword"];
    $user_type = $_POST["user_type"];
    $mobile = $_POST["mobile_number"];
    $department = $_POST["department"];
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/' . $image;

    // Validate mobile number
    if (!isValidMobileNumber($mobile)) {
        $message = 'Please enter a valid mobile number';
        // You can redirect the user back to the registration form or handle the error accordingly
    } else {
        // Check for existing users in both student and faculty tables
        $select = $conn->prepare("SELECT * FROM student WHERE prn = ? AND password = ?");
        $select->bind_param("ss", $prn, $password);
        $select->execute();
        $result = $select->get_result();

        $selectFaculty = $conn->prepare("SELECT * FROM faculty WHERE prn = ? AND password = ?");
        $selectFaculty->bind_param("ss", $prn, $password);
        $selectFaculty->execute();
        $resultFaculty = $selectFaculty->get_result();

        if ($result->num_rows > 0 || $resultFaculty->num_rows > 0) {
            $message = 'User already exists';
        } else {
            if ($password != $cpassword) {
                $message = 'Confirm password not matched!';
            } elseif ($image_size > 150000) {
                $message = 'Image size is too large!';
            } else {
                // You should perform additional validation and sanitation here
                // Insert data into the appropriate table
                if ($user_type === "student") {
                    $sql = $conn->prepare("INSERT INTO student(prn, name, email, password, image, mobile, department) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?)");
                } elseif ($user_type === "faculty") {
                    $sql = $conn->prepare("INSERT INTO faculty(prn, name, email, password, image, mobile, department) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?)");
                } else {
                    $message = "Invalid user type";
                    exit();
                }

                $sql->bind_param("sssssss", $prn, $name, $email, $password, $image, $mobile, $department);

                if ($sql->execute()) {
                    move_uploaded_file($image_tmp_name, $image_folder);

                    //code for sending mail to registerd user
                    $to = $email; //reciver email
                    $message = "You Have Registred successfully"; // body of mail
                    $subject = "DBATU Registration Notification"; // subject of mail
                    $header = "From: no-reply@resultp.com"; //Sender email
                    mail($to, $subject, $message, $header); //mail function to send email

                    header('location: login.php');
                    exit(); // Add exit to prevent further execution after redirection
                } else {
                    echo "Error: " . $sql->error;
                    $message = "Registration failed!";
                }
                $sql->close();
            }
        }
    }
}

// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <link rel="stylesheet" href="css/register.css" class="css">
</head>
<body>
    <?php
    // Check for registration success session variable
    if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']) {
        echo '<script>alert("Registration successful!");</script>';
        // Reset the session variable to prevent displaying the alert on page refresh
        $_SESSION['registration_success'] = false;
    }
    ?>
    <div class="form-container">
        <form action="" enctype="multipart/form-data" method="POST">
            <h3>Register now</h3>
            <?php
                if (isset($message)) {
                    echo '<div class="toast-container" id="toastContainer"></div>';
                    echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast('$message'); });</script>";
                }
            ?>
            <span>Select who you are</span>
            <select name="user_type" class="box">
                <option value="student">Student</option>
                <option value="faculty">Faculty</option>
            </select>
            <span>Name :</span>
            <input type="text" placeholder="enter full name" name="name" class="box" required>
            <span>PRN/ID :</span>
            <input type="text" placeholder="enter prn/id" name="prn" class="box" required>
            <span>Email :</span>
            <input type="email" placeholder="enter email" name="email" class="box" required>
            <span>Mobile Number :</span>
            <input type="text" placeholder="enter mobile number" name="mobile_number" class="box" required>
            <span>Select Your Department:</span>
            <select name="department" class="box" required>
                <option value="Computer Engineering">Computer Engineering</option>
                <option value="IT">IT</option>
                <option value="Chemical Engineering">Chemical Engineering</option>
                <option value="Petrochemical Engineering">Petrochemical Engineering</option>
                <option value="Civil Engineering">Civil Engineering</option>
                <option value="Electrical Engineering">Electrical Engineering</option>
            </select>
            <span>Enter Password</span>
            <input type="password" placeholder="enter password" name="password" class="box" required>
            <span>Confirm Password</span>
            <input type="password" placeholder="confirm password" name="cpassword" class="box" required>
            <span>Upload Profile picture (size <= 150kb)</span>
            <input type="file" class="box" name="image" accept="image/jpg, image/jpeg, image/png">
            <input type="submit" value="register now" class="btn">
            <p>already have an account ? <a href="login.php">login now</a></p>
        </form>

    </div>
    <script src="tost.js"></script>
</body>
</html>