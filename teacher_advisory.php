<?php
include("connect.php");
session_start();

if (!isset($_SESSION['teacher_id'])) {
    echo "<script>alert('You must be logged in as teacher.'); window.location='login.php';</script>";
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

$stmt_sec = $conn->prepare("
    SELECT section_id, year_level, section_name, school_year, grades_visible
    FROM sections
    WHERE adviser_id = ?
    ORDER BY year_level, section_name
");
$stmt_sec->bind_param("s", $teacher_id);
$stmt_sec->execute();
$result_sec = $stmt_sec->get_result();

$sections = [];
while ($sec = $result_sec->fetch_assoc()) $sections[] = $sec;
$stmt_sec->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Advisory Class</title>
    <link rel="stylesheet" href="teacher.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
    <style>
        /* ===== TOGGLE SWITCH ===== */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 24px;
            flex-shrink: 0;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: #ccc;
            border-radius: 24px;
            transition: .3s;
        }

        .slider:before {
            content: "";
            position: absolute;
            width: 18px;
            height: 18px;
            left: 3px;
            bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: .3s;
        }

        input:checked+.slider {
            background: #4caf50;
        }

        input:checked+.slider:before {
            transform: translateX(22px);
        }

        /* ===== VISIBILITY BANNER inside card ===== */
        .visibility-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            border-bottom: 1px solid #f0f4f0;
            background: #fafffe;
            gap: 12px;
            flex-wrap: wrap;
        }

        .visibility-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        .visibility-info i {
            font-size: 13px;
        }

        .vis-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .vis-status.visible {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .vis-status.hidden {
            background: #fff8e1;
            color: #f57f17;
            border: 1px solid #ffe082;
        }

        .toggle-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toggle-hint {
            font-size: 11px;
            color: #90a4ae;
            font-weight: 500;
        }

        /* ===== STUDENT TABLE inside card ===== */
        .advisory-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 420px;
        }

        .advisory-table thead th {
            background: #f6faf6;
            color: #4a7c59;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 11px 16px;
            text-align: left;
            border-bottom: 1px solid #e8f0e8;
        }

        .advisory-table thead th.col-action {
            text-align: center;
        }

        .advisory-table tbody tr {
            border-bottom: 1px solid #f5faf5;
            transition: background 0.1s;
        }

        .advisory-table tbody tr:last-child {
            border-bottom: none;
        }

        .advisory-table tbody tr:hover td {
            background: #f9fffe;
        }

        .advisory-table td {
            padding: 11px 16px;
            font-size: 13px;
            color: #2e3a2f;
            vertical-align: middle;
        }

        .advisory-table td.col-id {
            font-weight: 600;
            color: #546e7a;
            font-size: 12px;
        }

        .advisory-table td.col-name {
            font-weight: 600;
            color: #1b3a1f;
        }

        .advisory-table td.col-action {
            text-align: center;
        }

        .col-id {
            width: 25%;
        }

        .col-name {
            width: 45%;
        }

        .col-action {
            width: 30%;
        }

        /* Print button */
        .btn-print-card {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #fff;
            border: 1.5px solid #c8e6c9;
            color: #2e7d32;
            padding: 6px 13px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.15s, border-color 0.15s, transform 0.1s;
            white-space: nowrap;
        }

        .btn-print-card:hover {
            background: #e8f5e9;
            border-color: #2e7d32;
            transform: translateY(-1px);
        }

        .btn-print-card i {
            font-size: 11px;
        }
    </style>
</head>

