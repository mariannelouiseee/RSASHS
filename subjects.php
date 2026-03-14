<?php
include("connect.php");
include("functions.php");
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

addLog($conn, $student_id, 'student', 'Viewed report card');

// Check grade visibility
$stmtInfo = $conn->prepare("SELECT year_level, section_name FROM students WHERE student_id = ?");
$stmtInfo->bind_param("i", $student_id);
$stmtInfo->execute();
$studentInfo = $stmtInfo->get_result()->fetch_assoc();
$stmtInfo->close();

$stmtVis = $conn->prepare("SELECT grades_visible FROM sections WHERE section_name = ? AND year_level = ? LIMIT 1");
$stmtVis->bind_param("ss", $studentInfo['section_name'], $studentInfo['year_level']);
$stmtVis->execute();
$visRow = $stmtVis->get_result()->fetch_assoc();
$stmtVis->close();

$gradesVisible = $visRow['grades_visible'] ?? 0;

// Fetch all school years
$stmtYears = $conn->prepare("SELECT DISTINCT school_year FROM grades WHERE student_id = ? ORDER BY school_year DESC");
$stmtYears->bind_param("i", $student_id);
$stmtYears->execute();
$resYears = $stmtYears->get_result();
$school_years = [];
while ($row = $resYears->fetch_assoc()) $school_years[] = $row['school_year'];
$stmtYears->close();

// Fetch subjects per school year
$tables_by_year = [];
$sqlSubjects = "
    SELECT sub.subject_name, sub.category,
           CONCAT(t.first_name, ' ', t.last_name) AS teacher_name,
           g.q1, g.q2, g.q3, g.q4,
           g.first_sem_final, g.gwa_first_sem,
           g.second_sem_final, g.gwa_second_sem, g.final
    FROM students stu
    INNER JOIN sections sec ON stu.section_name = sec.section_name AND stu.year_level = sec.year_level
    INNER JOIN section_subjects ss ON sec.section_id = ss.section_id
    INNER JOIN subjects sub ON ss.subject_id = sub.subject_id
    LEFT JOIN teachers t ON sub.teacher_id = t.teacher_id
    LEFT JOIN grades g ON g.student_id = stu.student_id AND g.subject_id = sub.subject_id AND g.school_year = ?
    WHERE stu.student_id = ?
    ORDER BY sub.category, sub.subject_name";

$stmtSubjects = $conn->prepare($sqlSubjects);
foreach ($school_years as $sy) {
    $stmtSubjects->bind_param("si", $sy, $student_id);
    $stmtSubjects->execute();
    $res = $stmtSubjects->get_result();
    $subjects_by_category = [];
    while ($row = $res->fetch_assoc()) $subjects_by_category[$row['category']][] = $row;
    $tables_by_year[$sy] = $subjects_by_category;
}
$stmtSubjects->close();
$conn->close();

// Helper: colour-code a grade value
function gradeClass($val)
{
    if ($val === null || $val === '') return 'none';
    if ($val < 75) return 'low';
    if ($val < 80) return 'mid';
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Grades</title>
    <link rel="stylesheet" href="subjects.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
</head>

<body>
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
                <li><a href="student.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="subjects.php" class="active"><i class="fas fa-book-open"></i> Subjects</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="page-wrap">

        <div class="page-title">
            My Subjects &amp; Grades
        </div>

        <?php if (!$gradesVisible): ?>
            <div class="notice-bar">
                <i class="fas fa-lock"></i>
                Grades are currently not yet released by your adviser.
            </div>
        <?php endif; ?>

        <?php if (!empty($tables_by_year)): ?>
            <?php foreach ($tables_by_year as $school_year => $subjects_by_category): ?>

                <div class="year-block">
                    <div class="year-label">
                        <span class="year-pill">
                            <i class="fas fa-calendar-alt" style="margin-right:5px;"></i>
                            School Year <?= htmlspecialchars($school_year) ?>
                        </span>
                    </div>

                    <?php if (!empty($subjects_by_category)): ?>
                        <?php foreach ($subjects_by_category as $category => $subjects): ?>

                            <div class="table-card">
                                <div class="cat-header">
                                    <i class="fas fa-layer-group" style="margin-right:7px;font-size:12px;"></i>
                                    <?= htmlspecialchars($category) ?>
                                </div>

                                <div class="table-scroll">
                                    <table class="subjects-table">
                                        <thead>
                                            <tr>
                                                <th class="col-subject">Subject</th>
                                                <th class="col-teacher">Teacher</th>
                                                <?php if ($gradesVisible): ?>
                                                    <th colspan="4" class="sem-group-header">1st Semester</th>
                                                    <th colspan="4" class="sem-group-header">2nd Semester</th>
                                                    <th>Final</th>
                                                <?php endif; ?>
                                            </tr>
                                            <?php if ($gradesVisible): ?>
                                                <tr class="sub-header-row">
                                                    <th class="col-subject"></th>
                                                    <th class="col-teacher"></th>
                                                    <th>Q1</th>
                                                    <th>Q2</th>
                                                    <th>Final</th>
                                                    <th>GWA</th>
                                                    <th>Q3</th>
                                                    <th>Q4</th>
                                                    <th>Final</th>
                                                    <th>GWA</th>
                                                    <th></th>
                                                </tr>
                                            <?php endif; ?>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($subjects as $s): ?>
                                                <tr>
                                                    <td class="col-subject td-name"><?= htmlspecialchars($s['subject_name']) ?></td>
                                                    <td class="col-teacher td-teacher"><?= htmlspecialchars($s['teacher_name'] ?? 'Unassigned') ?></td>
                                                    <?php if ($gradesVisible): ?>
                                                        <?php
                                                        $cols = ['q1', 'q2', 'first_sem_final', 'gwa_first_sem', 'q3', 'q4', 'second_sem_final', 'gwa_second_sem', 'final'];
                                                        foreach ($cols as $col):
                                                            $v = $s[$col] ?? null;
                                                            $cls = gradeClass($v);
                                                        ?>
                                                            <td>
                                                                <span class="grade <?= $cls ?>">
                                                                    <?= ($v !== null && $v !== '') ? htmlspecialchars($v) : '—' ?>
                                                                </span>
                                                            </td>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div><!-- .table-scroll -->
                            </div><!-- .table-card -->

                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No subjects found for school year <?= htmlspecialchars($school_year) ?>.</p>
                        </div>
                    <?php endif; ?>
                </div><!-- .year-block -->

            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No grades found for your account.</p>
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