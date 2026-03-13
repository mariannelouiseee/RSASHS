<?php
include("connect.php");
include("functions.php");
session_start(); // Needed to access admin_id for logging

$year_level = $_GET['year_level'] ?? '';
$section_name = $_GET['section_name'] ?? '';

$stmt = $conn->prepare("
    SELECT ss.subject_id 
    FROM section_subjects ss
    JOIN sections s ON ss.section_id = s.section_id
    WHERE s.year_level=? AND s.section_name=?
");
$stmt->bind_param("ss", $year_level, $section_name);
$stmt->execute();
$result = $stmt->get_result();

$subject_ids = [];
while ($row = $result->fetch_assoc()) {
    $subject_ids[] = strval($row['subject_id']); // cast to string
}

// --- Admin log ---
if (isset($_SESSION['admin_id'])) {
    addLog(
        $conn,
        $_SESSION['admin_id'],
        'admin',
        "Viewed subjects for section '$section_name' (Year Level: $year_level) - " . count($subject_ids) . " subject(s) retrieved"
    );
}

echo json_encode($subject_ids);
?>