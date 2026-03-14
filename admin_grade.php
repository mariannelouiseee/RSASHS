<?php
include("connect.php");
include("functions.php");

session_start();

if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('You must be logged in as admin.'); window.location='login.php';</script>";
    exit();
}

addLog($conn, $_SESSION['admin_id'], 'admin', "Visited Teachers page");

$sql = "SELECT teacher_id, first_name, middle_name, last_name, teacher_image FROM teachers ORDER BY last_name ASC";
$result = $conn->query($sql);
if (!$result) die("Query failed: " . $conn->error);

$teacher_count = $result->num_rows;

// Build initials helper colors
$banner_colors = [
    ['banner' => 'linear-gradient(135deg, #2e7d32, #43a047)', 'bg' => '#c8e6c9', 'text' => '#2e7d32'],
    ['banner' => 'linear-gradient(135deg, #1565c0, #1976d2)', 'bg' => '#bbdefb', 'text' => '#1565c0'],
    ['banner' => 'linear-gradient(135deg, #6a1b9a, #8e24aa)', 'bg' => '#e1bee7', 'text' => '#6a1b9a'],
    ['banner' => 'linear-gradient(135deg, #e65100, #ef6c00)', 'bg' => '#ffe0b2', 'text' => '#e65100'],
    ['banner' => 'linear-gradient(135deg, #00695c, #00897b)', 'bg' => '#b2dfdb', 'text' => '#00695c'],
    ['banner' => 'linear-gradient(135deg, #ad1457, #c2185b)', 'bg' => '#fce4ec', 'text' => '#ad1457'],
];

