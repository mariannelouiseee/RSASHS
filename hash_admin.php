<?php
include("connect.php");

// Replace 'admin' with your admin username
$admin_username = 'admin';
$plain_password = 'rsashs123'; // current password in plain text

// Hash the password
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// Update in database
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashed_password, $admin_username);

if ($stmt->execute()) {
    echo "Admin password has been hashed successfully.";
} else {
    echo "Error updating password: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>