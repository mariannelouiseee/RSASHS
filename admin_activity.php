<?php
include("connect.php");
include("functions.php");

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('You must be logged in as admin.'); window.location='login.php';</script>";
    exit();
}

// ===== AUTO-CLEANUP: Keep only the latest 250 logs =====
$LOG_LIMIT = 250;
$count_res = $conn->query("SELECT COUNT(*) AS total FROM activity_logs")->fetch_assoc();
if ((int)$count_res['total'] >= $LOG_LIMIT) {
    // Delete oldest logs beyond the limit, keeping the newest 249 to make room for the new entry
    $keep = $LOG_LIMIT - 1;
    $conn->query("DELETE FROM activity_logs WHERE id NOT IN (
        SELECT id FROM (
            SELECT id FROM activity_logs ORDER BY created_at DESC LIMIT $keep
        ) AS latest
    )");
}

// Log page visit (inserted after cleanup so it counts as the newest entry)
addLog($conn, $_SESSION['admin_id'], 'admin', "Visited activity logs page");

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Get logs
$result = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT $start, $limit");
$total_res = $conn->query("SELECT COUNT(*) AS total FROM activity_logs")->fetch_assoc();
$total_logs = $total_res['total'];
$total_pages = ceil($total_logs / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs</title>
    <link rel="stylesheet" href="admin.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
</head>

<body>
    <header>
        <div class="header-left">
            <img src="./img/logo.jpg" alt="RSASHS Logo" />
            <h2>RSASHS E-PORTAL</h2>
        </div>
        <button id="sidebarToggle" class="sidebar-toggle"><i class="fas fa-bars"></i></button>
    </header>

    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_activity.php" class="active"><i class="fas fa-clock"></i> Activity Logs</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="dashboard">
            <h2>Activity Logs</h2>
            <p style="font-size:13px; color:#777; margin-bottom:12px;">
                Showing <?= $total_logs ?> / <?= $LOG_LIMIT ?> logs &mdash; oldest entries are removed automatically once the limit is reached.
            </p>
            <div class="activity full-log">
                <table>
                    <thead>
                        <tr>
                            <th>User Role</th>
                            <th>Description</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['role']); ?></td>
                                    <td><?= htmlspecialchars($row['description']); ?></td>
                                    <td><?= date("M d, Y H:i", strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No logs found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            </div>
        </main>
    </div>
</body>

</html>