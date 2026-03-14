<?php
include("connect.php");
session_start();

$selected_role = isset($_GET['role']) ? $_GET['role'] : 'student';

$dropdown_label = $selected_role === 'teacher' ? "TEACHERS ACCOUNT" : "STUDENTS ACCOUNT";

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $records_per_page;

if ($selected_role === 'student') {
    $count_sql = "SELECT COUNT(*) AS total FROM students s JOIN users u ON s.student_id = u.username WHERE u.role = 'student'";
    $sql = "SELECT s.student_id, s.first_name, s.middle_name, s.last_name, s.birthday, s.student_image, 
               u.role
        FROM students s
        JOIN users u ON s.student_id = u.username
        WHERE u.role = 'student'
        LIMIT $records_per_page OFFSET $offset";
} else if ($selected_role === 'teacher') {
    $count_sql = "SELECT COUNT(*) AS total FROM teachers t JOIN users u ON t.teacher_id = u.username WHERE u.role = 'teacher'";
    $sql = "SELECT t.teacher_id AS student_id, t.first_name, t.middle_name, t.last_name, 
               NULL AS birthday, t.teacher_image,
               u.role
        FROM teachers t
        JOIN users u ON t.teacher_id = u.username
        WHERE u.role = 'teacher'
        LIMIT $records_per_page OFFSET $offset";
} else {
    $sql = "";
    $count_sql = "";
}

$total_records = 0;
$total_pages = 1;

if (!empty($count_sql)) {
    $count_result = $conn->query($count_sql);
    if ($count_result) {
        $count_row = $count_result->fetch_assoc();
        $total_records = (int)$count_row['total'];
        $total_pages = ceil($total_records / $records_per_page);
        if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
    }
}

$result = $conn->query($sql);

