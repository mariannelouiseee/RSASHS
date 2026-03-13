<?php
function addLog($conn, $user_id, $role, $description)
{
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, role, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $role, $description);
    $stmt->execute();
    $stmt->close();
}
?>