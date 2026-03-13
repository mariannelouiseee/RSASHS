<?php
include("connect.php");
include("functions.php");
session_start(); // Must start session to access admin_id

$section_id = $_GET['section_id'] ?? '';
$subject_id = $_GET['subject_id'] ?? '';  // Needed to fetch grades
$school_year = "2025-2026"; // You can also get this dynamically

if (!$section_id || !$subject_id) {
    echo json_encode(["students" => [], "section_name" => ""]);
    exit();
}

// Fetch section name
$section_stmt = $conn->prepare("SELECT section_name FROM sections WHERE section_id = ?");
$section_stmt->bind_param("i", $section_id);
$section_stmt->execute();
$section_res = $section_stmt->get_result();
$section_data = $section_res->fetch_assoc();
$section_name = $section_data['section_name'] ?? "";

// Fetch students in the section along with their grades for this subject
$stmt = $conn->prepare("
    SELECT s.student_id, s.first_name, s.middle_name, s.last_name, s.extension_name,
           g.q1, g.q2, g.first_sem_final, g.gwa_first_sem,
           g.q3, g.q4, g.second_sem_final, g.gwa_second_sem, g.final
    FROM students s
    LEFT JOIN grades g 
           ON s.student_id = g.student_id 
           AND g.subject_id = ? 
           AND g.school_year = ?
    WHERE s.section_name = ?
    ORDER BY s.last_name, s.first_name
");
$stmt->bind_param("iss", $subject_id, $school_year, $section_name);
$stmt->execute();
$res = $stmt->get_result();

$students = [];
while ($row = $res->fetch_assoc()) {
    // Construct full name with middle initial and extension
    $middle_initial = !empty($row['middle_name']) ? strtoupper($row['middle_name'][0]) . "." : "";
    $ext = !empty($row['extension_name']) ? " " . $row['extension_name'] : "";
    $full_name = strtoupper($row['first_name'] . " " . $middle_initial . " " . $row['last_name'] . $ext);

    $students[] = [
        "student_id" => $row['student_id'],
        "full_name" => $full_name,
        "q1" => $row['q1'] ?? "-",
        "q2" => $row['q2'] ?? "-",
        "first_sem_final" => $row['first_sem_final'] ?? "-",
        "gwa_first_sem" => $row['gwa_first_sem'] ?? "-",
        "q3" => $row['q3'] ?? "-",
        "q4" => $row['q4'] ?? "-",
        "second_sem_final" => $row['second_sem_final'] ?? "-",
        "gwa_second_sem" => $row['gwa_second_sem'] ?? "-",
        "final" => $row['final'] ?? "-"
    ];
}

// --- Add log for admin ---
if (isset($_SESSION['admin_id'])) {
    $student_count = count($students);
    addLog(
        $conn,
        $_SESSION['admin_id'],
        'admin',
        "Viewed students and grades of section ID: $section_id ($section_name) for subject ID: $subject_id - $student_count student(s) retrieved"
    );
}

echo json_encode([
    "students" => $students,
    "section_name" => $section_name
]);
