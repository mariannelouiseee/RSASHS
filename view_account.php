<?php
include("connect.php");
include("functions.php");
session_start();

$id = $_GET['id'] ?? '';
$role = $_GET['role'] ?? '';

if (!$id || !$role) {
    die("Missing required parameters.");
}

if ($role === 'student') {
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id=? LIMIT 1");
    $stmt->bind_param("s", $id);
} elseif ($role === 'teacher') {
    $stmt = $conn->prepare("SELECT * FROM teachers WHERE teacher_id=? LIMIT 1");
    $stmt->bind_param("s", $id);
} else {
    die("Invalid role.");
}

$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();

    // ✅ Log admin viewing account
    if (isset($_SESSION['admin_id'])) {
        addLog($conn, $_SESSION['admin_id'], 'admin', "Viewed $role account: $id");
    }

    if ($role === 'student') {
        echo "<div class='profile-section'>";
        $image = !empty($row['student_image']) ? $row['student_image'] : "default.png";
        echo "<img src='uploads/" . htmlspecialchars($image) . "' alt='Student Photo' class='profile-pic'>";
        echo "</div>";

        echo "<h4>Student Information</h4>";
        echo "<table class='view-table'>";
        echo "<tr><th>ID</th><td>" . htmlspecialchars($row['student_id']) . "</td></tr>";
        echo "<tr><th>Full Name</th><td>" . htmlspecialchars($row['first_name'] . " " . $row['middle_name'] . " " . $row['last_name'] . " " . $row['extension_name']) . "</td></tr>";
        echo "<tr><th>Gender</th><td>" . htmlspecialchars($row['gender']) . "</td></tr>";
        echo "<tr><th>Birthday</th><td>" . htmlspecialchars($row['birthday']) . "</td></tr>";
        echo "<tr><th>Contact</th><td>" . htmlspecialchars($row['contact']) . "</td></tr>";
        echo "<tr><th>Email</th><td>" . htmlspecialchars($row['email']) . "</td></tr>";
        echo "<tr><th>Address</th><td>" . htmlspecialchars($row['address']) . "</td></tr>";
        echo "</table>";

        echo "<h4>Academic Information</h4>";
        echo "<table class='view-table'>";
        echo "<tr><th>Year Level</th><td>" . htmlspecialchars($row['year_level']) . "</td></tr>";
        echo "<tr><th>Section</th><td>" . htmlspecialchars($row['section_name']) . "</td></tr>";
        echo "<tr><th>School Year</th><td>" . htmlspecialchars($row['school_year']) . "</td></tr>";
        echo "<tr><th>Last School</th><td>" . htmlspecialchars($row['last_school']) . "</td></tr>";
        echo "<tr><th>School Address</th><td>" . htmlspecialchars($row['school_address']) . "</td></tr>";
        echo "<tr><th>Date Attended</th><td>" . htmlspecialchars($row['date_attended']) . "</td></tr>";
        echo "<tr><th>Honors Received</th><td>" . htmlspecialchars($row['honors_received']) . "</td></tr>";
        echo "</table>";

        echo "<h4>Family Information</h4>";
        echo "<table class='view-table'>";
        echo "<tr><th>Father</th><td>" . htmlspecialchars($row['father_name']) . " (" . htmlspecialchars($row['father_occupation']) . ")</td></tr>";
        echo "<tr><th>Mother</th><td>" . htmlspecialchars($row['mother_name']) . " (" . htmlspecialchars($row['mother_occupation']) . ")</td></tr>";
        echo "<tr><th>Guardian</th><td>" . htmlspecialchars($row['guardian_name']) . " (" . htmlspecialchars($row['guardian_contact']) . ")</td></tr>";
        echo "</table>";

    } elseif ($role === 'teacher') {
        echo "<div class='profile-section'>";
        $image = !empty($row['teacher_image']) ? $row['teacher_image'] : "teacher_default.png";
        echo "<img src='uploads/" . htmlspecialchars($image) . "' alt='Teacher Photo' class='profile-pic'>";
        echo "</div>";

        echo "<h4>Teacher Information</h4>";
        echo "<table class='view-table'>";
        echo "<tr><th>ID</th><td>" . htmlspecialchars($row['teacher_id']) . "</td></tr>";
        echo "<tr><th>Full Name</th><td>" . htmlspecialchars($row['first_name'] . " " . $row['middle_name'] . " " . $row['last_name']) . "</td></tr>";
        echo "</table>";
    }

} else {
    echo "<p>No details found.</p>";
}

$stmt->close();
?>