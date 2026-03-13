<?php
include("functions.php");
include("connect.php");
session_start(); // Make sure session is started to know who performed the search

$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$role = isset($_GET['role']) ? $conn->real_escape_string($_GET['role']) : 'all';

// Log search action (only if a user is logged in)
if (isset($_SESSION['admin_id'])) {
    addLog($conn, $_SESSION['admin_id'], 'admin', "Searched students with query: '$q', role: '$role'");
} elseif (isset($_SESSION['teacher_id'])) {
    addLog($conn, $_SESSION['teacher_id'], 'teacher', "Searched students with query: '$q', role: '$role'");
} elseif (isset($_SESSION['student_id'])) {
    addLog($conn, $_SESSION['student_id'], 'student', "Searched students with query: '$q', role: '$role'");
}

$sql = "SELECT s.student_id, s.first_name, s.middle_name, s.last_name, s.birthday, s.student_image, u.role
        FROM students s
        JOIN users u ON s.student_id = u.username
        WHERE (s.student_id LIKE '%$q%' 
        OR s.first_name LIKE '%$q%' 
        OR s.middle_name LIKE '%$q%' 
        OR s.last_name LIKE '%$q%')";

if ($role != 'all') {
    $sql .= " AND u.role = '$role'";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>";
        if (!empty($row['student_image'])) {
            echo "<img src='uploads/{$row['student_image']}' width='60'>";
        } else {
            echo "<img src='uploads/default.png' width='60'>";
        }
        echo "</td>
                <td>" . htmlspecialchars($row['student_id']) . "</td>
                <td>" . htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']) . "</td>
                <td>" . date("mdY", strtotime($row['birthday'])) . "</td>
                <td>" . htmlspecialchars($row['role']) . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5'>No results found</td></tr>";
}
?>