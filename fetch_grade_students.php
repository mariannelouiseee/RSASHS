<?php
include("connect.php");

$section_id = $_GET['section_id'] ?? '';
$subject = $_GET['subject'] ?? '';

// Base query
$sql = "SELECT s.student_number, s.first_name, s.last_name,
               g.q1, g.q2, g.q3, g.q4, g.final
        FROM students s
        LEFT JOIN grades g ON s.student_number = g.student_number AND g.subject = ?
        WHERE 1";

// Parameters array
$params = [$subject];
$types = "s";

// Add section filter if provided
if ($section_id) {
    $sql .= " AND s.section_id = ?";
    $params[] = $section_id;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode(["students" => $students]);
