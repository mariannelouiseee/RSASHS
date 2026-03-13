<?php
include("connect.php");
session_start();

// ===== CHECK IF TEACHER IS LOGGED IN =====
if (!isset($_SESSION['teacher_id'])) {
    echo "<script>alert('You must be logged in as teacher.'); window.location='login.php';</script>";
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// ===== FETCH TEACHER PROFILE DATA =====
$stmt = $conn->prepare("
    SELECT teacher_id, first_name, middle_name, last_name, teacher_image
    FROM teachers
    WHERE teacher_id = ?
");
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

// Build full name
$full_name =
    $teacher['first_name'] .
    (!empty($teacher['middle_name']) ? " " . strtoupper($teacher['middle_name'][0]) . ". " : " ") .
    $teacher['last_name'];

// ===== FETCH SUBJECTS WITH SECTIONS =====
$sub_stmt = $conn->prepare("
    SELECT 
        s.subject_id, 
        s.subject_name, 
        s.category, 
        s.semester,
        sec.section_name,
        sec.year_level
    FROM subjects s
    LEFT JOIN section_subjects ss ON s.subject_id = ss.subject_id
    LEFT JOIN sections sec ON ss.section_id = sec.section_id
    WHERE s.teacher_id = ?
    ORDER BY sec.year_level, sec.section_name, s.semester, s.subject_name
");
$sub_stmt->bind_param("s", $teacher_id);
$sub_stmt->execute();
$subjects_result = $sub_stmt->get_result();

// ===== GROUP DATA BY YEAR LEVEL + SECTION =====
$tables = [];

while ($row = $subjects_result->fetch_assoc()) {
    if (!$row['section_name'] || !$row['year_level']) continue;

    $key = $row['year_level'] . '_' . $row['section_name'];

    if (!isset($tables[$key])) {
        $tables[$key] = [
            'year_level' => $row['year_level'],
            'section_name' => $row['section_name'],
            'subjects' => [] // subjects grouped by semester
        ];
    }

    $semester = $row['semester'];
    if (!isset($tables[$key]['subjects'][$semester])) {
        $tables[$key]['subjects'][$semester] = [];
    }

    $tables[$key]['subjects'][$semester][] = [
        'subject_id' => $row['subject_id'],
        'subject_name' => $row['subject_name'],
        'category' => $row['category']
    ];
}

$school_year = date('Y') . '-' . (date('Y') + 1);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Teacher Profile</title>
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
    <link rel="stylesheet" href="teacher.css">
</head>

<body>

    <header>
        <div class="header-left">
            <img src="./img/logo.jpg" alt="RSASHS Logo">
            <h2>RSASHS E-PORTAL</h2>
        </div>

        <button id="menuToggle" class="menu-toggle">☰</button>

        <nav class="navbar" id="navbarLinks">
            <ul>
                <li><a href="teacher_dashboard.php" class="active">Dashboard</a></li>
                <li><a href="teacher_advisory.php">Advisory Class</a></li>
                <li><a href="logout.php" class="logout">Logout</a></li>
            </ul>
        </nav>
    </header>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const navbar = document.getElementById('navbarLinks');
        menuToggle.addEventListener('click', () => {
            navbar.classList.toggle('active');
        });
    </script>

    <div class="subjects-container">
        <h3>ASSIGNED SUBJECTS</h3>

        <?php if (!empty($tables)): ?>
            <?php foreach ($tables as $group): ?>
                <h4>
                    Year Level: <?= htmlspecialchars($group['year_level']) ?> | Section: <?= htmlspecialchars($group['section_name']) ?>
                </h4>

                <table>
                    <thead>
                        <tr>
                            <th>Subject Name</th>
                            <th>Category</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($group['subjects'] as $semester => $subjects): ?>
                            <!-- Semester row header -->
                            <tr>
                                <td colspan="4" style="text-align:left; font-weight:bold;">
                                    Semester: <?= htmlspecialchars($semester) ?>
                                </td>
                            </tr>

                            <?php foreach ($subjects as $sub): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sub['subject_name']) ?></td>
                                    <td><?= htmlspecialchars($sub['category']) ?></td>
                                    <td>
                                        <a href="input_grade.php?
                                    year_level=<?= urlencode($group['year_level']) ?>
                                    &section_name=<?= urlencode($group['section_name']) ?>
                                    &subject_id=<?= urlencode($sub['subject_id']) ?>
                                    &school_year=<?= urlencode($school_year) ?>"
                                            class="btn-view-students">
                                            View Students
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php endforeach; ?>
                    </tbody>
                </table>
                <br>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;">No subjects assigned.</p>
        <?php endif; ?>
    </div>
    <script>
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