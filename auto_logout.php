<?php
session_start();
include("connect.php");

$username = $_SESSION['admin_id'] ?? $_SESSION['teacher_id'] ?? $_SESSION['student_id'] ?? '';

if ($username) {
    $stmt = $conn->prepare("UPDATE users SET status = 'logged_out' WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
}

// Destroy session
session_unset();
session_destroy();

echo json_encode(['status' => true, 'message' => 'Logged out due to inactivity.']);
