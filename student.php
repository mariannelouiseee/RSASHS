<?php
include("connect.php");
include("functions.php");
session_start();

// ===== Ensure student is logged in =====
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// ===== Fetch student info =====
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

// ===== Log the action =====
addLog($conn, $student_id, 'student', 'Viewed profile');

$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Profile</title>
    <link rel="stylesheet" href="student.css">
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="student.php">Profile</a></li>
                <li><a href="subjects.php">Subjects</a></li>
                <li><a href="logout.php" class="logout">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="profile-container">
        <h2>STUDENT PROFILE</h2>

        <div class="profile-section">
            <h3>Personal Information</h3>
            <div class="profile-grid">
                <p><strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']) ?></p>
                <p><strong>Name:</strong>
                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name'] . ' ' . $student['extension_name']) ?>
                </p>
                <p><strong>Gender:</strong> <?= htmlspecialchars($student['gender']) ?></p>
                <p><strong>Birthday:</strong> <?= htmlspecialchars($student['birthday']) ?></p>
                <p><strong>Contact:</strong> <?= htmlspecialchars($student['contact']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($student['address']) ?></p>
            </div>
        </div>

        <div class="profile-section">
            <h3>Educational Background</h3>
            <div class="profile-grid">
                <p><strong>Last School Attended:</strong> <?= htmlspecialchars($student['last_school']) ?></p>
                <p><strong>School Address:</strong> <?= htmlspecialchars($student['school_address']) ?></p>
                <p><strong>Date Attended:</strong> <?= htmlspecialchars($student['date_attended']) ?></p>
                <p><strong>Honors Received:</strong> <?= htmlspecialchars($student['honors_received']) ?></p>
            </div>
        </div>

        <div class="profile-section">
            <h3>Family Information</h3>
            <div class="profile-grid">
                <p><strong>Father's Name:</strong> <?= htmlspecialchars($student['father_name']) ?></p>
                <p><strong>Father's Occupation:</strong> <?= htmlspecialchars($student['father_occupation']) ?></p>
                <p><strong>Mother's Name:</strong> <?= htmlspecialchars($student['mother_name']) ?></p>
                <p><strong>Mother's Occupation:</strong> <?= htmlspecialchars($student['mother_occupation']) ?></p>
                <p><strong>Guardian's Name:</strong> <?= htmlspecialchars($student['guardian_name']) ?></p>
                <p><strong>Guardian Contact:</strong> <?= htmlspecialchars($student['guardian_contact']) ?></p>
            </div>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const navbarLinks = document.getElementById('navbarLinks');

        menuToggle.addEventListener('click', () => {
            navbarLinks.classList.toggle('active');
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