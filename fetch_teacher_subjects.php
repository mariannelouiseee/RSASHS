<?php
include("connect.php");
include("functions.php");
session_start(); // Must start session to access admin_id

$teacher_id = $_GET['teacher_id'] ?? '';

if (!$teacher_id) {
    echo json_encode(["teacher_name" => "Unknown", "subjects" => []]);
    exit;
}

// Log admin action
if (isset($_SESSION['admin_id'])) {
    addLog($conn, $_SESSION['admin_id'], 'admin', "Viewed subjects of teacher ID: $teacher_id");
}

// Get teacher full name
$tq = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS teacher_name FROM teachers WHERE teacher_id = ?");
$tq->bind_param("s", $teacher_id);
$tq->execute();
$tres = $tq->get_result()->fetch_assoc();
$teacher_name = $tres ? $tres["teacher_name"] : "Unknown";

// Fetch subjects assigned to the teacher
$sql = "
    SELECT 
        s.section_id,
        s.year_level,
        s.section_name,
        s.strand,
        s.school_year,
        sub.subject_id,
        sub.subject_code,
        sub.subject_name
    FROM section_subjects ss
    INNER JOIN sections s ON ss.section_id = s.section_id
    INNER JOIN subjects sub ON ss.subject_id = sub.subject_id
    WHERE sub.teacher_id = ?
    ORDER BY s.year_level, s.section_name
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = [
        "subject_id" => $row["subject_id"],
        "subject_code" => $row["subject_code"],
        "subject_name" => $row["subject_name"],
        "year_level" => $row["year_level"],
        "section_id" => $row["section_id"],
        "section_name" => $row["section_name"],
        "school_year" => $row["school_year"],
        "status" => "Active"
    ];
}

// --- Admin log of fetched subjects ---
if (isset($_SESSION['admin_id'])) {
    addLog(
        $conn,
        $_SESSION['admin_id'],
        'admin',
        "Fetched " . count($subjects) . " subject(s) for teacher '$teacher_name' (ID: $teacher_id)"
    );
}

echo json_encode([
    "teacher_name" => $teacher_name,
    "subjects" => $subjects
]);
?>