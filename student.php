<?php
include("connect.php");
include("functions.php");
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
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

addLog($conn, $student_id, 'student', 'Viewed profile');

$stmt->close();
$conn->close();

// Build full name
$full_name = trim(implode(' ', array_filter([
    $student['first_name'],
    $student['middle_name'],
    $student['last_name'],
    $student['extension_name'],
])));

// Initials for avatar
$initials = strtoupper(
    substr($student['first_name'] ?? '', 0, 1) .
        substr($student['last_name']  ?? '', 0, 1)
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Profile</title>
    <link rel="stylesheet" href="student.css">
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
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="student.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="subjects.php"><i class="fas fa-book-open"></i> Subjects</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- ===== PAGE WRAPPER ===== -->
    <div class="page-wrap">

        <div class="page-title">Student Profile</div>

        <!-- Profile hero card -->
        <div class="hero-card">
            <div class="hero-banner"></div>
            <div class="hero-body">
                <div class="hero-avatar">
                    <?php if (!empty($student['student_image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($student['student_image']) ?>" alt="Photo" />
                    <?php else: ?>
                        <?= htmlspecialchars($initials ?: '?') ?>
                    <?php endif; ?>
                </div>
                <div class="hero-info">
                    <div class="hero-name"><?= htmlspecialchars($full_name) ?></div>
                    <div class="hero-meta">
                        <span class="hero-badge">
                            <i class="fas fa-id-card"></i>
                            <?= htmlspecialchars($student['student_id']) ?>
                        </span>
                        <?php if (!empty($student['year_level'])): ?>
                            <span class="hero-badge" style="background:#e3f2fd; color:#1565c0; border-color:#bbdefb;">
                                <i class="fas fa-graduation-cap"></i>
                                <?= htmlspecialchars($student['year_level']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($student['section_name'])): ?>
                            <span class="hero-badge" style="background:#f3e5f5; color:#6a1b9a; border-color:#e1bee7;">
                                <i class="fas fa-layer-group"></i>
                                <?= htmlspecialchars($student['section_name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== PERSONAL INFORMATION ===== -->
        <div class="info-section">
            <div class="section-label">
                <i class="fas fa-user-circle"></i>
                Personal Information
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Student ID</div>
                    <div class="info-val"><?= htmlspecialchars($student['student_id']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Full Name</div>
                    <div class="info-val"><?= htmlspecialchars($full_name) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Gender</div>
                    <div class="info-val"><?= htmlspecialchars($student['gender'] ?? '—') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Birthday</div>
                    <div class="info-val"><?= !empty($student['birthday']) ? date('F d, Y', strtotime($student['birthday'])) : '—' ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact</div>
                    <div class="info-val"><?= htmlspecialchars($student['contact'] ?? '—') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-val"><?= htmlspecialchars($student['email'] ?? '—') ?></div>
                </div>
                <div class="info-item full">
                    <div class="info-label">Address</div>
                    <div class="info-val"><?= htmlspecialchars($student['address'] ?? '—') ?></div>
                </div>
            </div>
        </div>

        <!-- ===== EDUCATIONAL BACKGROUND ===== -->
        <div class="info-section">
            <div class="section-label">
                <i class="fas fa-school"></i>
                Educational Background
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Last School Attended</div>
                    <div class="info-val"><?= htmlspecialchars($student['last_school'] ?? '—') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">School Address</div>
                    <div class="info-val"><?= htmlspecialchars($student['school_address'] ?? '—') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date Attended</div>
                    <div class="info-val"><?= htmlspecialchars($student['date_attended'] ?? '—') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Honors Received</div>
                    <div class="info-val"><?= htmlspecialchars($student['honors_received'] ?? '—') ?></div>
                </div>
            </div>
        </div>

        <!-- ===== FAMILY INFORMATION ===== -->
        <div class="info-section">
            <div class="section-label">
                <i class="fas fa-users"></i>
                Family Information
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Father's Name</div>
                    <div class="info-val"><?= htmlspecialchars($student['father_name'] ?? '—') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Father's Occupation</div>
                    <div class="info-val"><?= htmlspecialchars($student['father_occupation'] ?? '—') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Mother's Name</div>
                    <div class="info-val"><?= htmlspecialchars($student['mother_name'] ?? '—') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Mother's Occupation</div>
                    <div class="info-val"><?= htmlspecialchars($student['mother_occupation'] ?? '—') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Guardian's Name</div>
                    <div class="info-val"><?= htmlspecialchars($student['guardian_name'] ?? '—') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Guardian Contact</div>
                    <div class="info-val"><?= htmlspecialchars($student['guardian_contact'] ?? '—') ?></div>
                </div>
            </div>
        </div>

    </div><!-- .page-wrap -->

    <script>
        document.getElementById("menuToggle").addEventListener("click", () => {
            document.querySelector("#navbarLinks ul").classList.toggle("active");
        });

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