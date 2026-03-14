<?php
include("connect.php");
session_start();

if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('You must be logged in.'); window.location='login.php';</script>";
    exit();
}

$student_id = $_SESSION['student_id'];

$sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $student = $result->fetch_assoc();
} else {
    echo "Student record not found.";
    exit();
}

$stmt->close();
$conn->close();

$initials = strtoupper(
    substr($student['first_name'] ?? '', 0, 1) .
        substr($student['last_name']  ?? '', 0, 1)
);

$first_name = htmlspecialchars($student['first_name'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
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
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="student.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="subjects.php"><i class="fas fa-book-open"></i> Subjects</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- ===== DASHBOARD BODY ===== -->
    <div class="dash-body">

        <!-- Left: Welcome + avatar -->
        <div class="welcome-col">

            <div class="avatar-wrap">
                <?php if (!empty($student['student_image']) && file_exists("uploads/" . $student['student_image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($student['student_image']) ?>" alt="Profile Photo" />
                <?php else: ?>
                    <?= $initials ?: '?' ?>
                <?php endif; ?>
            </div>

            <div class="greeting-line" id="dynamicGreeting">Good morning!</div>

            <h1 class="welcome-name">
                <?= $first_name ?> <?= htmlspecialchars($student['last_name'] ?? '') ?>
            </h1>

            <div class="id-pill">
                <i class="fas fa-id-card"></i>
                <?= htmlspecialchars($student['student_id']) ?>
            </div>

            <div class="nav-shortcuts">
                <a href="student.php" class="shortcut-btn">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </a>
                <a href="subjects.php" class="shortcut-btn">
                    <i class="fas fa-book-open"></i>
                    <span>My Grades</span>
                </a>
            </div>
        </div>

        <!-- Right: Academic info card -->
        <div class="info-col">

            <div class="info-card-title">
                <i class="fas fa-graduation-cap"></i>
                Academic Information
            </div>

            <div class="info-tiles">
                <div class="info-tile">
                    <div class="tile-label">Grade Level</div>
                    <div class="tile-val"><?= htmlspecialchars($student['year_level'] ?? '—') ?></div>
                </div>
                <div class="info-tile">
                    <div class="tile-label">Section</div>
                    <div class="tile-val"><?= htmlspecialchars($student['section_name'] ?? '—') ?></div>
                </div>
                <div class="info-tile full">
                    <div class="tile-label">School Year</div>
                    <div class="tile-val"><?= htmlspecialchars($student['school_year'] ?? '—') ?></div>
                </div>
                <?php if (!empty($student['strand'])): ?>
                    <div class="info-tile full">
                        <div class="tile-label">Strand</div>
                        <div class="tile-val"><?= htmlspecialchars($student['strand']) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick links inside card -->
            <div class="card-links">
                <a href="student.php" class="card-link">
                    <i class="fas fa-user-circle"></i> View Full Profile
                    <i class="fas fa-arrow-right card-link-arrow"></i>
                </a>
                <a href="subjects.php" class="card-link">
                    <i class="fas fa-clipboard-list"></i> View Subjects &amp; Grades
                    <i class="fas fa-arrow-right card-link-arrow"></i>
                </a>
            </div>
        </div>

    </div><!-- .dash-body -->

    <script>
        document.getElementById("menuToggle").addEventListener("click", () => {
            document.querySelector("#navbarLinks ul").classList.toggle("active");
        });

        function updateGreeting() {
            const hour = new Date().getHours();
            const el = document.getElementById('dynamicGreeting');
            if (hour < 12) el.textContent = "Good morning! Ready to learn? ☀️";
            else if (hour < 17) el.textContent = "Good afternoon! Keep it up! 🌤";
            else el.textContent = "Good evening! Hope you had a great day! 🌙";
        }
        updateGreeting();

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