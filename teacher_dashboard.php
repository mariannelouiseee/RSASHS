<?php
include("connect.php");
session_start();

if (!isset($_SESSION['teacher_id'])) {
    echo "<script>alert('You must be logged in as teacher.'); window.location='login.php';</script>";
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch teacher profile
$stmt = $conn->prepare("
    SELECT teacher_id, first_name, middle_name, last_name, teacher_image
    FROM teachers WHERE teacher_id = ?
");
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

$full_name =
    ($teacher['first_name'] ?? '') .
    (!empty($teacher['middle_name']) ? ' ' . strtoupper($teacher['middle_name'][0]) . '. ' : ' ') .
    ($teacher['last_name'] ?? '');

$initials = strtoupper(
    substr($teacher['first_name'] ?? '', 0, 1) .
        substr($teacher['last_name']  ?? '', 0, 1)
);

// Fetch subjects with sections
$sub_stmt = $conn->prepare("
    SELECT s.subject_id, s.subject_name, s.category, s.semester,
           sec.section_name, sec.year_level
    FROM subjects s
    LEFT JOIN section_subjects ss ON s.subject_id = ss.subject_id
    LEFT JOIN sections sec ON ss.section_id = sec.section_id
    WHERE s.teacher_id = ?
    ORDER BY sec.year_level, sec.section_name, s.semester, s.subject_name
");
$sub_stmt->bind_param("s", $teacher_id);
$sub_stmt->execute();
$subjects_result = $sub_stmt->get_result();
$sub_stmt->close();

// Group by year level + section
$tables = [];
while ($row = $subjects_result->fetch_assoc()) {
    if (!$row['section_name'] || !$row['year_level']) continue;
    $key = $row['year_level'] . '_' . $row['section_name'];
    if (!isset($tables[$key])) {
        $tables[$key] = [
            'year_level'   => $row['year_level'],
            'section_name' => $row['section_name'],
            'subjects'     => [],
        ];
    }
    $sem = $row['semester'];
    if (!isset($tables[$key]['subjects'][$sem])) $tables[$key]['subjects'][$sem] = [];
    $tables[$key]['subjects'][$sem][] = [
        'subject_id'   => $row['subject_id'],
        'subject_name' => $row['subject_name'],
        'category'     => $row['category'],
    ];
}

$school_year = date('Y') . '-' . (date('Y') + 1);

// Count totals for the stat strip
$total_subjects = 0;
$total_sections = count($tables);
foreach ($tables as $g) foreach ($g['subjects'] as $subs) $total_subjects += count($subs);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="teacher.css">
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
                <li><a href="teacher_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="teacher_advisory.php"><i class="fas fa-users"></i> Advisory Class</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- ===== PAGE WRAPPER ===== -->
    <div class="page-wrap">

        <!-- Teacher hero card -->
        <div class="hero-card">
            <div class="hero-banner"></div>
            <div class="hero-body">
                <div class="hero-avatar">
                    <?php if (!empty($teacher['teacher_image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($teacher['teacher_image']) ?>" alt="Photo" />
                    <?php else: ?>
                        <?= htmlspecialchars($initials ?: '?') ?>
                    <?php endif; ?>
                </div>
                <div class="hero-info">
                    <div class="hero-name"><?= htmlspecialchars($full_name) ?></div>
                    <div class="hero-meta">
                        <span class="hero-badge">
                            <i class="fas fa-id-badge"></i>
                            <?= htmlspecialchars($teacher['teacher_id']) ?>
                        </span>
                        <span class="hero-badge" style="background:#e3f2fd;color:#1565c0;border-color:#bbdefb;">
                            <i class="fas fa-chalkboard-teacher"></i>
                            Teacher
                        </span>
                        <span class="hero-badge" style="background:#fff8e1;color:#f57f17;border-color:#ffe082;">
                            <i class="fas fa-calendar-alt"></i>
                            S.Y. <?= htmlspecialchars($school_year) ?>
                        </span>
                    </div>
                </div>
            </div>
            <!-- Stat strip -->
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hs-num"><?= $total_sections ?></div>
                    <div class="hs-label">Sections</div>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <div class="hs-num"><?= $total_subjects ?></div>
                    <div class="hs-label">Subjects</div>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <div class="hs-num"><?= htmlspecialchars($school_year) ?></div>
                    <div class="hs-label">School Year</div>
                </div>
            </div>
        </div>

        <!-- ===== ASSIGNED SUBJECTS ===== -->
        <div class="page-title">
            Assigned Subjects
        </div>

        <?php if (!empty($tables)): ?>
            <?php foreach ($tables as $group): ?>

                <!-- Section group label -->
                <div class="section-group-label">
                    <span class="sgb-pill">
                        <i class="fas fa-layer-group"></i>
                        <?= htmlspecialchars($group['year_level']) ?>
                    </span>
                    <span class="sgb-sep">&mdash;</span>
                    <span class="sgb-section"><?= htmlspecialchars($group['section_name']) ?></span>
                </div>

                <?php foreach ($group['subjects'] as $semester => $subjects): ?>

                    <div class="table-card">
                        <div class="cat-header">
                            <i class="fas fa-book" style="margin-right:7px;font-size:11px;"></i>
                            <?= htmlspecialchars($semester) ?>
                        </div>
                        <div class="table-scroll">
                            <table class="subjects-table">
                                <thead>
                                    <tr>
                                        <th class="col-name">Subject Name</th>
                                        <th class="col-cat">Category</th>
                                        <th class="col-action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects as $sub): ?>
                                        <tr>
                                            <td class="td-name"><?= htmlspecialchars($sub['subject_name']) ?></td>
                                            <td>
                                                <span class="cat-badge <?= $sub['category'] === 'Core Subjects' ? 'core' : 'applied' ?>">
                                                    <?= htmlspecialchars($sub['category']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="input_grade.php?year_level=<?= urlencode($group['year_level']) ?>&section_name=<?= urlencode($group['section_name']) ?>&subject_id=<?= urlencode($sub['subject_id']) ?>&school_year=<?= urlencode($school_year) ?>"
                                                    class="btn-view-students">
                                                    <i class="fas fa-users" style="margin-right:5px;"></i>View Students
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php endforeach; ?>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No subjects assigned yet.</p>
            </div>
        <?php endif; ?>

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