<?php
include("connect.php");
include("functions.php");
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Log viewing
addLog($conn, $student_id, 'student', 'Viewed report card');

// ===== CHECK GRADE VISIBILITY FOR STUDENT'S SECTION =====
$stmtInfo = $conn->prepare("SELECT year_level, section_name FROM students WHERE student_id = ?");
$stmtInfo->bind_param("i", $student_id);
$stmtInfo->execute();
$studentInfo = $stmtInfo->get_result()->fetch_assoc();
$stmtInfo->close();

$stmtVis = $conn->prepare("
    SELECT grades_visible FROM sections 
    WHERE section_name = ? AND year_level = ?
    LIMIT 1
");
$stmtVis->bind_param("ss", $studentInfo['section_name'], $studentInfo['year_level']);
$stmtVis->execute();
$visRow = $stmtVis->get_result()->fetch_assoc();
$stmtVis->close();

$gradesVisible = $visRow['grades_visible'] ?? 0;

// ===== FETCH ALL SCHOOL YEARS =====
$sqlYears = "SELECT DISTINCT school_year 
             FROM grades 
             WHERE student_id = ?
             ORDER BY school_year DESC";

$stmtYears = $conn->prepare($sqlYears);
$stmtYears->bind_param("i", $student_id);
$stmtYears->execute();
$resYears = $stmtYears->get_result();

$school_years = [];
while ($row = $resYears->fetch_assoc()) {
    $school_years[] = $row['school_year'];
}

$stmtYears->close();

// ===== FETCH SUBJECTS PER SCHOOL YEAR =====
$tables_by_year = [];

$sqlSubjects = "
SELECT 
    sub.subject_name,
    sub.category,
    CONCAT(t.first_name, ' ', t.last_name) AS teacher_name,
    g.q1, g.q2, g.q3, g.q4,
    g.first_sem_final, g.gwa_first_sem,
    g.second_sem_final, g.gwa_second_sem,
    g.final
FROM students stu
INNER JOIN sections sec
    ON stu.section_name = sec.section_name
   AND stu.year_level = sec.year_level
INNER JOIN section_subjects ss
    ON sec.section_id = ss.section_id
INNER JOIN subjects sub
    ON ss.subject_id = sub.subject_id
LEFT JOIN teachers t
    ON sub.teacher_id = t.teacher_id
LEFT JOIN grades g
    ON g.student_id = stu.student_id
   AND g.subject_id = sub.subject_id
   AND g.school_year = ?
WHERE stu.student_id = ?
ORDER BY sub.category, sub.subject_name
";

$stmtSubjects = $conn->prepare($sqlSubjects);

foreach ($school_years as $sy) {
    $stmtSubjects->bind_param("si", $sy, $student_id);
    $stmtSubjects->execute();
    $res = $stmtSubjects->get_result();

    $subjects_by_category = [];
    while ($row = $res->fetch_assoc()) {
        $subjects_by_category[$row['category']][] = $row;
    }

    $tables_by_year[$sy] = $subjects_by_category;
}

$stmtSubjects->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Grades</title>
    <link rel="stylesheet" href="subjects.css">
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
</head>

<body>

    <header>
        <div class="header-left">
            <img src="./img/logo.jpg" alt="RSASHS Logo" style="height:48px;">
            <h2>RSASHS E-PORTAL</h2>
        </div>
        <button id="menuToggle" class="menu-toggle">☰</button>
        <nav class="navbar" id="navbarLinks">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="student.php">Profile</a></li>
                <li><a href="subjects.php">Subjects</a></li>
                <li><a href="logout.php" class="logout">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="profile-container">
        <h2>MY SUBJECTS & GRADES</h2>

        <?php if (!$gradesVisible): ?>
            <div style="
                background: #fff8e1;
                border: 1px solid #f9a825;
                border-radius: 8px;
                padding: 12px 18px;
                margin-bottom: 16px;
                color: #6d4c00;
                font-size: 14px;
            ">
                🔒 Grades are currently not yet released by your adviser.
            </div>
        <?php endif; ?>

        <?php if (!empty($tables_by_year)): ?>
            <?php foreach ($tables_by_year as $school_year => $subjects_by_category): ?>
                <h3>School Year: <?= htmlspecialchars($school_year) ?></h3>

                <?php if (!empty($subjects_by_category)): ?>
                    <table class="subjects-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <?php if ($gradesVisible): ?>
                                    <th>Q1</th>
                                    <th>Q2</th>
                                    <th>1st Sem Final</th>
                                    <th>GWA 1st Sem</th>
                                    <th>Q3</th>
                                    <th>Q4</th>
                                    <th>2nd Sem Final</th>
                                    <th>GWA 2nd Sem</th>
                                    <th>Final</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects_by_category as $category => $subjects): ?>
                                <tr class="category-row">
                                    <td colspan="<?= $gradesVisible ? '11' : '2' ?>">
                                        <?= htmlspecialchars($category) ?>
                                    </td>
                                </tr>
                                <?php foreach ($subjects as $s): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($s['subject_name']) ?></td>
                                        <td><?= htmlspecialchars($s['teacher_name'] ?? 'Unassigned') ?></td>
                                        <?php if ($gradesVisible): ?>
                                            <td><?= $s['q1'] ?? '-' ?></td>
                                            <td><?= $s['q2'] ?? '-' ?></td>
                                            <td><?= $s['first_sem_final'] ?? '-' ?></td>
                                            <td><?= $s['gwa_first_sem'] ?? '-' ?></td>
                                            <td><?= $s['q3'] ?? '-' ?></td>
                                            <td><?= $s['q4'] ?? '-' ?></td>
                                            <td><?= $s['second_sem_final'] ?? '-' ?></td>
                                            <td><?= $s['gwa_second_sem'] ?? '-' ?></td>
                                            <td><?= $s['final'] ?? '-' ?></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No subjects or grades found for school year <?= htmlspecialchars($school_year) ?>.</p>
                <?php endif; ?>
                <hr>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No grades found.</p>
        <?php endif; ?>

    </div>

    <script>
        document.getElementById("menuToggle").addEventListener("click", () => {
            document.querySelector("#navbarLinks ul").classList.toggle("active");
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
                    if (countdown <= 0) clearInterval(interval);
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