<?php
include("connect.php");
include("functions.php");
session_start();

// ===== CHECK IF ADMIN IS LOGGED IN =====
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('You must be logged in as admin.'); window.location='login.php';</script>";
    exit();
}

// ===== LOG PAGE VISIT =====
addLog($conn, $_SESSION['admin_id'], 'admin', "Visited Manage Subjects page");

$success = $error = "";

function logAction($conn, $message)
{
    if (isset($_SESSION['admin_id'])) {
        addLog($conn, $_SESSION['admin_id'], 'admin', $message);
    }
}

// Fetch teachers
$teacher_list = [];
$teachers_result = $conn->query("SELECT teacher_id, first_name, last_name FROM teachers ORDER BY last_name ASC");
if ($teachers_result) {
    while ($t = $teachers_result->fetch_assoc()) {
        $teacher_list[] = $t;
    }
    $teachers_result->free();
}

// Handle adding subject
if (isset($_POST['add_subject'])) {
    $subject_name = trim($_POST['subject_name'] ?? '');
    $teacher_id   = trim($_POST['teacher_id']   ?? '');
    $category     = trim($_POST['category']      ?? '');
    $semester     = trim($_POST['semester']      ?? '');

    if ($subject_name && $teacher_id && $category && $semester) {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name, teacher_id, category, semester) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $subject_name, $teacher_id, $category, $semester);
            if ($stmt->execute()) {
                $success = "Subject added successfully!";
                logAction($conn, "Added subject: $subject_name (Teacher ID: $teacher_id, Category: $category, Semester: $semester)");
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Prepare failed: " . $conn->error;
        }
    } else {
        $error = "All fields are required.";
    }
}

