<?php
include("connect.php");
include("functions.php");
session_start();

// ===== ADDITION: CHECK IF ADMIN IS LOGGED IN =====
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('You must be logged in as admin.'); window.location='login.php';</script>";
    exit();
}


// Log that admin visited dashboard
addLog($conn, $_SESSION['admin_id'], 'admin', "Visited dashboard");

// Totals
$student_count = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'] ?? 0;
$teacher_count = $conn->query("SELECT COUNT(*) AS total FROM teachers")->fetch_assoc()['total'] ?? 0;
$subject_count = $conn->query("SELECT COUNT(*) AS total FROM subjects")->fetch_assoc()['total'] ?? 0;
$section_count = $conn->query("SELECT COUNT(*) AS total FROM sections")->fetch_assoc()['total'] ?? 0;

// Students per Grade Level
$grade_data = [];
$res = $conn->query("SELECT year_level, COUNT(*) AS total FROM students GROUP BY year_level ORDER BY year_level ASC");
while ($row = $res->fetch_assoc()) {
    $grade_data[] = $row;
}

// Recent activity (latest 5)
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
                    <a href="#" class="dropdown-toggle"><i class="fas fa-users"></i> Accounts <i
                            class="fas fa-caret-down arrow"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="admin_account.php?role=student"><i class="fas fa-user-graduate"></i> Student</a>
                        </li>
                        <li><a href="admin_account.php?role=teacher"><i class="fas fa-chalkboard-teacher"></i>
                                Teacher</a></li>
                    </ul>
                </li>
                <li><a href="admin_subject.php"><i class="fas fa-book-open"></i> Subjects</a></li>
                <li><a href="admin_section.php"><i class="fas fa-layer-group"></i> Section</a></li>
                <li><a href="admin_grade.php"><i class="fas fa-clipboard-list"></i> Grades</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="dashboard">
            <h2>DASHBOARD OVERVIEW</h2>

            <!-- Cards -->
            <div class="cards">
                <div class="card">
                    <i class="fas fa-user-graduate"></i>
                    <h3><?= $student_count ?></h3>
                    <p>Students</p>
                </div>
                <div class="card">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3><?= $teacher_count ?></h3>
                    <p>Teachers</p>
                </div>
                <div class="card">
                    <i class="fas fa-book-open"></i>
                    <h3><?= $subject_count ?></h3>
                    <p>Subjects</p>
                </div>
                <div class="card">
                    <i class="fas fa-layer-group"></i>
                    <h3><?= $section_count ?></h3>
                    <p>Sections</p>
                </div>
            </div>

            <!-- Chart -->
            <div class="chart-container">
                <h3>Students per Grade Level</h3>
                <canvas id="studentsChart"></canvas>
            </div>

            <!-- Recent Activity -->
            <div class="activity">
                <h3>Recent Activity</h3>
                <ul>
                    <?php if ($activity_data->num_rows > 0): ?>
                        <?php while ($log = $activity_data->fetch_assoc()): ?>
                            <li><?= htmlspecialchars($log['description']) ?>
                                <span><?= date("M d, Y H:i", strtotime($log['created_at'])) ?></span>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li>No recent activity.</li>
                    <?php endif; ?>
                </ul>
                <a href="admin_activity.php" class="btn view-all">View All</a>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="action-buttons">
                    <a href="admin_account.php?role=student" class="btn"><i class="fas fa-user-plus"></i> Add
                        Student</a>
                    <a href="admin_account.php?role=teacher" class="btn"><i class="fas fa-user-tie"></i> Add Teacher</a>
                    <a href="admin_subject.php" class="btn"><i class="fas fa-plus"></i> Add Subject</a>
                    <a href="admin_section.php" class="btn"><i class="fas fa-plus-square"></i> Add Section</a>
                </div>
            </div>
        </main>
    </div>

    <script>
        const ctx = document.getElementById('studentsChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php foreach ($grade_data as $g)
                                echo "'" . $g['year_level'] . "',"; ?>],
                datasets: [{
                    label: 'Students',
                    data: [<?php foreach ($grade_data as $g)
                                echo $g['total'] . ","; ?>],
                    backgroundColor: '#1b5e20'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
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
        (function() {
            const INACTIVITY_LIMIT = 5 * 60 * 1000;
            const WARNING_TIME = 10 * 1000;

            let inactivityTimer;
            let warningTimer;

            function resetTimer() {
                clearTimeout(inactivityTimer);
                clearTimeout(warningTimer);


                warningTimer = setTimeout(showWarning, INACTIVITY_LIMIT - WARNING_TIME);
                inactivityTimer = setTimeout(logoutUser, INACTIVITY_LIMIT);
            }

            function showWarning() {
                if (document.getElementById('inactivityWarning')) return;

                const warningDiv = document.createElement('div');
                warningDiv.id = 'inactivityWarning';

                Object.assign(warningDiv.style, {
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
                    fontFamily: '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif',
                    fontSize: '14px',
                    lineHeight: '1.4',
                    display: 'flex',
                    flexDirection: 'column',
                    gap: '10px',
                    opacity: 0,
                    transition: 'opacity 0.5s ease',
                    zIndex: 10000
                });

                warningDiv.innerHTML = `
        <strong style="font-size:16px; color:#1b5e20;">Inactivity Warning</strong>
        <span>You have been inactive. You will be logged out in <span id="countdown">10</span> seconds.</span>
        <button id="stayLoggedIn" style="
            padding:8px 12px;
            background:#2e7d32;
            color:white;
            border:none;
            border-radius:6px;
            font-weight:bold;
            cursor:pointer;
            align-self:flex-end;
            transition: background 0.3s;
        ">Stay Logged In</button>
    `;

                document.body.appendChild(warningDiv);

                setTimeout(() => warningDiv.style.opacity = 1, 10);

                let countdown = 10;
                const countdownSpan = document.getElementById('countdown');
                const interval = setInterval(() => {
                    countdown--;
                    if (countdown <= 0) {
                        clearInterval(interval);
                    }
                    countdownSpan.textContent = countdown;
                }, 1000);

                document.getElementById('stayLoggedIn').addEventListener('click', () => {
                    clearInterval(interval);
                    warningDiv.style.opacity = 0;
                    setTimeout(() => warningDiv.remove(), 300);
                    resetTimer();
                });
            }


            function logoutUser() {
                fetch('auto_logout.php', {
                        method: 'POST',
                        credentials: 'same-origin'
                    })
                    .then(resp => resp.json())
                    .then(data => {
                        alert(data.message || 'You have been logged out due to inactivity.');
                        window.location.href = 'login.php';
                    })
                    .catch(err => {
                        console.error('Auto logout error:', err);
                        window.location.href = 'login.php';
                    });
            }

            ['mousemove', 'keydown', 'mousedown', 'touchstart'].forEach(evt => {
                document.addEventListener(evt, resetTimer);
            });

            resetTimer();
        })();
    </script>
</body>

</html>