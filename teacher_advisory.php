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
while ($sec = $result_sec->fetch_assoc()) {
    $sections[] = $sec;
}
$stmt_sec->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Advisory Class</title>
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
    <link rel="stylesheet" href="teacher.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 26px;
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
            border-radius: 26px;
            transition: .3s;
        }

        .slider:before {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            left: 3px;
            bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: .3s;
        }

        input:checked+.slider {
            background: #2e7d32;
        }

        input:checked+.slider:before {
            transform: translateX(22px);
        }

        .toggle-label {
            font-size: 13px;
            color: #555;
            font-weight: 600;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 6px;
            flex-wrap: wrap;
        }

        .section-header h4 {
            margin: 0;
        }
    </style>
</head>

<body>

    <header>
        <div class="header-left">
            <img src="./img/logo.jpg" alt="RSASHS Logo">
            <h2>RSASHS E-PORTAL</h2>
        </div>
        <button id="menuToggle" class="menu-toggle"><i class="fas fa-bars"></i></button>
        <nav class="navbar" id="navbarLinks">
            <ul>
                <li><a href="teacher_dashboard.php">Dashboard</a></li>
                <li><a href="teacher_advisory.php" class="active">Advisory Class</a></li>
                <li><a href="logout.php" class="logout">Logout</a></li>
            </ul>
        </nav>
    </header>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const navbar = document.getElementById('navbarLinks');
        menuToggle.addEventListener('click', () => navbar.classList.toggle('active'));
    </script>

    <main class="subjects-container">
        <h3>ADVISORY CLASS</h3>

        <?php if (empty($sections)): ?>
            <p>You are not assigned as an adviser to any section.</p>
        <?php else: ?>
            <?php foreach ($sections as $sec): ?>

                <div class="section-header">
                    <h4><?= htmlspecialchars($sec['year_level'] . ' - ' . $sec['section_name'] . ' | ' . $sec['school_year']) ?></h4>
                    <label class="toggle-switch" title="Toggle grade visibility for students">
                        <input type="checkbox" class="grade-toggle"
                            data-section-id="<?= $sec['section_id'] ?>"
                            <?= $sec['grades_visible'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label" id="label-<?= $sec['section_id'] ?>">
                        <?= $sec['grades_visible'] ? '✅ Grades: Visible' : '🔒 Grades: Hidden' ?>
                    </span>
                </div>

                <table border="1" cellpadding="5" cellspacing="0" style="margin-bottom:20px; width:100%;">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Action</th>
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
                                    <td><?= htmlspecialchars($stu['student_id']) ?></td>
                                    <td><?= htmlspecialchars($stu['name']) ?></td>
                                    <td>
                                        <a class="btn-print"
                                            href="print_report_card.php?student_id=<?= urlencode($stu['student_id']) ?>&year_level=<?= urlencode($sec['year_level']) ?>&section_name=<?= urlencode($sec['section_name']) ?>&school_year=<?= urlencode($sec['school_year']) ?>"
                                            target="_blank">
                                            <i class="fas fa-print"></i> Print Report Card
                                        </a>
                                    </td>
                                </tr>
                        <?php
                            endwhile;
                        else:
                            echo '<tr><td colspan="3">No students assigned to this section.</td></tr>';
                        endif;
                        $stmt_stu->close();
                        ?>
                    </tbody>
                </table>

            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <script>
        // ===== GRADE VISIBILITY TOGGLE =====
        document.querySelectorAll('.grade-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const sectionId = this.dataset.sectionId;
                const state = this.checked ? 1 : 0;
                const label = document.getElementById('label-' + sectionId);
                const checkbox = this;

                // ===== CONFIRMATION BEFORE MAKING VISIBLE =====
                if (state === 1) {
                    const confirmed = confirm('Are you sure you want to make the grades visible to students?');
                    if (!confirmed) {
                        checkbox.checked = false; // revert toggle
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
                            label.textContent = state ? '✅ Grades: Visible' : '🔒 Grades: Hidden';
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

        // ===== INACTIVITY LOGOUT =====
        (function() {
            const INACTIVITY_LIMIT = 5 * 60 * 1000;
            const WARNING_TIME = 10 * 1000;
            let inactivityTimer, warningTimer;

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
                <strong style="font-size:16px;color:#1b5e20;">Inactivity Warning</strong>
                <span>You have been inactive. You will be logged out in <span id="countdown">10</span> seconds.</span>
                <button id="stayLoggedIn" style="padding:8px 12px;background:#2e7d32;color:white;border:none;border-radius:6px;font-weight:bold;cursor:pointer;align-self:flex-end;">Stay Logged In</button>
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
                    .then(r => r.json())
                    .then(data => {
                        alert(data.message || 'Logged out due to inactivity.');
                        window.location.href = 'login.php';
                    })
                    .catch(() => {
                        window.location.href = 'login.php';
                    });
            }

            ['mousemove', 'keydown', 'mousedown', 'touchstart'].forEach(evt => document.addEventListener(evt, resetTimer));
            resetTimer();
        })();
    </script>

</body>

</html>