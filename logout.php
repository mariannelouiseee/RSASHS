<?php
session_start();
include("connect.php");
include("functions.php");

// Get current user
$username = $_SESSION['admin_id'] ?? $_SESSION['teacher_id'] ?? $_SESSION['student_id'] ?? '';
$role = $_SESSION['role'] ?? 'unknown';

// Update status to logged_out
if ($username) {
    $stmt = $conn->prepare("UPDATE users SET status = 'logged_out' WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();

    addLog($conn, $username, $role, 'Logged out');
}

// Destroy session completely
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
