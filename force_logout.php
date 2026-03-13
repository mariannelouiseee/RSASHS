<?php
session_start();
include("connect.php");
include("functions.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['username'])) {
    $username = $_POST['username'];

    // Force logout by updating status
    $stmt = $conn->prepare("UPDATE users SET status='logged_out' WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();

    addLog($conn, $username, 'unknown', 'Forced logout from another session');

    // Redirect back to login page
    header("Location: login.php");
    exit();
}
