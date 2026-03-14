<?php
include("connect.php");
include("functions.php");
session_start();

if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('You must be logged in as admin.'); window.location='login.php';</script>";
    exit();
}

addLog($conn, $_SESSION['admin_id'], 'admin', "Visited dashboard");

$student_count = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'] ?? 0;
$teacher_count = $conn->query("SELECT COUNT(*) AS total FROM teachers")->fetch_assoc()['total'] ?? 0;
$subject_count = $conn->query("SELECT COUNT(*) AS total FROM subjects")->fetch_assoc()['total'] ?? 0;
$section_count = $conn->query("SELECT COUNT(*) AS total FROM sections")->fetch_assoc()['total'] ?? 0;

$grade_data = [];
$res = $conn->query("SELECT year_level, COUNT(*) AS total FROM students GROUP BY year_level ORDER BY year_level ASC");
while ($row = $res->fetch_assoc()) $grade_data[] = $row;

$activity_data = $conn->query("SELECT description, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li><a href="admin.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-users"></i> Accounts
                        <i class="fas fa-caret-down arrow"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="admin_account.php?role=student"><i class="fas fa-user-graduate"></i> Student</a></li>
                        <li><a href="admin_account.php?role=teacher"><i class="fas fa-chalkboard-teacher"></i> Teacher</a></li>
                    </ul>
                </li>
                <li><a href="admin_subject.php"><i class="fas fa-book-open"></i> Subjects</a></li>
                <li><a href="admin_section.php"><i class="fas fa-layer-group"></i> Section</a></li>
                <li><a href="admin_grade.php"><i class="fas fa-clipboard-list"></i> Grades</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <div id="sidebarOverlay"></div>

        <main class="dashboard">

            <div class="dash-page-title">Dashboard Overview</div>

            <!-- ===== STAT CARDS ===== -->
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-top">
                        <div class="stat-icon" style="background:#e8f5e9; color:#2e7d32;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <span class="stat-badge" style="background:#e8f5e9; color:#2e7d32;"><?= $student_count ?></span>
                    </div>
                    <div class="stat-num"><?= $student_count ?></div>
                    <div class="stat-label">Students</div>
                </div>

                <div class="stat-card">
                    <div class="stat-top">
                        <div class="stat-icon" style="background:#e3f2fd; color:#1565c0;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <span class="stat-badge" style="background:#e3f2fd; color:#1565c0;"><?= $teacher_count ?></span>
                    </div>
                    <div class="stat-num"><?= $teacher_count ?></div>
                    <div class="stat-label">Teachers</div>
                </div>

                <div class="stat-card">
                    <div class="stat-top">
                        <div class="stat-icon" style="background:#fff8e1; color:#f57f17;">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <span class="stat-badge" style="background:#fff8e1; color:#f57f17;"><?= $subject_count ?></span>
                    </div>
                    <div class="stat-num"><?= $subject_count ?></div>
                    <div class="stat-label">Subjects</div>
                </div>

                <div class="stat-card">
                    <div class="stat-top">
                        <div class="stat-icon" style="background:#fce4ec; color:#c62828;">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <span class="stat-badge" style="background:#fce4ec; color:#c62828;"><?= $section_count ?></span>
                    </div>
                    <div class="stat-num"><?= $section_count ?></div>
                    <div class="stat-label">Sections</div>
                </div>
            </div>

            <!-- ===== CHART + ACTIVITY ROW ===== -->
            <div class="dash-row">

                <!-- Chart panel -->
                <div class="dash-panel">
                    <div class="panel-header">
                        <span class="panel-title">
                            <i class="fas fa-chart-bar"></i>
                            Students per Grade Level
                        </span>
                    </div>
                    <div class="panel-body">
                        <canvas id="studentsChart" style="max-height: 220px;"></canvas>
                    </div>
                </div>

                <!-- Activity panel -->
                <div class="dash-panel">
                    <div class="panel-header">
                        <span class="panel-title">
                            <i class="fas fa-clock"></i>
                            Recent Activity
                        </span>
                    </div>
                    <div class="panel-body" style="padding-bottom: 0;">
                        <ul class="activity-list">
                            <?php if ($activity_data && $activity_data->num_rows > 0): ?>
                                <?php while ($log = $activity_data->fetch_assoc()): ?>
                                    <li class="activity-item">
                                        <span class="activity-dot"></span>
                                        <span class="activity-text"><?= htmlspecialchars($log['description']) ?></span>
                                        <span class="activity-time"><?= date("M d, H:i", strtotime($log['created_at'])) ?></span>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li class="activity-item">
                                    <span class="activity-dot" style="background:#ccc;"></span>
                                    <span class="activity-text" style="color:#aaa;">No recent activity.</span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <a href="admin_activity.php" class="panel-view-all">
                        View all logs <i class="fas fa-arrow-right" style="font-size:11px;"></i>
                    </a>
                </div>

            </div>

            <!-- ===== QUICK ACTIONS ===== -->
            <div class="dash-panel">
                <div class="panel-header">
                    <span class="panel-title">
                        <i class="fas fa-bolt"></i>
                        Quick Actions
                    </span>
                </div>
                <div class="panel-body">
                    <div class="actions-grid">
                        <a href="admin_account.php?role=student" class="action-btn">
                            <div class="action-icon" style="background: linear-gradient(135deg,#2e7d32,#43a047);">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div>
                                <div class="action-title">Add Student</div>
                                <div class="action-sub">Manage student accounts</div>
                            </div>
                        </a>
                        <a href="admin_account.php?role=teacher" class="action-btn">
                            <div class="action-icon" style="background: linear-gradient(135deg,#1565c0,#1976d2);">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div>
                                <div class="action-title">Add Teacher</div>
                                <div class="action-sub">Manage teacher accounts</div>
                            </div>
                        </a>
                        <a href="admin_subject.php" class="action-btn">
                            <div class="action-icon" style="background: linear-gradient(135deg,#e65100,#ef6c00);">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <div>
                                <div class="action-title">Add Subject</div>
                                <div class="action-sub">Manage subjects</div>
                            </div>
                        </a>
                        <a href="admin_section.php" class="action-btn">
                            <div class="action-icon" style="background: linear-gradient(135deg,#6a1b9a,#8e24aa);">
                                <i class="fas fa-plus-square"></i>
                            </div>
                            <div>
                                <div class="action-title">Add Section</div>
                                <div class="action-sub">Manage sections</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        // Chart
        const ctx = document.getElementById('studentsChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php foreach ($grade_data as $g) echo "'" . addslashes($g['year_level']) . "',"; ?>],
                datasets: [{
                    label: 'Students',
                    data: [<?php foreach ($grade_data as $g) echo $g['total'] . ","; ?>],
                    backgroundColor: '#4caf50',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#2e7d32',
                        titleFont: {
                            size: 12
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f0f4f0'
                        },
                        ticks: {
                            color: '#81a881',
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#81a881',
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });

        // Sidebar toggle
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const container = document.querySelector('.container');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            sidebarOverlay?.classList.toggle('active');
            container.classList.toggle('sidebar-active');
        });
        sidebarOverlay?.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            container.classList.remove('sidebar-active');
        });

        document.querySelectorAll(".dropdown-toggle").forEach(toggle => {
            toggle.addEventListener("click", function(e) {
                e.preventDefault();
                const parent = this.parentElement;
                parent.classList.toggle("open");
                document.querySelectorAll(".dropdown").forEach(item => {
                    if (item !== parent) item.classList.remove("open");
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