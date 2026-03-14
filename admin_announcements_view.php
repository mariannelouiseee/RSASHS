<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Announcements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="announcement.css" />
    <style>
        /* Pagination Styles */
        .pagination-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            margin-bottom: 8px;
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

    <!-- ═══════ HEADER ═══════ -->
    <header>
        <div class="header-left">
            <img src="./img/logo.jpg" alt="RSASHS Logo" />
            <h2>RSASHS E-PORTAL</h2>
        </div>
        <button id="sidebarToggle" class="sidebar-toggle"><i class="fas fa-bars"></i></button>
    </header>

    <div class="container">

        <!-- ═══════ SIDEBAR ═══════ -->
        <aside class="sidebar">
            <ul>
                <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_announcements.php" class="active"><i class="fas fa-bullhorn"></i> Announcements</a></li>
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
                <li><a href="admin_grade.php"><i class="fas fa-clipboard-list"></i> Grades</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- ═══════ MAIN ═══════ -->
        <main class="main-content">

            <!-- Page Header -->
            <div class="page-header">
                <div class="icon-wrap"><i class="fas fa-bullhorn"></i></div>
                <div>
                    <h1>ANNOUNCEMENTS</h1>
                    <p>Create, schedule, and manage school announcements</p>
                </div>
            </div>

            <!-- ── FORM CARD ── -->
            <div class="box">
                <h2>
                    <i class="fas <?= $editData ? 'fa-pen' : 'fa-plus-circle' ?>"></i>
                    <?= $editData ? 'Edit Announcement' : 'New Announcement'; ?>
                </h2>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $editData['id'] ?? ''; ?>">

                    <div class="form-grid">

                        <div class="full">
                            <label>Title</label>
                            <input type="text" name="title" required
                                placeholder="Enter announcement title…"
                                value="<?= htmlspecialchars($editData['title'] ?? ''); ?>">
                        </div>

                        <div class="full">
                            <label>Message</label>
                            <textarea name="message" rows="5" required
                                placeholder="Write the announcement message here…"><?= htmlspecialchars($editData['message'] ?? ''); ?></textarea>
                        </div>

                        <div class="full">
                            <label>Publish Options</label>
                            <div class="schedule-toggle-group">
                                <label>
                                    <input type="radio" name="schedule_type" value="now" id="opt_now"
                                        <?= (!$editData || ($editData['status'] ?? '') !== 'scheduled') ? 'checked' : ''; ?>>
                                    <span class="toggle-btn">
                                        <i class="fas fa-paper-plane"></i> Publish Now
                                    </span>
                                </label>
                                <label>
                                    <input type="radio" name="schedule_type" value="schedule" id="opt_schedule"
                                        <?= (($editData['status'] ?? '') === 'scheduled') ? 'checked' : ''; ?>>
                                    <span class="toggle-btn">
                                        <i class="fas fa-clock"></i> Schedule
                                    </span>
                                </label>
                            </div>

                            <div class="schedule-picker <?= (($editData['status'] ?? '') === 'scheduled') ? 'visible' : ''; ?>" id="schedulePicker">
                                <label style="margin-top:12px;">Publish Date &amp; Time</label>
                                <input type="datetime-local" name="scheduled_at" id="scheduledAt"
                                    value="<?= !empty($editData['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($editData['scheduled_at'])) : ''; ?>">
                            </div>
                        </div>

                        <div class="full">
                            <label>
                                Image
                                <span style="font-weight:400;text-transform:none;font-size:0.75rem;">(optional)</span>
                            </label>
                            <div class="file-upload-area">
                                <input type="file" name="image" accept="image/*" id="imageInput">
                                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <p id="uploadLabel">Drag &amp; drop or <span>browse file</span></p>
                                <p style="margin-top:4px;font-size:0.72rem;">PNG, JPG, GIF up to 10MB</p>
                            </div>
                            <?php if (!empty($editData['image'])): ?>
                                <img class="announcement-image-preview"
                                    src="uploads/<?= htmlspecialchars($editData['image']); ?>"
                                    alt="Current Image">
                            <?php endif; ?>
                        </div>

                    </div>

                    <div class="form-actions">
                        <button type="submit">
                            <i class="fas <?= $editData ? 'fa-save' : 'fa-paper-plane' ?>"></i>
                            <?= $editData ? 'Update Announcement' : 'Post Announcement'; ?>
                        </button>
                        <?php if ($editData): ?>
                            <a href="admin_announcements.php" class="btn-cancel">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- ── TABS ── -->
            <div class="tab-nav">
                <button class="tab-btn active" data-tab="published">
                    <i class="fas fa-check-circle"></i> Published
                    <span class="tab-badge"><?= $pub_count ?></span>
                </button>
                <button class="tab-btn warn-tab" data-tab="scheduled">
                    <i class="fas fa-clock"></i> Scheduled
                    <span class="tab-badge"><?= $sch_count ?></span>
                </button>
            </div>

            <!-- ── PUBLISHED TAB ── -->
            <div class="tab-panel active" id="tab-published">
                <div class="cards-list">
                    <?php if ($published && mysqli_num_rows($published) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($published)): ?>
                            <div class="ann-card">
                                <div class="ann-card-accent"></div>
                                <div class="ann-card-body">
                                    <div class="ann-card-main">
                                        <div class="ann-card-meta">
                                            <span class="chip chip-pub">
                                                <i class="fas fa-check-circle"></i> Published
                                            </span>
                                            <span class="chip chip-date">
                                                <i class="fas fa-calendar-alt"></i>
                                                <?= date('M d, Y', strtotime($row['created_at'])); ?>
                                            </span>
                                        </div>
                                        <div class="ann-card-title"><?= htmlspecialchars($row['title']); ?></div>
                                        <div class="ann-card-msg"><?= htmlspecialchars($row['message']); ?></div>
                                        <div class="ann-card-footer">
                                            <div class="ann-card-actions">
                                                <a class="btn-edit" href="?edit=<?= $row['id']; ?>">
                                                    <i class="fas fa-pen"></i> Edit
                                                </a>
                                                <a class="btn-delete"
                                                    href="?delete=<?= $row['id']; ?>"
                                                    onclick="return confirm('Delete this announcement?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (!empty($row['image'])): ?>
                                        <img class="ann-thumb"
                                            src="uploads/<?= htmlspecialchars($row['image']); ?>"
                                            alt="Announcement Image">
                                    <?php else: ?>
                                        <div class="ann-thumb-ph"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            No published announcements yet.
                        </div>
                    <?php endif; ?>
                </div>

                <?= renderAnnPagination($page_pub, $pub_pages, 'page_pub', 'published') ?>
            </div>

            <!-- ── SCHEDULED TAB ── -->
            <div class="tab-panel" id="tab-scheduled">
                <div class="cards-list">
                    <?php if ($scheduled && mysqli_num_rows($scheduled) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($scheduled)): ?>
                            <?php
                            $sched_ts  = !empty($row['scheduled_at']) ? strtotime($row['scheduled_at']) : false;
                            $tomorrow  = strtotime('tomorrow midnight');
                            $day_after = strtotime('+2 days midnight');
                            $is_tomorrow = $sched_ts && ($sched_ts >= $tomorrow && $sched_ts < $day_after);
                            $sched_label = $sched_ts ? date('M d, Y · g:i A', $sched_ts) : '—';
                            $pub_label   = $sched_ts
                                ? ($is_tomorrow ? 'Publishes tomorrow at ' . date('g:i A', $sched_ts)
                                    : 'Publishes ' . date('M d', $sched_ts) . ' at ' . date('g:i A', $sched_ts))
                                : 'Publish time not set';
                            ?>
                            <div class="ann-card warn-card">
                                <div class="ann-card-accent warn"></div>
                                <div class="ann-card-body">
                                    <div class="ann-card-main">
                                        <div class="ann-card-meta">
                                            <?php if ($is_tomorrow): ?>
                                                <span class="chip chip-tomorrow">
                                                    <i class="fas fa-sun"></i> Tomorrow
                                                </span>
                                            <?php endif; ?>
                                            <span class="chip chip-sched">
                                                <i class="fas fa-clock"></i>
                                                <?= $sched_label; ?>
                                            </span>
                                        </div>
                                        <div class="ann-card-title"><?= htmlspecialchars($row['title']); ?></div>
                                        <div class="ann-card-msg"><?= htmlspecialchars($row['message']); ?></div>
                                        <div class="ann-card-footer">
                                            <span class="pub-status-chip">
                                                <i class="fas fa-lock"></i>
                                                <?= $pub_label; ?>
                                            </span>
                                            <div class="ann-card-actions">
                                                <a class="btn-publish"
                                                    href="?publish_now=<?= $row['id']; ?>"
                                                    onclick="return confirm('Publish this announcement now?')">
                                                    <i class="fas fa-paper-plane"></i> Publish Now
                                                </a>
                                                <a class="btn-edit" href="?edit=<?= $row['id']; ?>">
                                                    <i class="fas fa-pen"></i> Edit
                                                </a>
                                                <a class="btn-delete"
                                                    href="?delete=<?= $row['id']; ?>"
                                                    onclick="return confirm('Delete this announcement?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (!empty($row['image'])): ?>
                                        <img class="ann-thumb"
                                            src="uploads/<?= htmlspecialchars($row['image']); ?>"
                                            alt="Announcement Image">
                                    <?php else: ?>
                                        <div class="ann-thumb-ph"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-clock"></i>
                            No scheduled announcements.
                        </div>
                    <?php endif; ?>
                </div>

                <?= renderAnnPagination($page_sch, $sch_pages, 'page_sch', 'scheduled') ?>
            </div>

        </main>
    </div>

    <!-- ═══════ SCRIPTS ═══════ -->
    <script>
        /* ── Sidebar toggle ── */
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('active'));

        /* ── Sidebar dropdowns ── */
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const parent = this.parentElement;
                parent.classList.toggle('open');
                document.querySelectorAll('.dropdown').forEach(item => {
                    if (item !== parent) item.classList.remove('open');
                });
            });
        });

        /* ── Schedule toggle ── */
        const optNow = document.getElementById('opt_now');
        const optSchedule = document.getElementById('opt_schedule');
        const picker = document.getElementById('schedulePicker');
        const dtInput = document.getElementById('scheduledAt');

        function setMinDateTime() {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            dtInput.min = now.toISOString().slice(0, 16);
        }

        function updatePicker() {
            if (optSchedule.checked) {
                picker.classList.add('visible');
                dtInput.required = true;
                setMinDateTime();
            } else {
                picker.classList.remove('visible');
                dtInput.required = false;
                dtInput.value = '';
            }
        }

        optNow.addEventListener('change', updatePicker);
        optSchedule.addEventListener('change', updatePicker);
        updatePicker();

        /* ── File upload label ── */
        const imageInput = document.getElementById('imageInput');
        const uploadLabel = document.getElementById('uploadLabel');
        if (imageInput) {
            imageInput.addEventListener('change', () => {
                const name = imageInput.files[0]?.name;
                if (name) {
                    uploadLabel.textContent = name;
                    imageInput.closest('.file-upload-area').style.borderColor = 'var(--accent)';
                    imageInput.closest('.file-upload-area').style.background = 'var(--accent-bg)';
                }
            });
        }

        /* ── Tabs ── */
        function activateTab(tabName) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            const btn = document.querySelector('[data-tab="' + tabName + '"]');
            const panel = document.getElementById('tab-' + tabName);
            if (btn) btn.classList.add('active');
            if (panel) panel.classList.add('active');
        }

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => activateTab(btn.dataset.tab));
        });

        /* ── Auto-open correct tab based on URL hash or active pagination ── */
        (function() {
            const hash = window.location.hash.replace('#', '');
            const params = new URLSearchParams(window.location.search);
            if (hash === 'scheduled' || params.has('page_sch')) {
                activateTab('scheduled');
            } else if (hash === 'published' || params.has('page_pub')) {
                activateTab('published');
            }
        })();

        /* ════ INACTIVITY LOGOUT ════ */
        (function() {
            const LIMIT = 5 * 60 * 1000;
            const WARN_MS = 10 * 1000;
            let inactTimer, warnTimer;

            function reset() {
                clearTimeout(inactTimer);
                clearTimeout(warnTimer);
                warnTimer = setTimeout(showWarn, LIMIT - WARN_MS);
                inactTimer = setTimeout(logout, LIMIT);
            }

            function showWarn() {
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
                    boxShadow: '0 4px 12px rgba(0,0,0,.25)',
                    color: '#2e3a2f',
                    fontFamily: '"Plus Jakarta Sans",sans-serif',
                    fontSize: '14px',
                    lineHeight: '1.4',
                    display: 'flex',
                    flexDirection: 'column',
                    gap: '10px',
                    opacity: 0,
                    transition: 'opacity .5s ease',
                    zIndex: 10000
                });
                d.innerHTML = `
                    <strong style="font-size:16px;color:#1b5e20;">Inactivity Warning</strong>
                    <span>You will be logged out in <span id="countdown">10</span> seconds.</span>
                    <button id="stayLoggedIn" style="padding:8px 12px;background:#2e7d32;color:#fff;border:none;border-radius:6px;font-weight:700;cursor:pointer;align-self:flex-end;">
                        Stay Logged In
                    </button>`;
                document.body.appendChild(d);
                setTimeout(() => d.style.opacity = 1, 10);

                let n = 10;
                const span = document.getElementById('countdown');
                const interval = setInterval(() => {
                    if (--n <= 0) clearInterval(interval);
                    span.textContent = n;
                }, 1000);

                document.getElementById('stayLoggedIn').addEventListener('click', () => {
                    clearInterval(interval);
                    d.style.opacity = 0;
                    setTimeout(() => d.remove(), 300);
                    reset();
                });
            }

            function logout() {
                fetch('auto_logout.php', {
                        method: 'POST',
                        credentials: 'same-origin'
                    })
                    .then(r => r.json())
                    .then(d => {
                        alert(d.message || 'Logged out due to inactivity.');
                        location.href = 'login.php';
                    })
                    .catch(() => {
                        location.href = 'login.php';
                    });
            }

            ['mousemove', 'keydown', 'mousedown', 'touchstart'].forEach(e => document.addEventListener(e, reset));
            reset();
        })();
    </script>

</body>

</html>