<body>

    <!-- ===== HEADER ===== -->
    <header>
        <div class="header-left">
            <img src="./img/logo.jpg" alt="RSASHS Logo">
            <h2>RSASHS E-PORTAL</h2>
        </div>
        <button id="menuToggle" class="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <nav class="navbar" id="navbarLinks">
            <ul>
                <li><a href="teacher_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="teacher_advisory.php" class="active"><i class="fas fa-users"></i> Advisory Class</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- ===== PAGE WRAPPER ===== -->
    <div class="page-wrap">

        <div class="page-title">Advisory Class</div>

        <?php if (empty($sections)): ?>
            <div class="empty-state">
                <i class="fas fa-user-slash"></i>
                <p>You are not assigned as an adviser to any section.</p>
            </div>
        <?php else: ?>
            <?php foreach ($sections as $sec): ?>

                <!-- Section group label -->
                <div class="section-group-label">
                    <span class="sgb-pill">
                        <i class="fas fa-layer-group"></i>
                        <?= htmlspecialchars($sec['year_level']) ?>
                    </span>
                    <span class="sgb-sep">&mdash;</span>
                    <span class="sgb-section"><?= htmlspecialchars($sec['section_name']) ?></span>
                    <span class="sgb-year">S.Y. <?= htmlspecialchars($sec['school_year']) ?></span>
                </div>

                <!-- Section card -->
                <div class="table-card" style="margin-bottom:22px;">

                    <!-- Grade visibility bar -->
                    <div class="visibility-bar">
                        <div class="visibility-info">
                            <i class="fas fa-eye" style="color:#4caf50;"></i>
                            Grade Visibility for Students:
                            <span class="vis-status <?= $sec['grades_visible'] ? 'visible' : 'hidden' ?>"
                                id="vis-status-<?= $sec['section_id'] ?>">
                                <i class="fas fa-<?= $sec['grades_visible'] ? 'check-circle' : 'lock' ?>"></i>
                                <?= $sec['grades_visible'] ? 'Visible' : 'Hidden' ?>
                            </span>
                        </div>
                        <div class="toggle-row">
                            <span class="toggle-hint">Toggle to release grades</span>
                            <label class="toggle-switch" title="Toggle grade visibility for students">
                                <input type="checkbox" class="grade-toggle"
                                    data-section-id="<?= $sec['section_id'] ?>"
                                    <?= $sec['grades_visible'] ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Student list table -->
                    <div class="table-scroll">
                        <table class="advisory-table">
                            <thead>
                                <tr>
                                    <th class="col-id">Student ID</th>
                                    <th class="col-name">Full Name</th>
                                    <th class="col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt_stu = $conn->prepare("
                                    SELECT student_id, CONCAT(first_name,' ',last_name) AS name
                                    FROM students
                                    WHERE year_level=? AND section_name=? AND school_year=?
                                    ORDER BY last_name, first_name
                                ");
                                $stmt_stu->bind_param("sss", $sec['year_level'], $sec['section_name'], $sec['school_year']);
                                $stmt_stu->execute();
                                $res_stu = $stmt_stu->get_result();

                                if ($res_stu->num_rows > 0):
                                    while ($stu = $res_stu->fetch_assoc()):
                                ?>
                                        <tr>
                                            <td class="col-id"><?= htmlspecialchars($stu['student_id']) ?></td>
                                            <td class="col-name"><?= htmlspecialchars($stu['name']) ?></td>
                                            <td class="col-action">
                                                <a class="btn-print-card"
                                                    href="print_report_card.php?student_id=<?= urlencode($stu['student_id']) ?>&year_level=<?= urlencode($sec['year_level']) ?>&section_name=<?= urlencode($sec['section_name']) ?>&school_year=<?= urlencode($sec['school_year']) ?>"
                                                    target="_blank">
                                                    <i class="fas fa-print"></i> Print Report Card
                                                </a>
                                            </td>
                                        </tr>
                                    <?php
                                    endwhile;
                                else:
                                    ?>
                                    <tr>
                                        <td colspan="3" style="text-align:center; color:#90a4ae; padding:20px;">
                                            No students assigned to this section.
                                        </td>
                                    </tr>
                                <?php
                                endif;
                                $stmt_stu->close();
                                ?>
                            </tbody>
                        </table>
                    </div>

                </div><!-- .table-card -->

            <?php endforeach; ?>
        <?php endif; ?>

    </div><!-- .page-wrap -->

    <script>
        document.getElementById("menuToggle").addEventListener("click", () => {
            document.querySelector("#navbarLinks ul").classList.toggle("active");
        });

        // Grade visibility toggle
        document.querySelectorAll('.grade-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const sectionId = this.dataset.sectionId;
                const state = this.checked ? 1 : 0;
                const statusEl = document.getElementById('vis-status-' + sectionId);
                const checkbox = this;

                if (state === 1) {
                    const confirmed = confirm('Are you sure you want to make grades visible to students?');
                    if (!confirmed) {
                        checkbox.checked = false;
                        return;
                    }
                }

                fetch('toggle_grades.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `section_id=${sectionId}&state=${state}`
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            if (state) {
                                statusEl.className = 'vis-status visible';
                                statusEl.innerHTML = '<i class="fas fa-check-circle"></i> Visible';
                            } else {
                                statusEl.className = 'vis-status hidden';
                                statusEl.innerHTML = '<i class="fas fa-lock"></i> Hidden';
                            }
                        } else {
                            alert('Failed to update. Please try again.');
                            checkbox.checked = !checkbox.checked;
                        }
                    })
                    .catch(() => {
                        alert('Connection error. Please try again.');
                        checkbox.checked = !checkbox.checked;
                    });
            });
        });

        // Inactivity logout
        (function() {
            const INACTIVITY_LIMIT = 5 * 60 * 1000,
                WARNING_TIME = 10 * 1000;
            let inactivityTimer, warningTimer;

            function resetTimer() {
                clearTimeout(inactivityTimer);
                clearTimeout(warningTimer);
                warningTimer = setTimeout(showWarning, INACTIVITY_LIMIT - WARNING_TIME);
                inactivityTimer = setTimeout(logoutUser, INACTIVITY_LIMIT);
            }

            function showWarning() {
                if (document.getElementById('inactivityWarning')) return;
                const d = document.createElement('div');
                d.id = 'inactivityWarning';
                Object.assign(d.style, {
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    width: '320px',
                    background: '#f1f8e9',
                    border: '2px solid #2e7d32',
                    borderRadius: '12px',
                    padding: '20px',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.25)',
                    color: '#2e3a2f',
                    fontFamily: '"Segoe UI",Tahoma,Geneva,Verdana,sans-serif',
                    fontSize: '14px',
                    lineHeight: '1.4',
                    display: 'flex',
                    flexDirection: 'column',
                    gap: '10px',
                    opacity: 0,
                    transition: 'opacity 0.5s ease',
                    zIndex: 10000
                });
                d.innerHTML = `
                    <strong style="font-size:16px;color:#1b5e20;">Inactivity Warning</strong>
                    <span>You will be logged out in <span id="countdown">10</span> seconds.</span>
                    <button id="stayLoggedIn" style="padding:8px 12px;background:#2e7d32;color:white;border:none;border-radius:6px;font-weight:bold;cursor:pointer;align-self:flex-end;">Stay Logged In</button>`;
                document.body.appendChild(d);
                setTimeout(() => d.style.opacity = 1, 10);
                let c = 10;
                const span = document.getElementById('countdown');
                const iv = setInterval(() => {
                    if (--c <= 0) clearInterval(iv);
                    span.textContent = c;
                }, 1000);
                document.getElementById('stayLoggedIn').addEventListener('click', () => {
                    clearInterval(iv);
                    d.style.opacity = 0;
                    setTimeout(() => d.remove(), 300);
                    resetTimer();
                });
            }

            function logoutUser() {
                fetch('auto_logout.php', {
                        method: 'POST',
                        credentials: 'same-origin'
                    })
                    .then(r => r.json())
                    .then(d => {
                        alert(d.message || 'Logged out.');
                        window.location.href = 'login.php';
                    })
                    .catch(() => {
                        window.location.href = 'login.php';
                    });
            }

            ['mousemove', 'keydown', 'mousedown', 'touchstart'].forEach(e => document.addEventListener(e, resetTimer));
            resetTimer();
        })();
    </script>

</body>

</html>