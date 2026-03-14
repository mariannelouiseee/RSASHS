<?php
include("connect.php");
session_start();

$selected_role = isset($_GET['role']) ? $_GET['role'] : 'student';

$dropdown_label = $selected_role === 'teacher' ? "TEACHERS ACCOUNT" : "STUDENTS ACCOUNT";

$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $records_per_page;

if ($selected_role === 'student') {
    $count_sql = "SELECT COUNT(*) AS total FROM students s JOIN users u ON s.student_id = u.username WHERE u.role = 'student'";
    $sql = "SELECT s.student_id, s.first_name, s.middle_name, s.last_name, s.birthday, s.student_image, u.role
        FROM students s JOIN users u ON s.student_id = u.username
        WHERE u.role = 'student' LIMIT $records_per_page OFFSET $offset";
} else if ($selected_role === 'teacher') {
    $count_sql = "SELECT COUNT(*) AS total FROM teachers t JOIN users u ON t.teacher_id = u.username WHERE u.role = 'teacher'";
    $sql = "SELECT t.teacher_id AS student_id, t.first_name, t.middle_name, t.last_name,
               NULL AS birthday, t.teacher_image, u.role
        FROM teachers t JOIN users u ON t.teacher_id = u.username
        WHERE u.role = 'teacher' LIMIT $records_per_page OFFSET $offset";
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

        .pagination-wrapper.hidden {
            display: none;
        }

        /* ===== VIEW MODAL ===== */
        #viewModal .modal-content {
            padding: 0;
            width: 460px;
            border-radius: 16px;
            overflow: hidden;
        }

        .vm-banner {
            height: 82px;
            background: linear-gradient(135deg, #2e7d32, #43a047);
            position: relative;
            flex-shrink: 0;
        }

        .vm-close-btn {
            position: absolute;
            top: 12px;
            right: 14px;
            background: rgba(255, 255, 255, 0.18);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            cursor: pointer;
            color: #fff;
            font-size: 20px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .vm-close-btn:hover {
            background: rgba(255, 255, 255, 0.32);
        }

        .vm-avatar {
            position: absolute;
            bottom: -36px;
            left: 50%;
            transform: translateX(-50%);
            width: 74px;
            height: 74px;
            border-radius: 50%;
            border: 4px solid #fff;
            background: #c8e6c9;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            font-weight: 700;
            color: #2e7d32;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
        }

        .vm-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .vm-identity {
            padding: 50px 24px 16px;
            text-align: center;
            border-bottom: 1px solid #f0f4f0;
        }

        .vm-identity .vm-name {
            font-size: 17px;
            font-weight: 700;
            color: #1b3a1f;
            margin-bottom: 4px;
        }

        .vm-identity .vm-id {
            font-size: 12px;
            color: #78909c;
            margin-bottom: 8px;
        }

        .vm-badge {
            display: inline-block;
            padding: 3px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
            text-transform: capitalize;
        }

        .vm-section-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #81a881;
            padding: 16px 22px 6px;
        }

        .vm-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 0 22px 16px;
        }

        .vm-item {
            background: #f6faf6;
            border: 1px solid #e0ede0;
            border-radius: 10px;
            padding: 10px 13px;
        }

        .vm-item.full {
            grid-column: span 2;
        }

        .vm-item .vi-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.55px;
            color: #81a881;
            margin-bottom: 4px;
        }

        .vm-item .vi-val {
            font-size: 13px;
            font-weight: 600;
            color: #1b3a1f;
        }

        .vm-footer {
            padding: 12px 22px 18px;
            display: flex;
            justify-content: flex-end;
            border-top: 1px solid #f0f4f0;
        }

        .btn-vm-close {
            background: #f5f5f5;
            color: #555;
            padding: 9px 22px;
            border: 1.5px solid #ddd;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-vm-close:hover {
            background: #e8e8e8;
        }

        .vm-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px 0 60px;
            gap: 12px;
            font-size: 14px;
            color: #a5c9a5;
        }

        .vm-loading i {
            font-size: 28px;
            color: #a5d6a7;
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
                    <button class="btn-add" onclick="openAddTeacherModal()">
                        <i class="fas fa-plus" style="margin-right:6px;"></i>Add Teacher
                    </button>
                <?php endif; ?>
                <div class="filter-search">
                    <input type="text" id="search" placeholder="Search accounts..." />
                </div>
            </div>

            <?php if ($selected_role === 'teacher'): ?>
                <div id="addTeacherModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3><span class="modal-icon"><i class="fas fa-chalkboard-teacher"></i></span> Add New Teacher</h3>
                            <span class="close" onclick="closeAddTeacherModal()">&times;</span>
                        </div>
                        <form method="POST" action="add_teacher.php" enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="form-group"><label>First Name</label><input type="text" name="first_name" placeholder="Enter first name" required /></div>
                                <div class="form-group"><label>Middle Name</label><input type="text" name="middle_name" placeholder="Enter middle name (optional)" /></div>
                                <div class="form-group"><label>Last Name</label><input type="text" name="last_name" placeholder="Enter last name" required /></div>
                                <div class="form-group"><label>Profile Photo</label><input type="file" name="teacher_photo" accept="image/*" /></div>
                            </div>
                            <div class="modal-divider"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn-modal-cancel" onclick="closeAddTeacherModal()">Cancel</button>
                                <button type="submit" class="btn-modal-primary"><i class="fas fa-plus" style="margin-right:5px;"></i>Add Teacher</button>
                            </div>
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
                                    echo ($user && $u = $user->fetch_assoc()) ? htmlspecialchars($u['password']) : '-';
                                    ?>
                                </td>
                                <td>
                                    <button class="btn-view" onclick="openViewModal('<?= htmlspecialchars($row['student_id']); ?>', '<?= $selected_role ?>')">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button class="btn-change" onclick="openModal('<?= htmlspecialchars($row['student_id']); ?>')">
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

            <div class="pagination-wrapper" id="paginationWrapper">
                <ul class="pagination">
                    <li class="<?= $page <= 1 ? 'disabled' : '' ?>">
                        <?php if ($page <= 1): ?><span><i class="fas fa-chevron-left"></i></span>
                        <?php else: ?><a href="admin_account.php?role=<?= $selected_role ?>&page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
                    </li>
                    <?php
                    $window = 2;
                    $start_page = max(1, $page - $window);
                    $end_page = min($total_pages, $page + $window);
                    if ($start_page > 1): ?>
                        <li><a href="admin_account.php?role=<?= $selected_role ?>&page=1">1</a></li>
                        <?php if ($start_page > 2): ?><li class="disabled"><span>&hellip;</span></li><?php endif; ?>
                    <?php endif; ?>
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="<?= $i === $page ? 'active' : '' ?>">
                            <?php if ($i === $page): ?><span><?= $i ?></span>
                            <?php else: ?><a href="admin_account.php?role=<?= $selected_role ?>&page=<?= $i ?>"><?= $i ?></a><?php endif; ?>
                        </li>
                    <?php endfor; ?>
                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?><li class="disabled"><span>&hellip;</span></li><?php endif; ?>
                        <li><a href="admin_account.php?role=<?= $selected_role ?>&page=<?= $total_pages ?>"><?= $total_pages ?></a></li>
                    <?php endif; ?>
                    <li class="<?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <?php if ($page >= $total_pages): ?><span><i class="fas fa-chevron-right"></i></span>
                        <?php else: ?><a href="admin_account.php?role=<?= $selected_role ?>&page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
                    </li>
                </ul>
            </div>
        </main>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><span class="modal-icon"><i class="fas fa-lock"></i></span> Change Password</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="update_password.php">
                <input type="hidden" name="student_id" id="modal_student_id" />
                <div class="modal-body">
                    <div class="form-group">
                        <label>Account ID</label>
                        <input type="text" id="modal_student_id_display" readonly style="background:#f0f0f0;color:#888;cursor:not-allowed;" />
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="Enter new password" required />
                    </div>
                </div>
                <div class="modal-divider"></div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-modal-primary"><i class="fas fa-save" style="margin-right:5px;"></i>Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Account Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <!-- Green banner + avatar -->
            <div class="vm-banner">
                <button class="vm-close-btn" onclick="closeViewModal()">&times;</button>
                <div class="vm-avatar" id="vmAvatar">
                    <i class="fas fa-user" style="font-size:24px; color:#a5d6a7;"></i>
                </div>
            </div>
            <!-- Dynamic content injected here -->
            <div id="viewDetails">
                <div class="vm-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(studentId) {
            document.getElementById("modal_student_id").value = studentId;
            document.getElementById("modal_student_id_display").value = studentId;
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

        document.querySelectorAll(".modal").forEach(m => {
            m.addEventListener("click", function(e) {
                if (e.target === this) this.style.display = "none";
            });
        });

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

        const searchInput = document.getElementById("search");
        const paginationWrapper = document.getElementById("paginationWrapper");
        searchInput.addEventListener("keyup", function() {
            const query = this.value.trim();
            const role = <?= json_encode($selected_role) ?>;
            paginationWrapper.classList.toggle("hidden", query.length > 0);
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "search_accounts.php?q=" + encodeURIComponent(query) + "&role=" + encodeURIComponent(role), true);
            xhr.onload = function() {
                if (this.status === 200) document.getElementById("accountTable").innerHTML = this.responseText;
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

        // ===== VIEW MODAL =====
        function openViewModal(accountId, role) {
            const modal = document.getElementById("viewModal");
            const details = document.getElementById("viewDetails");
            const avatar = document.getElementById("vmAvatar");

            // Reset
            details.innerHTML = `<div class="vm-loading"><i class="fas fa-spinner fa-spin"></i><span>Loading...</span></div>`;
            avatar.innerHTML = `<i class="fas fa-user" style="font-size:24px;color:#a5d6a7;"></i>`;
            avatar.style.cssText = '';
            modal.style.display = "block";

            const xhr = new XMLHttpRequest();
            xhr.open("GET", "view_account.php?id=" + encodeURIComponent(accountId) + "&role=" + encodeURIComponent(role), true);
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        renderViewModal(JSON.parse(this.responseText));
                    } catch (e) {
                        details.innerHTML = this.responseText; // fallback if still HTML
                    }
                } else {
                    details.innerHTML = `<div class="vm-loading" style="color:#e53935;"><i class="fas fa-exclamation-circle"></i><span>Error loading details.</span></div>`;
                }
            };
            xhr.send();
        }

        function renderViewModal(d) {
            const avatar = document.getElementById("vmAvatar");
            const details = document.getElementById("viewDetails");

            // Avatar
            if (d.image) {
                avatar.innerHTML = `<img src="uploads/${esc(d.image)}" alt="Photo" />`;
            } else {
                const initials = ((d.first_name || '').charAt(0) + (d.last_name || '').charAt(0)).toUpperCase() || '?';
                avatar.textContent = initials;
                avatar.style.fontSize = '24px';
                avatar.style.fontWeight = '700';
                avatar.style.color = '#2e7d32';
                avatar.style.background = '#c8e6c9';
            }

            const fullName = [d.first_name, d.middle_name, d.last_name].filter(Boolean).join(' ');
            const roleLabel = (d.role || '').charAt(0).toUpperCase() + (d.role || '').slice(1);

            // Info tiles
            let tiles = `
                <div class="vm-item full"><div class="vi-label">Account ID</div><div class="vi-val">${esc(d.id||'-')}</div></div>
                <div class="vm-item"><div class="vi-label">First Name</div><div class="vi-val">${esc(d.first_name||'-')}</div></div>
                <div class="vm-item"><div class="vi-label">Last Name</div><div class="vi-val">${esc(d.last_name||'-')}</div></div>`;

            if (d.middle_name)
                tiles += `<div class="vm-item full"><div class="vi-label">Middle Name</div><div class="vi-val">${esc(d.middle_name)}</div></div>`;
            if (d.birthday)
                tiles += `<div class="vm-item full"><div class="vi-label">Birthday</div><div class="vi-val">${esc(d.birthday)}</div></div>`;
            if (d.section)
                tiles += `<div class="vm-item full"><div class="vi-label">Section</div><div class="vi-val">${esc(d.section)}</div></div>`;
            if (d.subjects && d.subjects.length)
                tiles += `<div class="vm-item full"><div class="vi-label">Subjects Handled</div><div class="vi-val">${d.subjects.map(s=>esc(s)).join(', ')}</div></div>`;

            details.innerHTML = `
                <div class="vm-identity">
                    <div class="vm-name">${esc(fullName)}</div>
                    <div class="vm-id">${esc(d.id||'')}</div>
                    <span class="vm-badge">${esc(roleLabel)}</span>
                </div>
                <div class="vm-section-label">Account Information</div>
                <div class="vm-grid">${tiles}</div>
                <div class="vm-footer">
                    <button class="btn-vm-close" onclick="closeViewModal()">Close</button>
                </div>`;
        }

        function esc(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function closeViewModal() {
            document.getElementById("viewModal").style.display = "none";
        }

        // Inactivity logout
        (function() {
            const INACTIVITY_LIMIT = 5 * 60 * 1000,
                WARNING_TIME = 10 * 1000;
            let inactivityTimer, warningTimer;

            function resetTimer() {
                clearTimeout(inactivityTimer);
                clearTimeout(warningTimer);
                warningTimer = setTimeout(showWarning, INACTIVITY_LIMIT - WARNING_TIME);
                inactivityTimer = setTimeout(logoutUser, INACTIVITY_LIMIT);
            }

            function showWarning() {
                if (document.getElementById('inactivityWarning')) return;
                const d = document.createElement('div');
                d.id = 'inactivityWarning';
                Object.assign(d.style, {
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
                    fontFamily: '"Segoe UI",Tahoma,Geneva,Verdana,sans-serif',
                    fontSize: '14px',
                    lineHeight: '1.4',
                    display: 'flex',
                    flexDirection: 'column',
                    gap: '10px',
                    opacity: 0,
                    transition: 'opacity 0.5s ease',
                    zIndex: 10000
                });
                d.innerHTML = `<strong style="font-size:16px;color:#1b5e20;">Inactivity Warning</strong><span>You will be logged out in <span id="countdown">10</span> seconds.</span><button id="stayLoggedIn" style="padding:8px 12px;background:#2e7d32;color:white;border:none;border-radius:6px;font-weight:bold;cursor:pointer;align-self:flex-end;">Stay Logged In</button>`;
                document.body.appendChild(d);
                setTimeout(() => d.style.opacity = 1, 10);
                let c = 10;
                const span = document.getElementById('countdown');
                const iv = setInterval(() => {
                    if (--c <= 0) clearInterval(iv);
                    span.textContent = c;
                }, 1000);
                document.getElementById('stayLoggedIn').addEventListener('click', () => {
                    clearInterval(iv);
                    d.style.opacity = 0;
                    setTimeout(() => d.remove(), 300);
                    resetTimer();
                });
            }

            function logoutUser() {
                fetch('auto_logout.php', {
                    method: 'POST',
                    credentials: 'same-origin'
                }).then(r => r.json()).then(d => {
                    alert(d.message || 'Logged out.');
                    window.location.href = 'login.php';
                }).catch(() => {
                    window.location.href = 'login.php';
                });
            }
            ['mousemove', 'keydown', 'mousedown', 'touchstart'].forEach(e => document.addEventListener(e, resetTimer));
            resetTimer();
        })();
    </script>
</body>

</html>