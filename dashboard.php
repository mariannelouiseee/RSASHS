<?php
include("connect.php");
session_start();

if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('You must be logged in as admin.'); window.location='login.php';</script>";
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
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

    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <div class="profile-picture">
                    <?php if (!empty($student['student_image']) && file_exists("uploads/" . $student['student_image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($student['student_image']) ?>" alt="Profile Photo">
                    <?php else: ?>
                        <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                    <?php endif; ?>
                </div>


                <div class="welcome-text">
                    <h1>Welcome, <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>! 👋</h1>
                    <div class="greeting" id="dynamicGreeting">Good morning! Ready for another great day of learning?
                    </div>
                    <div class="student-id">Student ID: <?= htmlspecialchars($student['student_id']) ?></div>
                </div>

                <div class="academic-info">
                    <h3>Academic Information</h3>
                    <div class="info-item">
                        <div class="info-label">Grade Level & Section</div>
                        <div class="info-value">
                            <?= htmlspecialchars(($student['year_level'] ?? '') . ' - ' . ($student['section_name'] ?? '')) ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">School Year</div>
                        <div class="info-value"><?= htmlspecialchars($student['school_year'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const navbarLinks = document.getElementById('navbarLinks').querySelector('ul');

        menuToggle.addEventListener('click', () => {
            navbarLinks.classList.toggle('active');
        });

        function updateGreeting() {
            const now = new Date();
            const hour = now.getHours();
            const greetingElement = document.getElementById('dynamicGreeting');
            let greeting;

            if (hour < 12) {
                greeting = "Good morning! Ready for another great day of learning?";
            } else if (hour < 17) {
                greeting = "Good afternoon! Hope you're having a productive day!";
            } else {
                greeting = "Good evening! Time to wrap up your studies for today!";
            }

            greetingElement.textContent = greeting;
        }

        updateGreeting();
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