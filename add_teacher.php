<?php
include("connect.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);

    $base_username = strtolower(substr($first_name, 0, 1) . $last_name);
    $username = $base_username . rand(100, 999);

    $default_password = 'rsashs123';
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
    $photo_filename = null;
    if (isset($_FILES['teacher_photo']) && $_FILES['teacher_photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $tmp_name = $_FILES['teacher_photo']['tmp_name'];
        $original_name = basename($_FILES['teacher_photo']['name']);
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed_ext)) {
            $photo_filename = $username . '_' . time() . '.' . $ext;
            $destination = $upload_dir . $photo_filename;

            if (!move_uploaded_file($tmp_name, $destination)) {
                echo "Failed to upload photo.";
                exit;
            }
        } else {
            echo "Invalid photo file type. Allowed types: jpg, jpeg, png, gif.";
            exit;
        }
    }

    $stmt_user = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'teacher')");
    $stmt_user->bind_param("ss", $username, $hashed_password);

    if ($stmt_user->execute()) {

        $stmt_teacher = $conn->prepare("INSERT INTO teachers (teacher_id, first_name, middle_name, last_name, teacher_image) VALUES (?, ?, ?, ?, ?)");
        $stmt_teacher->bind_param("sssss", $username, $first_name, $middle_name, $last_name, $photo_filename);

        if ($stmt_teacher->execute()) {
            header("Location: admin_account.php?role=teacher&added=success");
            exit;
        } else {
            echo "Error inserting teacher info: " . $conn->error;
        }
    } else {
        echo "Error inserting user info: " . $conn->error;
    }
} else {
    header("Location: admin_account.php?role=teacher");
    exit;
}
