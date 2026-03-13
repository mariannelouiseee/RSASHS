<?php
include("connect.php");
include("functions.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $new_password = trim($_POST['new_password']);

    // Hash password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param("ss", $hashed_password, $student_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // ✅ Add activity log
        if (isset($_SESSION['admin_id'])) {
            addLog($conn, $_SESSION['admin_id'], 'admin', "Changed password for account: $student_id");
        } elseif (isset($_SESSION['student_id']) && $_SESSION['student_id'] === $student_id) {
            addLog($conn, $student_id, 'student', "Changed own password");
        }

        echo "<script>
            alert('Password updated successfully!');
            window.location.href='admin_account.php';
        </script>";
    } else {
        echo "<script>
            alert('No rows updated. Check if username exists: $student_id');
            window.location.href='admin_account.php';
        </script>";
    }

    $stmt->close();
}
?>