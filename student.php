<?php
include("connect.php");
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $student = $result->fetch_assoc();
} else {
    echo "Student record not found.";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Profile</title>
    <link rel="stylesheet" href="student.css">
</head>

<body>
    <nav class="navbar">
        <div class="navbar-wrapper">
            <div class="navbar-left">
                <h1 class="navbar-logo">RSASHS E-Portal</h1>
                <span class="navbar-greeting">Hi <?= htmlspecialchars($student['first_name']) ?>!</span>
            </div>

            <button class="menu-toggle" id="menuToggle">&#9776;</button>

            <ul class="navbar-links" id="navbarLinks">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="student_profile.php" class="active">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="profile-container">
        <h2>Student Profile</h2>

        <div class="profile-section">
            <h3>Personal Information</h3>
            <div class="profile-grid">
                <p><strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']) ?></p>
                <p><strong>Name:</strong>
                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name'] . ' ' . $student['extension_name']) ?>
                </p>
                <p><strong>Gender:</strong> <?= htmlspecialchars($student['gender']) ?></p>
                <p><strong>Birthday:</strong> <?= htmlspecialchars($student['birthday']) ?></p>
                <p><strong>Contact:</strong> <?= htmlspecialchars($student['contact']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($student['address']) ?></p>
            </div>
        </div>

        <div class="profile-section">
            <h3>Educational Background</h3>
            <div class="profile-grid">
                <p><strong>Last School Attended:</strong> <?= htmlspecialchars($student['last_school']) ?></p>
                <p><strong>School Address:</strong> <?= htmlspecialchars($student['school_address']) ?></p>
                <p><strong>Date Attended:</strong> <?= htmlspecialchars($student['date_attended']) ?></p>
                <p><strong>Honors Received:</strong> <?= htmlspecialchars($student['honors_received']) ?></p>
            </div>
        </div>

        <div class="profile-section">
            <h3>Family Information</h3>
            <div class="profile-grid">
                <p><strong>Father's Name:</strong> <?= htmlspecialchars($student['father_name']) ?></p>
                <p><strong>Father's Occupation:</strong> <?= htmlspecialchars($student['father_occupation']) ?></p>
                <p><strong>Mother's Name:</strong> <?= htmlspecialchars($student['mother_name']) ?></p>
                <p><strong>Mother's Occupation:</strong> <?= htmlspecialchars($student['mother_occupation']) ?></p>
                <p><strong>Guardian's Name:</strong> <?= htmlspecialchars($student['guardian_name']) ?></p>
                <p><strong>Guardian Contact:</strong> <?= htmlspecialchars($student['guardian_contact']) ?></p>
            </div>
        </div>
    </div>
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const navbarLinks = document.getElementById('navbarLinks');

        menuToggle.addEventListener('click', () => {
            navbarLinks.classList.toggle('active');
        });
    </script>



</body>

</html>