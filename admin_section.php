<?php
include("connect.php");
include("functions.php");

session_start();

if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('You must be logged in as admin.'); window.location='login.php';</script>";
    exit();
}

addLog($conn, $_SESSION['admin_id'], 'admin', "Visited Manage Sections page");

// ================= HANDLE ADD SECTION =================
if (isset($_POST['add_section'])) {
    $year_level   = $_POST['year_level'];
    $strand       = $_POST['strand'];
    $section_name = $_POST['section_name'];
    $school_year  = $_POST['school_year'];
    $adviser_id   = $_POST['adviser_id'] ?? null;

    $stmt = $conn->prepare("INSERT INTO sections (year_level, strand, section_name, school_year, adviser_id) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) die("Prepare failed (Add Section): " . $conn->error);
    $stmt->bind_param("sssss", $year_level, $strand, $section_name, $school_year, $adviser_id);

    if ($stmt->execute()) {
        addLog($conn, $_SESSION['admin_id'], 'admin', "Added section: $section_name ($year_level - $strand, $school_year) with adviser ID $adviser_id");
        echo "<script>alert('Section added successfully!'); window.location='admin_section.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error adding section. Please try again.');</script>";
    }
}

// ================= HANDLE ASSIGNMENT / REASSIGNMENT =================
if (isset($_POST['assign_students'])) {
    $year_level   = $_POST['year_level'];
    $section_name = $_POST['section_name'];
    $subjects_raw = $_POST['subjects'] ?? [];
    $student_ids  = $_POST['student_ids'] ?? [];

    $stmt_sec = $conn->prepare("SELECT section_id, school_year FROM sections WHERE year_level=? AND section_name=? LIMIT 1");
    $stmt_sec->bind_param("ss", $year_level, $section_name);
    $stmt_sec->execute();
    $res_sec = $stmt_sec->get_result()->fetch_assoc();

    $section_id  = $res_sec['section_id'] ?? null;
    $school_year = $res_sec['school_year'] ?? null;

    if (!$section_id) {
        echo "<script>alert('Section not found.');</script>";
        exit();
    }

    $stmt_del = $conn->prepare("DELETE FROM section_subjects WHERE section_id=?");
    if ($stmt_del) {
        $stmt_del->bind_param("i", $section_id);
        $stmt_del->execute();
        $stmt_del->close();
    } else die("Delete failed: " . $conn->error);

    foreach ($subjects_raw as $subject_id) {
        $subject_id = intval($subject_id);
        $stmt_ins = $conn->prepare("INSERT INTO section_subjects (section_id, subject_id) VALUES (?, ?)");
        $stmt_ins->bind_param("ii", $section_id, $subject_id);
        $stmt_ins->execute();
        $stmt_ins->close();
    }

    $stmt_unassign = $conn->prepare("UPDATE students SET year_level=NULL, section_name=NULL, school_year=NULL WHERE section_name=? AND year_level=?");
    $stmt_unassign->bind_param("ss", $section_name, $year_level);
    $stmt_unassign->execute();

    foreach ($student_ids as $id) {
        $stmt = $conn->prepare("UPDATE students SET year_level=?, section_name=?, school_year=? WHERE student_id=?");
        $stmt->bind_param("ssss", $year_level, $section_name, $school_year, $id);
        $stmt->execute();
    }

    addLog($conn, $_SESSION['admin_id'], 'admin', "Assigned/Reassigned students and subjects for section: $section_name ($year_level)");
    echo "<script>alert('Students and subjects assigned/reassigned successfully!'); window.location='admin_section.php';</script>";
    exit();
}

// ================= HANDLE DELETE SECTION =================
if (isset($_POST['delete_section'])) {
    $section_id = $_POST['section_id'];

    $stmt_get = $conn->prepare("SELECT year_level, section_name FROM sections WHERE section_id=?");
    if (!$stmt_get) die("Prepare failed (Get Section): " . $conn->error);
    $stmt_get->bind_param("i", $section_id);
    $stmt_get->execute();
    $section = $stmt_get->get_result()->fetch_assoc();

    if ($section) {
        $stmt_unassign_students = $conn->prepare(
            "UPDATE students SET year_level=NULL, section_name=NULL, subjects=NULL, subject_teachers=NULL WHERE section_name=? AND year_level=?"
        );
        if (!$stmt_unassign_students) die("Prepare failed (Unassign on Delete): " . $conn->error);
        $stmt_unassign_students->bind_param("ss", $section['section_name'], $section['year_level']);
        $stmt_unassign_students->execute();

        $stmt_delete = $conn->prepare("DELETE FROM sections WHERE section_id=?");
        if (!$stmt_delete) die("Prepare failed (Delete Section): " . $conn->error);
        $stmt_delete->bind_param("i", $section_id);

        if ($stmt_delete->execute()) {
            addLog($conn, $_SESSION['admin_id'], 'admin', "Deleted section: {$section['section_name']} ({$section['year_level']})");
            echo "<script>alert('Section deleted successfully!'); window.location='admin_section.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error deleting section. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Section not found.');</script>";
    }
}

// ================= PAGINATION =================
$records_per_page = 10;
$page        = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset      = ($page - 1) * $records_per_page;
$filter_year = $_GET['filter_year'] ?? '';

// Count total
if (!empty($filter_year)) {
    $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM sections WHERE year_level = ?");
    $count_stmt->bind_param("s", $filter_year);
    $count_stmt->execute();
    $total_records = (int)$count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $total_records = (int)$conn->query("SELECT COUNT(*) AS total FROM sections")->fetch_assoc()['total'];
}

$total_pages = $total_records > 0 ? ceil($total_records / $records_per_page) : 1;
if ($page > $total_pages) $page = $total_pages;

// Fetch paginated sections
if (!empty($filter_year)) {
    $stmt = $conn->prepare("SELECT * FROM sections WHERE year_level=? ORDER BY section_name LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $filter_year, $records_per_page, $offset);
} else {
    $stmt = $conn->prepare("SELECT * FROM sections ORDER BY year_level, section_name LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $records_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// Pagination URL helper
function paginationUrl($page, $extras = [])
{
    $params = array_merge($_GET, $extras, ['page' => $page]);
    return '?' . http_build_query($params);
}

function renderPagination($current_page, $total_pages)
{
    if ($total_pages <= 1) return '';
    $window = 2;
    $start  = max(1, $current_page - $window);
    $end    = min($total_pages, $current_page + $window);

    $html = '<div class="pagination-wrapper"><ul class="pagination">';

    $html .= $current_page <= 1
        ? '<li class="disabled"><span><i class="fas fa-chevron-left"></i></span></li>'
        : '<li><a href="' . paginationUrl($current_page - 1) . '"><i class="fas fa-chevron-left"></i></a></li>';

    if ($start > 1) {
        $html .= '<li><a href="' . paginationUrl(1) . '">1</a></li>';
        if ($start > 2) $html .= '<li class="disabled"><span>&hellip;</span></li>';
    }

    for ($i = $start; $i <= $end; $i++) {
        $html .= $i === $current_page
            ? '<li class="active"><span>' . $i . '</span></li>'
            : '<li><a href="' . paginationUrl($i) . '">' . $i . '</a></li>';
    }

    if ($end < $total_pages) {
        if ($end < $total_pages - 1) $html .= '<li class="disabled"><span>&hellip;</span></li>';
        $html .= '<li><a href="' . paginationUrl($total_pages) . '">' . $total_pages . '</a></li>';
    }

    $html .= $current_page >= $total_pages
        ? '<li class="disabled"><span><i class="fas fa-chevron-right"></i></span></li>'
        : '<li><a href="' . paginationUrl($current_page + 1) . '"><i class="fas fa-chevron-right"></i></a></li>';

    $html .= '</ul></div>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Sections</title>
    <link rel="stylesheet" href="admin_account.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
    <style>
        /* Pagination Styles */
        .pagination-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 18px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 4px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .pagination li a,
        .pagination li span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 8px;
            border-radius: 6px;
            border: 1px solid #ddd;
            background: #fff;
            color: #333;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, border-color 0.2s;
        }

        .pagination li a:hover {
            background: #e8f5e9;
            border-color: #2e7d32;
            color: #2e7d32;
        }

        .pagination li.active span {
            background: #2e7d32;
            border-color: #2e7d32;
            color: #fff;
            font-weight: bold;
            cursor: default;
        }

        .pagination li.disabled span {
            background: #f5f5f5;
            border-color: #e0e0e0;
            color: #aaa;
            cursor: not-allowed;
        }
    </style>
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
                    <a href="#" class="dropdown-toggle"><i class="fas fa-users"></i> Accounts <i class="fas fa-caret-down arrow"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="admin_account.php?role=student"><i class="fas fa-user-graduate"></i> Student</a></li>
                        <li><a href="admin_account.php?role=teacher"><i class="fas fa-chalkboard-teacher"></i> Teacher</a></li>
                    </ul>
                </li>
                <li><a href="admin_subject.php"><i class="fas fa-book-open"></i> Subjects</a></li>
                <li><a href="admin_section.php" class="active"><i class="fas fa-layer-group"></i> Section</a></li>
                <li><a href="admin_grade.php"><i class="fas fa-clipboard-list"></i> Grades</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        <div id="sidebarOverlay"></div>

        <main class="main-content">
            <h2>MANAGE SECTIONS</h2>

            <!-- ADD SECTION FORM -->
            <form method="POST" style="margin-bottom:20px;">
                <select name="year_level" required>
                    <option value="">Select Year Level</option>
                    <option value="Grade 11">Grade 11</option>
                    <option value="Grade 12">Grade 12</option>
                </select>
                <select name="strand" required>
                    <option value="">Select Strand</option>
                    <option value="STEM">STEM</option>
                    <option value="ABM">ABM</option>
                    <option value="HUMSS">HUMSS</option>
                    <option value="TVL">TVL</option>
                </select>
                <input type="text" name="section_name" placeholder="Section Name" required>
                <input type="text" name="school_year" placeholder="School Year (e.g., 2025-2026)" required>
                <select name="adviser_id" required>
                    <option value="">Select Adviser</option>
                    <?php
                    $teachers = $conn->query("SELECT teacher_id, CONCAT(first_name,' ',last_name) AS name FROM teachers ORDER BY first_name");
                    while ($teacher = $teachers->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($teacher['teacher_id']) . '">'
                            . htmlspecialchars($teacher['name']) . '</option>';
                    }
                    ?>
                </select>
                <button type="submit" name="add_section" class="btn-add">Add Section</button>
            </form>

            <!-- FILTER -->
            <form method="GET" style="margin-bottom:15px; align-items:center;">
                <label for="filter_year">Filter by Year Level:</label>
                <select name="filter_year" id="filter_year" onchange="this.form.submit()">
                    <option value="">All Year Levels</option>
                    <option value="Grade 11" <?= ($filter_year == 'Grade 11') ? 'selected' : '' ?>>Grade 11</option>
                    <option value="Grade 12" <?= ($filter_year == 'Grade 12') ? 'selected' : '' ?>>Grade 12</option>
                </select>
            </form>

            <!-- SECTIONS TABLE -->
            <table>
                <thead>
                    <tr>
                        <th>Year Level</th>
                        <th>Strand</th>
                        <th>Section</th>
                        <th>Adviser</th>
                        <th>School Year</th>
                        <th>Subjects</th>
                        <th>Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                            $checkStudents = $conn->prepare("SELECT COUNT(*) AS cnt FROM students WHERE year_level = ? AND section_name = ?");
                            $checkStudents->bind_param("ss", $row['year_level'], $row['section_name']);
                            $checkStudents->execute();
                            $countResult = $checkStudents->get_result()->fetch_assoc();
                            $hasStudents = $countResult['cnt'] > 0;
                    ?>
                            <tr>
                                <td><?= htmlspecialchars($row['year_level']) ?></td>
                                <td><?= htmlspecialchars($row['strand']) ?></td>
                                <td><?= htmlspecialchars($row['section_name']) ?></td>
                                <td>
                                    <?php
                                    if ($row['adviser_id']) {
                                        $stmt_adv = $conn->prepare("SELECT CONCAT(first_name,' ',last_name) AS name FROM teachers WHERE teacher_id=?");
                                        $stmt_adv->bind_param("i", $row['adviser_id']);
                                        $stmt_adv->execute();
                                        $adv_name = $stmt_adv->get_result()->fetch_assoc()['name'] ?? 'No adviser assigned';
                                        echo htmlspecialchars($adv_name);
                                        $stmt_adv->close();
                                    } else {
                                        echo 'No adviser assigned';
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($row['school_year']) ?></td>
                                <td>
                                    <?php
                                    $stmt_sub = $conn->prepare("
                                    SELECT s.subject_name, s.category, t.first_name, t.last_name
                                    FROM section_subjects ss
                                    JOIN subjects s ON ss.subject_id = s.subject_id
                                    LEFT JOIN teachers t ON s.teacher_id = t.teacher_id
                                    WHERE ss.section_id = ?
                                    ORDER BY s.category, s.subject_name
                                ");
                                    $stmt_sub->bind_param("i", $row['section_id']);
                                    $stmt_sub->execute();
                                    $res_sub = $stmt_sub->get_result();

                                    $subjects_by_category = [];
                                    if ($res_sub->num_rows > 0) {
                                        while ($sub = $res_sub->fetch_assoc()) {
                                            $category     = $sub['category'] ?: "Uncategorized";
                                            $teacher_name = $sub['first_name'] ? $sub['first_name'] . " " . $sub['last_name'] : "No teacher";
                                            $subjects_by_category[$category][] = $sub['subject_name'] . " (Teacher: $teacher_name)";
                                        }
                                        foreach ($subjects_by_category as $cat => $subjects_list) {
                                            echo "<strong>" . htmlspecialchars($cat) . ":</strong><br>";
                                            echo implode("<br>", array_map('htmlspecialchars', $subjects_list));
                                            echo "<br><br>";
                                        }
                                    } else {
                                        echo "No subjects assigned";
                                    }
                                    $stmt_sub->close();
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $stmt_students = $conn->prepare("
                                    SELECT CONCAT(first_name, ' ', last_name) AS name
                                    FROM students
                                    WHERE year_level=? AND section_name=? AND school_year=?
                                ");
                                    $stmt_students->bind_param("sss", $row['year_level'], $row['section_name'], $row['school_year']);
                                    $stmt_students->execute();
                                    $res_students = $stmt_students->get_result();

                                    if ($res_students->num_rows > 0) {
                                        while ($stu = $res_students->fetch_assoc()) {
                                            echo htmlspecialchars($stu['name']) . "<br>";
                                        }
                                    } else {
                                        echo "No students assigned";
                                    }
                                    $stmt_students->close();
                                    ?>
                                </td>
                                <td>
                                    <?php if ($hasStudents): ?>
                                        <button class="btn-change assignBtn"
                                            data-year="<?= htmlspecialchars($row['year_level']) ?>"
                                            data-section="<?= htmlspecialchars($row['section_name']) ?>"
                                            data-mode="reassign" title="Reassign Students">
                                            <i class="fas fa-user-edit"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-change assignBtn"
                                            data-year="<?= htmlspecialchars($row['year_level']) ?>"
                                            data-section="<?= htmlspecialchars($row['section_name']) ?>"
                                            data-mode="assign" title="Assign Students">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    <?php endif; ?>

                                    <form method="POST" style="display:inline;"
                                        onsubmit="return confirm('Are you sure you want to delete this section?');">
                                        <input type="hidden" name="section_id" value="<?= htmlspecialchars($row['section_id']) ?>">
                                        <button type="submit" name="delete_section" class="btn-del" title="Delete Section">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">No sections found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?= renderPagination($page, $total_pages) ?>

        </main>
    </div>

    <!-- ASSIGN/REASSIGN MODAL -->
    <div id="assignStudentsModal" class="modal">
        <div class="modal-content" style="width:700px; max-width:95%;">
            <span class="close" onclick="closeAssignModal()">&times;</span>
            <h3 id="modalTitle">Assign Students to Section</h3>
            <form method="POST">
                <input type="hidden" name="year_level" id="year_level" readonly>
                <input type="hidden" name="section_name" id="section_name" readonly>

                <div class="mb-3">
                    <label>Year Level: </label>
                    <input type="text" id="year_level_display" readonly>
                </div>
                <div class="mb-3">
                    <label>Section: </label>
                    <input type="text" id="section_name_display" readonly>
                </div>

                <div class="mb-3">
                    <label>Subjects:</label><br>
                    <?php
                    $subjectResult = $conn->query("
                        SELECT s.subject_id, s.subject_name, s.teacher_id, CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
                        FROM subjects s
                        LEFT JOIN teachers t ON s.teacher_id = t.teacher_id
                        ORDER BY s.subject_name ASC
                    ");
                    while ($row = $subjectResult->fetch_assoc()) {
                        $teacherDisplay = $row['teacher_name'] ? " (Teacher: " . $row['teacher_name'] . ")" : " (No teacher assigned)";
                        echo '<label><input type="checkbox" name="subjects[]" value="' . intval($row['subject_id']) . '"> '
                            . htmlspecialchars($row['subject_name']) . ' ' . htmlspecialchars($teacherDisplay) . '</label><br>';
                    }
                    ?>
                </div>

                <h4>Select Students</h4>
                <div id="studentList" style="max-height:250px; overflow-y:auto; border:1px solid #ccc; padding:5px;"></div>

                <br>
                <button type="submit" name="assign_students" class="btn-change">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll(".dropdown-toggle").forEach(toggle => {
            toggle.addEventListener("click", function(e) {
                e.preventDefault();
                const parent = this.parentElement;
                parent.classList.toggle("open");
                document.querySelectorAll(".dropdown").forEach(item => {
                    if (item !== parent) item.classList.remove("open");
                });
            });
        });

        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const container = document.querySelector('.container');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            container.classList.toggle('sidebar-active');
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            container.classList.remove('sidebar-active');
        });

        document.querySelectorAll(".assignBtn").forEach(btn => {
            btn.addEventListener("click", function() {
                const year = this.dataset.year;
                const section = this.dataset.section;
                const mode = this.dataset.mode;

                document.getElementById("year_level").value = year;
                document.getElementById("section_name").value = section;
                document.getElementById("year_level_display").value = year;
                document.getElementById("section_name_display").value = section;
                document.getElementById("modalTitle").textContent = mode === "reassign" ? "Reassign Students" : "Assign Students";

                document.getElementById("studentList").innerHTML = "";
                document.querySelectorAll('input[name="subjects[]"]').forEach(cb => cb.checked = false);

                fetch(`fetch_students.php?year_level=${encodeURIComponent(year)}&section_name=${encodeURIComponent(section)}&mode=${mode}`)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById("studentList").innerHTML = html;

                        if (mode === "reassign") {
                            fetch(`fetch_subjects.php?year_level=${encodeURIComponent(year)}&section_name=${encodeURIComponent(section)}`)
                                .then(res => res.json())
                                .then(subjects => {
                                    document.querySelectorAll('input[name="subjects[]"]').forEach(cb => {
                                        if (subjects.includes(cb.value)) cb.checked = true;
                                    });
                                });
                        }
                    });

                document.getElementById("assignStudentsModal").style.display = "block";
            });
        });

        function closeAssignModal() {
            document.getElementById("assignStudentsModal").style.display = "none";
        }

        window.onclick = function(event) {
            const modal = document.getElementById("assignStudentsModal");
            if (event.target === modal) closeAssignModal();
        };

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
                    <strong style="font-size:16px; color:#1b5e20;">Inactivity Warning</strong>
                    <span>You have been inactive. You will be logged out in <span id="countdown">10</span> seconds.</span>
                    <button id="stayLoggedIn" style="padding:8px 12px;background:#2e7d32;color:white;border:none;border-radius:6px;font-weight:bold;cursor:pointer;align-self:flex-end;transition:background 0.3s;">Stay Logged In</button>
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
                    .catch(() => {
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