// Handle delete subject
if (isset($_POST['delete_subject'])) {
    $subject_id = intval($_POST['subject_id'] ?? 0);
    if ($subject_id) {
        $stmt = $conn->prepare("DELETE FROM subjects WHERE subject_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $subject_id);
            if ($stmt->execute()) {
                $success = "Subject deleted successfully!";
                logAction($conn, "Deleted subject ID: $subject_id");
            } else {
                $error = "Error deleting subject: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Prepare failed: " . $conn->error;
        }
    } else {
        $error = "Invalid subject ID.";
    }
}

// Handle edit subject
if (isset($_POST['edit_subject'])) {
    $subject_id   = intval($_POST['subject_id']   ?? 0);
    $subject_name = trim($_POST['subject_name']    ?? '');
    $teacher_id   = trim($_POST['teacher_id']      ?? '');
    $category     = trim($_POST['category']        ?? '');
    $semester     = trim($_POST['semester']        ?? '');

    if ($subject_id && $subject_name && $teacher_id && $category && $semester) {
        $stmt = $conn->prepare("UPDATE subjects SET subject_name = ?, teacher_id = ?, category = ?, semester = ? WHERE subject_id = ?");
        if ($stmt) {
            $stmt->bind_param("ssssi", $subject_name, $teacher_id, $category, $semester, $subject_id);
            if ($stmt->execute()) {
                $success = "Subject updated successfully!";
                logAction($conn, "Edited subject ID: $subject_id to $subject_name (Teacher ID: $teacher_id, Category: $category, Semester: $semester)");
            } else {
                $error = "Error updating subject: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Prepare failed: " . $conn->error;
        }
    } else {
        $error = "All fields are required for editing.";
    }
}

// Pagination settings
$records_per_page = 10;

// Per-semester page params
$page_sem1 = isset($_GET['page_sem1']) && is_numeric($_GET['page_sem1']) ? max(1, (int)$_GET['page_sem1']) : 1;
$page_sem2 = isset($_GET['page_sem2']) && is_numeric($_GET['page_sem2']) ? max(1, (int)$_GET['page_sem2']) : 1;

$filter_teacher = $_GET['filter_teacher'] ?? '';

// Helper: build paginated query for a semester
function fetchSemesterSubjects($conn, $semester, $filter_teacher, $page, $records_per_page)
{
    $offset = ($page - 1) * $records_per_page;

    if (!empty($filter_teacher)) {
        $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM subjects s WHERE s.semester = ? AND s.teacher_id = ?");
        $count_stmt->bind_param("ss", $semester, $filter_teacher);
        $count_stmt->execute();
        $total = (int)$count_stmt->get_result()->fetch_assoc()['total'];
        $count_stmt->close();

        $stmt = $conn->prepare("
            SELECT s.subject_id, s.subject_name, s.teacher_id, s.category, s.semester,
                   t.first_name, t.last_name
            FROM subjects s
            LEFT JOIN teachers t ON s.teacher_id = t.teacher_id
            WHERE s.semester = ? AND s.teacher_id = ?
            ORDER BY s.subject_name ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("ssii", $semester, $filter_teacher, $records_per_page, $offset);
    } else {
        $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM subjects s WHERE s.semester = ?");
        $count_stmt->bind_param("s", $semester);
        $count_stmt->execute();
        $total = (int)$count_stmt->get_result()->fetch_assoc()['total'];
        $count_stmt->close();

        $stmt = $conn->prepare("
            SELECT s.subject_id, s.subject_name, s.teacher_id, s.category, s.semester,
                   t.first_name, t.last_name
            FROM subjects s
            LEFT JOIN teachers t ON s.teacher_id = t.teacher_id
            WHERE s.semester = ?
            ORDER BY s.subject_name ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("sii", $semester, $records_per_page, $offset);
    }

    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $total_pages = $total > 0 ? ceil($total / $records_per_page) : 1;
    return ['rows' => $rows, 'total' => $total, 'total_pages' => $total_pages];
}

$sem1_data = fetchSemesterSubjects($conn, '1st Semester', $filter_teacher, $page_sem1, $records_per_page);
$sem2_data = fetchSemesterSubjects($conn, '2nd Semester', $filter_teacher, $page_sem2, $records_per_page);

// Build pagination URL preserving all GET params except the target page param
function paginationUrl($param, $page, $extras = [])
{
    $params = array_merge($_GET, $extras, [$param => $page]);
    return '?' . http_build_query($params);
}

// Render pagination HTML
function renderPagination($current_page, $total_pages, $param, $extras = [])
{
    if ($total_pages <= 1) return '';
    $window = 2;
    $start  = max(1, $current_page - $window);
    $end    = min($total_pages, $current_page + $window);

    $html = '<div class="pagination-wrapper"><ul class="pagination">';

    // Prev
    if ($current_page <= 1) {
        $html .= '<li class="disabled"><span><i class="fas fa-chevron-left"></i></span></li>';
    } else {
        $html .= '<li><a href="' . paginationUrl($param, $current_page - 1, $extras) . '"><i class="fas fa-chevron-left"></i></a></li>';
    }

    if ($start > 1) {
        $html .= '<li><a href="' . paginationUrl($param, 1, $extras) . '">1</a></li>';
        if ($start > 2) $html .= '<li class="disabled"><span>&hellip;</span></li>';
    }

    for ($i = $start; $i <= $end; $i++) {
        if ($i === $current_page) {
            $html .= '<li class="active"><span>' . $i . '</span></li>';
        } else {
            $html .= '<li><a href="' . paginationUrl($param, $i, $extras) . '">' . $i . '</a></li>';
        }
    }

    if ($end < $total_pages) {
        if ($end < $total_pages - 1) $html .= '<li class="disabled"><span>&hellip;</span></li>';
        $html .= '<li><a href="' . paginationUrl($param, $total_pages, $extras) . '">' . $total_pages . '</a></li>';
    }

    // Next
    if ($current_page >= $total_pages) {
        $html .= '<li class="disabled"><span><i class="fas fa-chevron-right"></i></span></li>';
    } else {
        $html .= '<li><a href="' . paginationUrl($param, $current_page + 1, $extras) . '"><i class="fas fa-chevron-right"></i></a></li>';
    }

    $html .= '</ul></div>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="admin_account.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
    <style>
        /* Tab Styles */
        .tab-wrapper {
            display: flex;
            gap: 0;
            margin-top: 20px;
            border-bottom: 2px solid #2e7d32;
        }

        .tab-btn {
            padding: 10px 28px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #555;
            transition: background 0.2s, color 0.2s;
        }

        .tab-btn:hover {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .tab-btn.active {
            background: #2e7d32;
            color: #fff;
            border-color: #2e7d32;
        }

        .tab-panel {
            display: none;
            padding-top: 20px;
        }

        .tab-panel.active {
            display: block;
        }

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
                <li><a href="admin_subject.php" class="active"><i class="fas fa-book-open"></i> Subjects</a></li>
                <li><a href="admin_section.php"><i class="fas fa-layer-group"></i> Section</a></li>
                <li><a href="admin_grade.php"><i class="fas fa-clipboard-list"></i> Grades</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <div id="sidebarOverlay"></div>

        <main class="main-content">
            <h2>MANAGE SUBJECTS</h2>

            <?php if (!empty($success)): ?>
                <div class="alert success"><?= htmlspecialchars($success) ?></div>
            <?php elseif (!empty($error)): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- ADD SUBJECT FORM -->
            <form method="POST" class="form-card form-inline">
                <input type="text" id="subject_name" name="subject_name" placeholder="Subject Name" required>
                <select name="teacher_id" id="teacher_id" required>
                    <option value="">-- Select Teacher --</option>
                    <?php foreach ($teacher_list as $t): ?>
                        <option value="<?= htmlspecialchars($t['teacher_id']) ?>">
                            <?= htmlspecialchars($t['last_name'] . ", " . $t['first_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="category" id="category" required>
                    <option value="">-- Select Category --</option>
                    <option value="Core Subjects">Core Subjects</option>
                    <option value="Applied and Specialized Subjects">Applied and Specialized Subjects</option>
                </select>
                <select name="semester" id="semester" required>
                    <option value="">-- Select Semester --</option>
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                </select>
                <button type="submit" name="add_subject" class="btn-add">Add Subject</button>
            </form>

            <!-- FILTER BY TEACHER -->
            <form method="GET" style="margin-top:15px; margin-bottom:15px;">
                <label for="filter_teacher">Filter by Teacher:</label>
                <select name="filter_teacher" id="filter_teacher" onchange="this.form.submit()">
                    <option value="">All Teachers</option>
                    <?php foreach ($teacher_list as $t):
                        $selected = ($filter_teacher == $t['teacher_id']) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($t['teacher_id']) ?>" <?= $selected ?>>
                            <?= htmlspecialchars($t['last_name'] . ", " . $t['first_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <!-- TABS -->
            <div class="tab-wrapper">
                <button class="tab-btn active" onclick="switchTab('sem1', this)">1st Semester</button>
                <button class="tab-btn" onclick="switchTab('sem2', this)">2nd Semester</button>
            </div>

            <!-- 1ST SEMESTER TAB -->
            <div class="tab-panel active" id="tab-sem1">
                <table>
                    <thead>
                        <tr>
                            <th>Subject Name</th>
                            <th>Assigned Teacher</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sem1_data['rows'])): ?>
                            <?php foreach ($sem1_data['rows'] as $row): ?>
                                <tr data-subject='<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                                    <td><?= htmlspecialchars($row['subject_name']) ?></td>
                                    <td><?= htmlspecialchars($row['last_name'] . ", " . $row['first_name']) ?></td>
                                    <td><?= htmlspecialchars($row['category']) ?></td>
                                    <td>
                                        <button class="btn-change btn-edit" type="button" title="Edit">
                                            <i class="fa fa-pencil-alt"></i>
                                        </button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                            <input type="hidden" name="subject_id" value="<?= (int)$row['subject_id'] ?>">
                                            <button type="submit" name="delete_subject" class="btn-del" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center;">No subjects.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?= renderPagination($page_sem1, $sem1_data['total_pages'], 'page_sem1') ?>
            </div>

            <!-- 2ND SEMESTER TAB -->
            <div class="tab-panel" id="tab-sem2">
                <table>
                    <thead>
                        <tr>
                            <th>Subject Name</th>
                            <th>Assigned Teacher</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sem2_data['rows'])): ?>
                            <?php foreach ($sem2_data['rows'] as $row): ?>
                                <tr data-subject='<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                                    <td><?= htmlspecialchars($row['subject_name']) ?></td>
                                    <td><?= htmlspecialchars($row['last_name'] . ", " . $row['first_name']) ?></td>
                                    <td><?= htmlspecialchars($row['category']) ?></td>
                                    <td>
                                        <button class="btn-change btn-edit" type="button" title="Edit">
                                            <i class="fa fa-pencil-alt"></i>
                                        </button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                            <input type="hidden" name="subject_id" value="<?= (int)$row['subject_id'] ?>">
                                            <button type="submit" name="delete_subject" class="btn-del" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center;">No subjects.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?= renderPagination($page_sem2, $sem2_data['total_pages'], 'page_sem2') ?>
            </div>

        </main>
    </div>

    <!-- EDIT MODAL -->
    <div id="editModal" class="modal" aria-hidden="true">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3>Edit Subject</h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="subject_id" id="edit_subject_id">
                <label for="edit_subject_name">Subject Name:</label>
                <input type="text" id="edit_subject_name" name="subject_name" required>
                <label for="edit_teacher_id">Assign Teacher:</label>
                <select name="teacher_id" id="edit_teacher_id" required>
                    <option value="">-- Select Teacher --</option>
                    <?php foreach ($teacher_list as $t): ?>
                        <option value="<?= htmlspecialchars($t['teacher_id']) ?>">
                            <?= htmlspecialchars($t['last_name'] . ", " . $t['first_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="edit_category">Category:</label>
                <select name="category" id="edit_category" required>
                    <option value="">-- Select Category --</option>
                    <option value="Core Subjects">Core Subjects</option>
                    <option value="Applied and Specialized Subjects">Applied and Specialized Subjects</option>
                </select>
                <label for="edit_semester">Semester:</label>
                <select name="semester" id="edit_semester" required>
                    <option value="">-- Select Semester --</option>
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                </select>
                <button type="submit" name="edit_subject" class="btn-add" style="margin-top:10px;">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        // Tab switching
        function switchTab(tabId, btn) {
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-' + tabId).classList.add('active');
            btn.classList.add('active');
        }

        // Sidebar toggle
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

        // Dropdown menu
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

        // Edit modal
        const editModal = document.getElementById('editModal');

        function openEditModalWithData(data) {
            document.getElementById('edit_subject_id').value = data.subject_id || '';
            document.getElementById('edit_subject_name').value = data.subject_name || '';
            document.getElementById('edit_teacher_id').value = data.teacher_id || '';
            document.getElementById('edit_category').value = data.category || '';
            document.getElementById('edit_semester').value = data.semester || '';
            editModal.style.display = 'block';
            editModal.setAttribute('aria-hidden', 'false');
        }

        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', () => {
                const tr = button.closest('tr');
                const raw = tr.getAttribute('data-subject');
                try {
                    openEditModalWithData(JSON.parse(raw));
                } catch (err) {
                    alert('Could not parse subject data for editing.');
                    console.error(err);
                }
            });
        });

        editModal.querySelector('.close').addEventListener('click', () => closeEditModal());
        window.addEventListener('click', (e) => {
            if (e.target === editModal) closeEditModal();
        });

        function closeEditModal() {
            editModal.style.display = 'none';
            editModal.setAttribute('aria-hidden', 'true');
        }

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