$subjects_list = [];
if ($selected_role === 'teacher') {
    $result_subjects = $conn->query("SELECT subject_name FROM subjects ORDER BY subject_name ASC");
    if ($result_subjects) {
        while ($row = $result_subjects->fetch_assoc()) {
            $subjects_list[] = $row['subject_name'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Accounts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="admin_account.css" />
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

        .pagination-info {
            font-size: 13px;
            color: #555;
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

        /* Hide pagination when search is active */
        .pagination-wrapper.hidden {
            display: none;
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

                <li class="dropdown <?= ($selected_role == 'student' || $selected_role == 'teacher') ? 'open' : '' ?>">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-users"></i> Accounts
                        <i class="fas fa-caret-down arrow"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="admin_account.php?role=student" <?= $selected_role == 'student' ? 'class="active"' : '' ?>><i class="fas fa-user-graduate"></i> Student</a></li>
                        <li><a href="admin_account.php?role=teacher" <?= $selected_role == 'teacher' ? 'class="active"' : '' ?>><i class="fas fa-chalkboard-teacher"></i> Teacher</a></li>
                    </ul>
                </li>

                <li><a href="admin_subject.php"><i class="fas fa-book-open"></i> Subjects</a></li>
                <li><a href="admin_section.php"><i class="fas fa-layer-group"></i> Section</a></li>
                <li><a href="admin_grade.php"><i class="fas fa-clipboard-list"></i> Grades</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <div id="sidebarOverlay"></div>

        <main class="main-content">
            <h2><?= $dropdown_label ?></h2>

            <div class="actions-row <?= $selected_role === 'student' ? 'student-search' : '' ?>">
                <?php if ($selected_role === 'teacher'): ?>
                    <button id="btnAddTeacher" class="btn-add" onclick="openAddTeacherModal()">Add Teacher</button>
                <?php endif; ?>

                <div class="filter-search">
                    <input type="text" id="search" placeholder="Search accounts..." />
                </div>
            </div>

            <?php if ($selected_role === 'teacher'): ?>
                <div id="addTeacherModal" class="modal">
                    <div class="modal-content">
                        <h3>Add New Teacher</h3>
                        <form id="addTeacherForm" method="POST" action="add_teacher.php" enctype="multipart/form-data">
                            <input type="text" name="first_name" placeholder="First Name" required />
                            <input type="text" name="middle_name" placeholder="Middle Name" />
                            <input type="text" name="last_name" placeholder="Last Name" required />
                            <input type="file" name="teacher_photo" accept="image/*" />
                            <br />
                            <button type="submit" class="btn-change">Add Teacher</button>
                            <button type="button" class="btn-cancel" onclick="closeAddTeacherModal()">Cancel</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>Picture</th>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Password</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="accountTable">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($selected_role === 'student' && !empty($row['student_image'])): ?>
                                        <img src="uploads/<?= htmlspecialchars($row['student_image']); ?>" alt="Photo" />
                                    <?php elseif ($selected_role === 'teacher' && !empty($row['teacher_image'])): ?>
                                        <img src="uploads/<?= htmlspecialchars($row['teacher_image']); ?>" alt="Teacher Photo" />
                                    <?php elseif ($selected_role === 'teacher'): ?>
                                        <img src="uploads/teacher_default.png" alt="Teacher Photo" />
                                    <?php else: ?>
                                        <img src="uploads/default.png" alt="No Photo" />
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['student_id']); ?></td>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></td>
                                <td>
                                    <?php
                                    $user = $conn->query("SELECT password FROM users WHERE username = '" . $row['student_id'] . "' LIMIT 1");
                                    if ($user && $u = $user->fetch_assoc()) {
                                        echo htmlspecialchars($u['password']);
                                    } else {
                                        echo "-";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button class="btn-view"
                                        onclick="openViewModal('<?= htmlspecialchars($row['student_id']); ?>', '<?= $selected_role ?>')">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button class="btn-change"
                                        onclick="openModal('<?= htmlspecialchars($row['student_id']); ?>')">
                                        <i class="fa fa-pencil-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">No accounts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-wrapper" id="paginationWrapper">
                <ul class="pagination">
                    <!-- Previous -->
                    <li class="<?= $page <= 1 ? 'disabled' : '' ?>">
                        <?php if ($page <= 1): ?>
                            <span><i class="fas fa-chevron-left"></i></span>
                        <?php else: ?>
                            <a href="admin_account.php?role=<?= $selected_role ?>&page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
                        <?php endif; ?>
                    </li>

                    <?php
                    // Show a window of pages around the current page
                    $window = 2;
                    $start_page = max(1, $page - $window);
                    $end_page   = min($total_pages, $page + $window);

                    if ($start_page > 1): ?>
                        <li><a href="admin_account.php?role=<?= $selected_role ?>&page=1">1</a></li>
                        <?php if ($start_page > 2): ?>
                            <li class="disabled"><span>&hellip;</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="<?= $i === $page ? 'active' : '' ?>">
                            <?php if ($i === $page): ?>
                                <span><?= $i ?></span>
                            <?php else: ?>
                                <a href="admin_account.php?role=<?= $selected_role ?>&page=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>

                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <li class="disabled"><span>&hellip;</span></li>
                        <?php endif; ?>
                        <li><a href="admin_account.php?role=<?= $selected_role ?>&page=<?= $total_pages ?>"><?= $total_pages ?></a></li>
                    <?php endif; ?>

                    <!-- Next -->
                    <li class="<?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <?php if ($page >= $total_pages): ?>
                            <span><i class="fas fa-chevron-right"></i></span>
                        <?php else: ?>
                            <a href="admin_account.php?role=<?= $selected_role ?>&page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
            <!-- End Pagination -->

        </main>
    </div>

    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Change Password</h3>
            <form id="passwordForm" method="POST" action="update_password.php">
                <input type="hidden" name="student_id" id="modal_student_id" />
                <input type="password" name="new_password" placeholder="Enter new password" required />
                <br />
                <button type="submit" class="btn-change">Update</button>
            </form>
        </div>
    </div>

    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeViewModal()">&times;</span>
            <h3>Account Details</h3>
            <div id="viewDetails">
                <p>Loading...</p>
            </div>
        </div>
    </div>

    <script>
        function openModal(studentId) {
            document.getElementById("modal_student_id").value = studentId;
            document.getElementById("passwordModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("passwordModal").style.display = "none";
        }

        function openAddTeacherModal() {
            document.getElementById("addTeacherModal").style.display = "block";
        }

        function closeAddTeacherModal() {
            document.getElementById("addTeacherModal").style.display = "none";
        }

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

        // Search — hides pagination while active, shows it when cleared
        const searchInput = document.getElementById("search");
        const paginationWrapper = document.getElementById("paginationWrapper");

        searchInput.addEventListener("keyup", function() {
            let query = this.value.trim();
            let role = <?= json_encode($selected_role) ?>;

            if (query.length > 0) {
                // Hide pagination during search
                paginationWrapper.classList.add("hidden");
            } else {
                // Restore pagination when search is cleared
                paginationWrapper.classList.remove("hidden");
            }

            let xhr = new XMLHttpRequest();
            xhr.open("GET", "search_accounts.php?q=" + encodeURIComponent(query) + "&role=" + encodeURIComponent(role), true);
            xhr.onload = function() {
                if (this.status === 200) {
                    document.getElementById("accountTable").innerHTML = this.responseText;
                }
            };
            xhr.send();
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

        function openViewModal(accountId, role) {
            document.getElementById("viewModal").style.display = "block";
            document.getElementById("viewDetails").innerHTML = "<p>Loading...</p>";
            let xhr = new XMLHttpRequest();
            xhr.open("GET", "view_account.php?id=" + encodeURIComponent(accountId) + "&role=" + encodeURIComponent(role), true);
            xhr.onload = function() {
                if (this.status === 200) {
                    document.getElementById("viewDetails").innerHTML = this.responseText;
                } else {
                    document.getElementById("viewDetails").innerHTML = "<p>Error loading details.</p>";
                }
            };
            xhr.send();
        }

        function closeViewModal() {
            document.getElementById("viewModal").style.display = "none";
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