$teachers = [];
$i = 0;
while ($row = $result->fetch_assoc()) {
    $row['_color'] = $banner_colors[$i % count($banner_colors)];
    $initials = strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1));
    $row['_initials'] = $initials;
    $teachers[] = $row;
    $i++;
}
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
                        <li><a href="admin_account.php?role=teacher"><i class="fas fa-chalkboard-teacher"></i> Teacher</a></li>
                    </ul>
                </li>
                <li><a href="admin_subject.php"><i class="fas fa-book-open"></i> Subjects</a></li>
                <li><a href="admin_section.php"><i class="fas fa-layer-group"></i> Section</a></li>
                <li><a href="admin_grade.php" class="active"><i class="fas fa-clipboard-list"></i> Grades</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="content">
            <!-- Section Header -->
            <div class="section-header">
                <h2 class="section-title">Teachers</h2>
                <div class="header-controls">
                    <div class="teacher-search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" id="teacherSearch" placeholder="Search teacher..." />
                    </div>
                    <span class="teacher-count-badge" id="teacherCountBadge">
                        <?= $teacher_count ?> Teacher<?= $teacher_count !== 1 ? 's' : '' ?>
                    </span>
                </div>
            </div>

            <!-- Teacher Grid -->
            <div class="teacher-grid" id="teacherGrid">
                <?php if (!empty($teachers)): ?>
                    <?php foreach ($teachers as $row): ?>
                        <?php $c = $row['_color']; ?>
                        <a href="#" class="teacher-card" data-teacher-id="<?= htmlspecialchars($row['teacher_id']); ?>"
                            data-name="<?= htmlspecialchars(strtolower($row['first_name'] . ' ' . $row['last_name'])); ?>">

                            <div class="card-banner" style="background: <?= $c['banner']; ?>">
                                <?php if (!empty($row['teacher_image'])): ?>
                                    <div class="card-avatar">
                                        <img src="uploads/<?= htmlspecialchars($row['teacher_image']); ?>" alt="Photo" />
                                    </div>
                                <?php else: ?>
                                    <div class="card-avatar" style="background: <?= $c['bg']; ?>; color: <?= $c['text']; ?>;">
                                        <?= htmlspecialchars($row['_initials']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <div class="card-name">
                                    <?= htmlspecialchars($row['first_name'] . ' ' . (!empty($row['middle_name']) ? $row['middle_name'][0] . '. ' : '') . $row['last_name']); ?>
                                </div>
                                <div class="card-id"><?= htmlspecialchars($row['teacher_id']); ?></div>
                            </div>

                            <div class="card-footer">
                                <i class="fas fa-book-open" style="font-size:12px;"></i>
                                View Subjects
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-results">No teachers found.</p>
                <?php endif; ?>
            </div>

            <!-- Empty search state -->
            <div id="noSearchResults" style="display:none; text-align:center; padding: 40px 0; color: #888;">
                <i class="fas fa-user-slash" style="font-size:36px; margin-bottom:12px; color:#c8e6c9;"></i>
                <p style="font-size:15px;">No teachers match your search.</p>
            </div>
        </main>
    </div>

    <!-- Teacher Modal -->
    <div id="teacherModal" class="modal">
        <div class="modal-content modal-split">
            <div class="teacher-panel">
                <div class="modal-panel-header">
                    <h2 id="teacherName">Subjects</h2>
                    <span class="close">&times;</span>
                </div>
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
                <div class="modal-panel-header">
                    <h2 id="studentTitle">Students</h2>
                </div>
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
        // Sidebar dropdown
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

        // Sidebar toggle
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('active'));

        // Teacher search filter
        const teacherSearch = document.getElementById('teacherSearch');
        const teacherGrid = document.getElementById('teacherGrid');
        const noResults = document.getElementById('noSearchResults');
        const countBadge = document.getElementById('teacherCountBadge');

        teacherSearch.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            const cards = teacherGrid.querySelectorAll('.teacher-card');
            let visible = 0;

            cards.forEach(card => {
                const name = card.dataset.name || '';
                const id = (card.dataset.teacherId || '').toLowerCase();
                const show = !q || name.includes(q) || id.includes(q);
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            noResults.style.display = visible === 0 ? 'block' : 'none';
            countBadge.textContent = visible + ' Teacher' + (visible !== 1 ? 's' : '');
        });

        // Modal logic
        document.addEventListener("DOMContentLoaded", () => {
            const modal = document.getElementById("teacherModal");
            const closeBtn = modal.querySelector(".close");
            const subjectList = document.getElementById("subjectList");
            const teacherName = document.getElementById("teacherName");
            const studentPanel = document.getElementById("studentPanel");
            const studentList = document.getElementById("studentList");
            const studentTitle = document.getElementById("studentTitle");

            document.querySelectorAll(".teacher-card").forEach(card => {
                card.addEventListener("click", e => {
                    e.preventDefault();
                    const teacherId = card.dataset.teacherId;

                    subjectList.innerHTML = `<tr><td colspan="3" style="text-align:center;padding:14px;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>`;
                    studentList.innerHTML = "";
                    studentPanel.classList.remove("active");
                    modal.style.display = "flex";

                    fetch(`fetch_teacher_subjects.php?teacher_id=${encodeURIComponent(teacherId)}`)
                        .then(res => res.json())
                        .then(data => {
                            teacherName.textContent = (data.teacher_name || "Teacher") + " — Subjects";

                            if (data.subjects && data.subjects.length > 0) {
                                const sectionMap = {};
                                data.subjects.forEach(item => {
                                    const key = (item.year_level || '') + ' - ' + (item.section_name || '');
                                    if (!sectionMap[key]) sectionMap[key] = [];
                                    sectionMap[key].push(item);
                                });

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
                                                    <i class="fas fa-eye" style="margin-right:4px;"></i>View Students
                                                </button>
                                            </td>`;
                                        subjectList.appendChild(row);
                                    });
                                });
                            } else {
                                subjectList.innerHTML = `<tr><td colspan="3" style="text-align:center;padding:14px;color:#888;">No subjects assigned.</td></tr>`;
                            }
                        })
                        .catch(() => {
                            subjectList.innerHTML = `<tr><td colspan="3" style="text-align:center;color:#e53935;">Error loading subjects.</td></tr>`;
                        });
                });
            });

            document.addEventListener("click", e => {
                if (e.target.classList.contains("btn-view") || e.target.closest(".btn-view")) {
                    const btn = e.target.classList.contains("btn-view") ? e.target : e.target.closest(".btn-view");
                    const sectionId = btn.dataset.sectionId;
                    const subjectId = btn.dataset.subjectId;
                    const sectionName = btn.dataset.sectionName;
                    const subjectName = btn.dataset.subjectName;

                    studentPanel.classList.add("active");
                    studentList.innerHTML = `<tr><td colspan="11" style="text-align:center;padding:14px;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>`;

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
                                        <td>${stu.final || "-"}</td>`;
                                    studentList.appendChild(row);
                                });
                            } else {
                                studentList.innerHTML = `<tr><td colspan="11" style="text-align:center;padding:14px;color:#888;">No students found.</td></tr>`;
                            }
                        })
                        .catch(() => {
                            studentList.innerHTML = `<tr><td colspan="11" style="text-align:center;color:#e53935;">Error loading students.</td></tr>`;
                        });
                }
            });

            function closeModal() {
                modal.style.display = "none";
                studentPanel.classList.remove("active");
                studentList.innerHTML = "";
                subjectList.innerHTML = "";
            }

            closeBtn.addEventListener("click", closeModal);
            window.addEventListener("click", e => {
                if (e.target === modal) closeModal();
            });
        });

        // Inactivity logout
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
                    <span>You will be logged out in <span id="countdown">10</span> seconds.</span>
                    <button id="stayLoggedIn" style="padding:8px 12px;background:#2e7d32;color:white;border:none;border-radius:6px;font-weight:bold;cursor:pointer;align-self:flex-end;">Stay Logged In</button>`;
                document.body.appendChild(warningDiv);
                setTimeout(() => warningDiv.style.opacity = 1, 10);

                let countdown = 10;
                const span = document.getElementById('countdown');
                const interval = setInterval(() => {
                    if (--countdown <= 0) clearInterval(interval);
                    span.textContent = countdown;
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
                    .then(d => {
                        alert(d.message || 'Logged out due to inactivity.');
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