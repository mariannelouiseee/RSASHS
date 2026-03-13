<?php
include("connect.php");
include("functions.php");
session_start(); // Start session for logging

$message = "";
$modalType = ""; // 'success', 'error', or 'warning'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $student_id = $_POST['student_id'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $extension_name = $_POST['extension_name'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $last_school = $_POST['last_school'];
    $school_address = $_POST['school_address'];
    $date_attended = $_POST['date_attended'];
    $honors_received = $_POST['honors_received'];
    $father_name = $_POST['father_name'];
    $father_occupation = $_POST['father_occupation'];
    $mother_name = $_POST['mother_name'];
    $mother_occupation = $_POST['mother_occupation'];
    $guardian_name = $_POST['guardian_name'];
    $guardian_contact = $_POST['guardian_contact'];

    // Handle image upload
    $image_name = "";
    if (isset($_FILES['student_image']) && $_FILES['student_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_tmp = $_FILES['student_image']['tmp_name'];
        $file_type = mime_content_type($file_tmp);
        $file_size = $_FILES['student_image']['size'];

        if (in_array($file_type, $allowed_types) && $file_size <= 2 * 1024 * 1024) { // 2MB limit
            $image_ext = pathinfo($_FILES['student_image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid("student_", true) . "." . $image_ext;
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            move_uploaded_file($file_tmp, $upload_dir . $image_name);
        } else {
            $message = "Invalid image file. Only JPG/PNG under 2MB are allowed.";
            $modalType = "error";
        }
    }

    if ($modalType !== "error") {
        // Check if student ID already exists in users table
        $check_sql = "SELECT id FROM users WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $student_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "Student ID already exists.";
            $modalType = "error";
            $check_stmt->close();
        } else {
            $check_stmt->close();

            // Insert into students table
            $sql = "INSERT INTO students (
                student_id, first_name, middle_name, last_name, extension_name, gender, birthday, contact, email, address,
                last_school, school_address, date_attended, honors_received,
                father_name, father_occupation, mother_name, mother_occupation, guardian_name, guardian_contact, student_image
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssssssssssssssss",
                $student_id,
                $first_name,
                $middle_name,
                $last_name,
                $extension_name,
                $gender,
                $birthday,
                $contact,
                $email,
                $address,
                $last_school,
                $school_address,
                $date_attended,
                $honors_received,
                $father_name,
                $father_occupation,
                $mother_name,
                $mother_occupation,
                $guardian_name,
                $guardian_contact,
                $image_name
            );

            if ($stmt->execute()) {
                // Create user account
                $username = $student_id;
                $password = date("mdY", strtotime($birthday));
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $user_sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'student')";
                $user_stmt = $conn->prepare($user_sql);
                $user_stmt->bind_param("ss", $username, $hashed_password);

                if ($user_stmt->execute()) {
                    $message = "Student registered successfully.";
                    $modalType = "success";

                    // ===== ADD LOG: Student signed up =====
                    addLog($conn, $student_id, 'student', "New student account created via signup.");
                } else {
                    $message = "Student saved, but failed to create login account: " . $user_stmt->error;
                    $modalType = "warning";
                }

                $user_stmt->close();
            } else {
                $message = "Error: " . $stmt->error;
                $modalType = "error";
            }

            $stmt->close();
        }
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>STUDENT SIGNUP</title>
    <link rel="stylesheet" href="signup.css">
    <link rel="icon" href="./img/logo.jpg">
</head>

<body>
    <?php if (!empty($message)): ?>
        <div class="modal <?php echo $modalType; ?>">
            <?php echo $message; ?>
        </div>
        <?php if ($modalType === "success"): ?>
            <script>
                setTimeout(function() {
                    window.location.href = "login.php";
                }, 2000);
            </script>
        <?php endif; ?>
    <?php endif; ?>
    <div class="signup-container">
        <h2>RSASHS Student Sign-Up Form</h2>
        <form action="#" method="POST" enctype="multipart/form-data">

            <fieldset>
                <legend>Personal Information</legend>
                <div class="form-row">
                    <label>Student ID:</label>
                    <input type="text" name="student_id" required>
                    <div class="form-col">

                        <label>First Name:</label>
                        <input type="text" name="first_name" required>

                        <label>Middle Name:</label>
                        <input type="text" name="middle_name" required>

                        <label>Gender:</label>
                        <select name="gender" required>
                            <option value="" disabled selected>Select Gender</option>
                            <option>Male</option>
                            <option>Female</option>
                        </select>

                        <label>Contact Number:</label>
                        <input type="text" name="contact" required>
                    </div>

                    <div class="form-col">
                        <label>Last Name:</label>
                        <input type="text" name="last_name" required>

                        <label>Extension Name:</label>
                        <input type="text" name="extension_name">

                        <label>Birthday:</label>
                        <input type="date" name="birthday" required>

                        <label>Email Address:</label>
                        <input type="email" name="email" required>
                    </div>

                </div>

                <label>Address:</label>
                <textarea name="address" rows="3" required></textarea>

                <div class="photo-upload">
                    <label>Upload Photo:</label>
                    <input type="file" name="student_image" accept="image/*" onchange="previewPhoto(event)" required>
                    <div class="photo-preview">
                        <img id="photoPreview" alt="Preview">
                    </div>
                </div>

            </fieldset>


            <!-- Educational Background -->
            <fieldset>
                <legend>Educational Background</legend>
                <div class="form-row">
                    <div class="form-col">
                        <label>Last School Attended:</label>
                        <input type="text" name="last_school" required>

                        <label>Date of Attendance:</label>
                        <input type="text" name="date_attended" required>
                    </div>

                    <div class="form-col">
                        <label>School Address:</label>
                        <input type="text" name="school_address" required>

                        <label>Honors Received:</label>
                        <input type="text" name="honors_received">
                    </div>
                </div>
            </fieldset>

            <!-- Family Information -->
            <fieldset>
                <legend>Family Information</legend>
                <div class="form-row">
                    <div class="form-col">
                        <label>Father's Name:</label>
                        <input type="text" name="father_name">
                        <label>Occupation:</label>
                        <input type="text" name="father_occupation">
                    </div>

                    <div class="form-col">
                        <label>Mother's Name:</label>
                        <input type="text" name="mother_name">
                        <label>Occupation:</label>
                        <input type="text" name="mother_occupation">
                    </div>
                </div>

                <label>Guardian's Name (if not parents):</label>
                <input type="text" name="guardian_name">

                <label>Parent/Guardian Contact:</label>
                <input type="text" name="guardian_contact">
            </fieldset>

            <button type="submit">Sign Up</button>
            <div class="signup-link">
                Already have an account? <a href="login.php">Log in</a>
            </div>
        </form>
    </div>
    <script>
        function previewPhoto(event) {
            const preview = document.getElementById('photoPreview');
            const file = event.target.files[0];

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = "block";
            } else {
                preview.src = "";
                preview.style.display = "none";
            }
        }
    </script>


</body>

</html>