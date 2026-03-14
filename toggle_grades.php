<?php
include("connect.php");
session_start();

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$section_id = intval($_POST['section_id']);
$state = intval($_POST['state']);

$stmt = $conn->prepare("UPDATE sections SET grades_visible = ? WHERE section_id = ?");
$stmt->bind_param("ii", $state, $section_id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'state' => $state]);
