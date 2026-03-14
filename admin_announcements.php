<?php
include 'connect.php';
include("functions.php");
session_start();

if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('You must be logged in as admin.'); window.location='login.php';</script>";
    exit();
}

date_default_timezone_set('Asia/Manila');
mysqli_query($conn, "SET time_zone = '+08:00'");

$col_check = mysqli_query($conn, "SHOW COLUMNS FROM announcements LIKE 'scheduled_at'");
if (mysqli_num_rows($col_check) === 0) {
    mysqli_query($conn, "ALTER TABLE announcements ADD COLUMN scheduled_at DATETIME NULL DEFAULT NULL");
}

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

/* ── PUBLISH NOW ── */
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
        $status       = 'scheduled';
        $raw_dt       = $_POST['scheduled_at'];
        $scheduled_at = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $raw_dt)));
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

/* ── PAGINATION ── */
$records_per_page = 5;

$page_pub = isset($_GET['page_pub']) && is_numeric($_GET['page_pub']) ? max(1, (int)$_GET['page_pub']) : 1;
$page_sch = isset($_GET['page_sch']) && is_numeric($_GET['page_sch']) ? max(1, (int)$_GET['page_sch']) : 1;

// Published counts & data
$pub_count_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM announcements WHERE status='published'");
$pub_count     = (int)mysqli_fetch_assoc($pub_count_res)['total'];
$pub_pages     = $pub_count > 0 ? ceil($pub_count / $records_per_page) : 1;
$pub_offset    = ($page_pub - 1) * $records_per_page;
$published     = mysqli_query($conn, "SELECT * FROM announcements WHERE status='published' ORDER BY created_at DESC LIMIT $records_per_page OFFSET $pub_offset");

// Scheduled counts & data
$sch_count_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM announcements WHERE status='scheduled'");
$sch_count     = (int)mysqli_fetch_assoc($sch_count_res)['total'];
$sch_pages     = $sch_count > 0 ? ceil($sch_count / $records_per_page) : 1;
$sch_offset    = ($page_sch - 1) * $records_per_page;
$scheduled     = mysqli_query($conn, "SELECT * FROM announcements WHERE status='scheduled' ORDER BY scheduled_at ASC LIMIT $records_per_page OFFSET $sch_offset");

// Pagination URL helper
function annPaginationUrl($param, $page)
{
    $params = array_merge($_GET, [$param => $page]);
    return '?' . http_build_query($params);
}

function renderAnnPagination($current_page, $total_pages, $param, $tab)
{
    if ($total_pages <= 1) return '';
    $window = 2;
    $start  = max(1, $current_page - $window);
    $end    = min($total_pages, $current_page + $window);

    $html = '<div class="pagination-wrapper"><ul class="pagination">';

    $html .= $current_page <= 1
        ? '<li class="disabled"><span><i class="fas fa-chevron-left"></i></span></li>'
        : '<li><a href="' . annPaginationUrl($param, $current_page - 1) . '#' . $tab . '"><i class="fas fa-chevron-left"></i></a></li>';

    if ($start > 1) {
        $html .= '<li><a href="' . annPaginationUrl($param, 1) . '#' . $tab . '">1</a></li>';
        if ($start > 2) $html .= '<li class="disabled"><span>&hellip;</span></li>';
    }

    for ($i = $start; $i <= $end; $i++) {
        $html .= $i === $current_page
            ? '<li class="active"><span>' . $i . '</span></li>'
            : '<li><a href="' . annPaginationUrl($param, $i) . '#' . $tab . '">' . $i . '</a></li>';
    }

    if ($end < $total_pages) {
        if ($end < $total_pages - 1) $html .= '<li class="disabled"><span>&hellip;</span></li>';
        $html .= '<li><a href="' . annPaginationUrl($param, $total_pages) . '#' . $tab . '">' . $total_pages . '</a></li>';
    }

    $html .= $current_page >= $total_pages
        ? '<li class="disabled"><span><i class="fas fa-chevron-right"></i></span></li>'
        : '<li><a href="' . annPaginationUrl($param, $current_page + 1) . '#' . $tab . '"><i class="fas fa-chevron-right"></i></a></li>';

    $html .= '</ul></div>';
    return $html;
}

include 'admin_announcements_view.php';
