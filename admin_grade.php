<?php
include("connect.php");
include("functions.php");

session_start();

if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('You must be logged in as admin.'); window.location='login.php';</script>";
    exit();
}

addLog($conn, $_SESSION['admin_id'], 'admin', "Visited Teachers page");

$sql = "SELECT teacher_id, first_name, middle_name, last_name, teacher_image FROM teachers";
$result = $conn->query($sql);
if (!$result) die("Query failed: " . $conn->error);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Grades - Teachers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="admin_grade.css">
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
</head>

<body>
    <header>
        <div class="header-left">
            <img src="./img/logo.jpg" alt="RSASHS Logo" />
            <h2>RSASHS E-PORTAL</h2>
        </div>
        <button id="sidebarToggle" class="sidebar-toggle" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
    </header>

    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-users"></i> Accounts
                        <i class="fas fa-caret-down arrow"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="admin_account.php?role=student"><i class="fas fa-user-graduate"></i> Student</a></li>
                        <li><a href="admin_account.php?role=teacher" class="active"><i class="fas fa-chalkboard-teacher"></i> Teacher</a></li>
                    </ul>
                </li>
                <li><a href="admin_subject.php"><i class="fas fa-book-open"></i> Subjects</a></li>
                <li><a href="admin_section.php"><i class="fas fa-layer-group"></i> Section</a></li>
                <li><a href="admin_grade.php" class="active"><i class="fas fa-clipboard-list"></i> Grades</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="content">
            <h2>TEACHERS</h2>
            <div class="teacher-list">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <a href="#" class="teacher-card" data-teacher-id="<?= htmlspecialchars($row['teacher_id']); ?>">
                            <?php if (!empty($row['teacher_image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($row['teacher_image']); ?>" alt="Teacher Image">
                            <?php else: ?>
                                <img src="img/default_teacher.png" alt="No Image">
                            <?php endif; ?>
                            <h3><?= htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></h3>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No teachers found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Teacher Modal -->
    <div id="teacherModal" class="modal">
        <div class="modal-content modal-split">
            <div class="teacher-panel">
                <span class="close">&times;</span>
                <h2 id="teacherName">Subjects</h2>
                <div class="modal-body">
                    <table id="subjectTable">
                        <thead>
                            <tr>
                                <th>Section</th>
                                <th>Subject</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="subjectList"></tbody>
                    </table>
                </div>
            </div>

            <div class="student-panel" id="studentPanel">
                <h2 id="studentTitle">Students</h2>
                <div class="modal-body">
                    <table id="studentTable">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Full Name</th>
                                <th>Q1</th>
                                <th>Q2</th>
                                <th>1st Sem Final</th>
                                <th>GWA 1st Sem</th>
                                <th>Q3</th>
                                <th>Q4</th>
                                <th>2nd Sem Final</th>
                                <th>GWA 2nd Sem</th>
                                <th>Final</th>
                            </tr>
                        </thead>
                        <tbody id="studentList"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll(".dropdown-toggle").forEach(toggle => {
            toggle.addEventListener("click", e => {
                e.preventDefault();
                const parent = toggle.parentElement;
                parent.classList.toggle("open");
                document.querySelectorAll(".dropdown").forEach(item => {
                    if (item !== parent) item.classList.remove("open");
                });
            });
        });

        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('active'));

        document.addEventListener("DOMContentLoaded", () => {
            const modal = document.getElementById("teacherModal");
            const closeBtn = modal.querySelector(".close");
            const subjectList = document.getElementById("subjectList");
            const teacherName = document.getElementById("teacherName");
            const studentPanel = document.getElementById("studentPanel");
            const studentList = document.getElementById("studentList");
            const studentTitle = document.getElementById("studentTitle");

            // Open teacher modal
            document.querySelectorAll(".teacher-card").forEach(card => {
                card.addEventListener("click", e => {
                    e.preventDefault();
                    const teacherId = card.dataset.teacherId;

                    // Clear old data
                    subjectList.innerHTML = `<tr><td colspan="3">Loading...</td></tr>`;
                    studentList.innerHTML = "";
                    studentPanel.classList.remove("active");

                    modal.style.display = "flex";

                    fetch(`fetch_teacher_subjects.php?teacher_id=${encodeURIComponent(teacherId)}`)
                        .then(res => res.json())
                        .then(data => {
                            teacherName.textContent = (data.teacher_name || "Teacher") + " - Subjects";

                            if (data.subjects && data.subjects.length > 0) {
                                // Group subjects by section
                                const sectionMap = {};
                                data.subjects.forEach(item => {
                                    const sectionKey = (item.year_level || "") + ' - ' + (item.section_name || "");
                                    if (!sectionMap[sectionKey]) sectionMap[sectionKey] = [];
                                    sectionMap[sectionKey].push(item);
                                });

                                // Build table rows with rowspan for section
                                subjectList.innerHTML = "";
                                Object.keys(sectionMap).forEach(sectionKey => {
                                    const subjects = sectionMap[sectionKey];
                                    subjects.forEach((subj, index) => {
                                        const row = document.createElement("tr");
                                        row.innerHTML = `
                                    ${index === 0 ? `<td rowspan="${subjects.length}">${sectionKey}</td>` : ""}
                                    <td>${subj.subject_name || ""}</td>
                                    <td>
                                        <button class="btn-view" 
                                            data-section-id="${subj.section_id}" 
                                            data-subject-id="${subj.subject_id}"
                                            data-section-name="${subj.section_name}" 
                                            data-subject-name="${subj.subject_name}">
                                            View Students
                                        </button>
                                    </td>
                                `;
                                        subjectList.appendChild(row);
                                    });
                                });
                            } else {
                                subjectList.innerHTML = `<tr><td colspan="3">No subjects assigned.</td></tr>`;
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            subjectList.innerHTML = `<tr><td colspan="3">Error loading subjects.</td></tr>`;
                        });
                });
            });

            // Open students for a subject
            document.addEventListener("click", e => {
                if (e.target.classList.contains("btn-view")) {
                    const sectionId = e.target.dataset.sectionId;
                    const subjectId = e.target.dataset.subjectId;
                    const sectionName = e.target.dataset.sectionName;
                    const subjectName = e.target.dataset.subjectName;

                    studentPanel.classList.add("active");
                    studentList.innerHTML = `<tr><td colspan="11">Loading...</td></tr>`;

                    fetch(`fetch_section_students.php?section_id=${encodeURIComponent(sectionId)}&subject_id=${encodeURIComponent(subjectId)}`)
                        .then(res => res.json())
                        .then(data => {
                            studentTitle.textContent = `${subjectName} | ${sectionName}`;
                            studentList.innerHTML = "";

                            if (data.students && data.students.length > 0) {
                                data.students.forEach(stu => {
                                    const row = document.createElement("tr");
                                    row.innerHTML = `
                            <td>${stu.student_id || ""}</td>
                            <td>${stu.full_name || ""}</td>
                            <td>${stu.q1 || "-"}</td>
                            <td>${stu.q2 || "-"}</td>
                            <td>${stu.first_sem_final || "-"}</td>
                            <td>${stu.gwa_first_sem || "-"}</td>
                            <td>${stu.q3 || "-"}</td>
                            <td>${stu.q4 || "-"}</td>
                            <td>${stu.second_sem_final || "-"}</td>
                            <td>${stu.gwa_second_sem || "-"}</td>
                            <td>${stu.final || "-"}</td>
                        `;
                                    studentList.appendChild(row);
                                });
                            } else {
                                studentList.innerHTML = `<tr><td colspan="11">No students found.</td></tr>`;
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            studentList.innerHTML = `<tr><td colspan="11">Error loading students.</td></tr>`;
                        });
                }
            });


            // Close modal
            closeBtn.addEventListener("click", () => {
                modal.style.display = "none";
                studentPanel.classList.remove("active");
                studentList.innerHTML = "";
                subjectList.innerHTML = "";
            });

            window.addEventListener("click", e => {
                if (e.target === modal) {
                    modal.style.display = "none";
                    studentPanel.classList.remove("active");
                    studentList.innerHTML = "";
                    subjectList.innerHTML = "";
                }
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