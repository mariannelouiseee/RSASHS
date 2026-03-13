<?php
include 'connect.php';
include("functions.php");
session_start();

if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('You must be logged in as admin.'); window.location='login.php';</script>";
    exit();
}

/* ── FIX: Sync PHP and MySQL timezones ── */
date_default_timezone_set('Asia/Manila'); // change to your timezone if different
mysqli_query($conn, "SET time_zone = '+08:00'"); // match your UTC offset

/* ── Ensure scheduled_at column exists (safe one-time migration) ── */
$col_check = mysqli_query($conn, "SHOW COLUMNS FROM announcements LIKE 'scheduled_at'");
if (mysqli_num_rows($col_check) === 0) {
    mysqli_query($conn, "ALTER TABLE announcements ADD COLUMN scheduled_at DATETIME NULL DEFAULT NULL");
}

/* ── AUTO-PUBLISH scheduled announcements that are due ── */
mysqli_query(
    $conn,
    "UPDATE announcements
     SET status = 'published', scheduled_at = NULL
     WHERE status = 'scheduled'
       AND scheduled_at IS NOT NULL
       AND scheduled_at <= NOW()"
);

/* ── DELETE ── */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $img_res = mysqli_query($conn, "SELECT image FROM announcements WHERE id=$id");
    if ($img_res && $img_row = mysqli_fetch_assoc($img_res)) {
        if (!empty($img_row['image']) && file_exists('uploads/' . $img_row['image']))
            unlink('uploads/' . $img_row['image']);
    }
    mysqli_query($conn, "DELETE FROM announcements WHERE id=$id");
    header("Location: admin_announcements.php");
    exit;
}

/* ── PUBLISH NOW (force a scheduled post live) ── */
if (isset($_GET['publish_now'])) {
    $id = (int)$_GET['publish_now'];
    mysqli_query($conn, "UPDATE announcements SET status='published', scheduled_at=NULL WHERE id=$id");
    header("Location: admin_announcements.php");
    exit;
}

/* ── CREATE / UPDATE ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title']);
    $message      = trim($_POST['message']);
    $schedule_opt = $_POST['schedule_type'] ?? 'now';
    $image_name   = null;

    if ($schedule_opt === 'schedule' && !empty($_POST['scheduled_at'])) {
        $status = 'scheduled';
        /* FIX: Convert "2025-01-15T14:30" → "2025-01-15 14:30:00" for MySQL */
        $raw_dt       = $_POST['scheduled_at'];
        $scheduled_at = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $raw_dt)));

        /* Safety check: if the chosen time is already in the past, publish immediately */
        if (strtotime($scheduled_at) <= time()) {
            $status       = 'published';
            $scheduled_at = null;
        }
    } else {
        $status       = 'published';
        $scheduled_at = null;
    }

    if (!empty($_FILES['image']['name'])) {
        $file       = $_FILES['image'];
        $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
        $image_name = uniqid('announcement_', true) . '.' . $ext;
        move_uploaded_file($file['tmp_name'], 'uploads/' . $image_name);
    }

    if (!empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        if ($image_name) {
            $old_res = mysqli_query($conn, "SELECT image FROM announcements WHERE id=$id");
            $old_row = mysqli_fetch_assoc($old_res);
            if (!empty($old_row['image']) && file_exists('uploads/' . $old_row['image']))
                unlink('uploads/' . $old_row['image']);
            $sql  = "UPDATE announcements SET title=?, message=?, status=?, scheduled_at=?, image=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssi", $title, $message, $status, $scheduled_at, $image_name, $id);
        } else {
            $sql  = "UPDATE announcements SET title=?, message=?, status=?, scheduled_at=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssi", $title, $message, $status, $scheduled_at, $id);
        }
        mysqli_stmt_execute($stmt);
    } else {
        $sql  = "INSERT INTO announcements (title, message, status, scheduled_at, image) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $title, $message, $status, $scheduled_at, $image_name);
        mysqli_stmt_execute($stmt);
    }

    $action_label = ($status === 'scheduled') ? "Scheduled Announcement: $title" : "Posted Announcement: $title";
    addLog($conn, $_SESSION['admin_id'], 'admin', $action_label);

    header("Location: admin_announcements.php");
    exit;
}

/* ── EDIT prefill ── */
$editData = null;
if (isset($_GET['edit'])) {
    $id       = (int)$_GET['edit'];
    $res      = mysqli_query($conn, "SELECT * FROM announcements WHERE id=$id");
    $editData = mysqli_fetch_assoc($res);
}

/* ── FETCH lists ── */
$published = mysqli_query($conn, "SELECT * FROM announcements WHERE status='published' ORDER BY created_at DESC");
$scheduled = mysqli_query($conn, "SELECT * FROM announcements WHERE status='scheduled' ORDER BY scheduled_at ASC");
$pub_count = $published ? mysqli_num_rows($published) : 0;
$sch_count = $scheduled ? mysqli_num_rows($scheduled) : 0;

include 'admin_announcements_view.php';
