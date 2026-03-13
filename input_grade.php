<?php
// input_grade.php
include("connect.php");
session_start();

// ===== CHECK IF TEACHER IS LOGGED IN =====
if (!isset($_SESSION['teacher_id'])) {
    echo "<script>alert('You must be logged in as teacher.'); window.location='login.php';</script>";
    exit();
}

$teacher_id = trim($_SESSION['teacher_id']);

// ===== FETCH TEACHER NAME =====
$teach_stmt = $conn->prepare("SELECT first_name, middle_name, last_name FROM teachers WHERE teacher_id = ?");
$teach_stmt->bind_param("s", $teacher_id);
$teach_stmt->execute();
$teach_result = $teach_stmt->get_result();
$teach_row    = $teach_result->fetch_assoc();

if (!$teach_row) {
    $full_name = "Unknown Teacher";
} else {
    $first          = $teach_row['first_name'];
    $middle         = $teach_row['middle_name'];
    $last           = $teach_row['last_name'];
    $middle_initial = $middle ? strtoupper($middle[0]) . '.' : '';
    $full_name      = trim("$first $middle_initial $last");
}

// ===== REQUIRED GET PARAMS =====
$year_level   = $_GET['year_level']   ?? '';
$section_name = $_GET['section_name'] ?? '';
$subject_id   = $_GET['subject_id']   ?? '';

if (empty($year_level) || empty($section_name) || empty($subject_id)) {
    echo "Missing required parameters.";
    exit();
}

$subject_id   = (int)$subject_id;
$year_level   = trim($year_level);
$section_name = trim($section_name);

// ===== FETCH SCHOOL YEAR =====
$sec_stmt = $conn->prepare("SELECT school_year FROM sections WHERE year_level=? AND section_name=? LIMIT 1");
$sec_stmt->bind_param("ss", $year_level, $section_name);
$sec_stmt->execute();
$sec_result = $sec_stmt->get_result();
$sec_row    = $sec_result->fetch_assoc();

if (!$sec_row) {
    echo "Section not found.";
    exit();
}
$school_year = trim($sec_row['school_year']);

// ===== FETCH SUBJECT NAME =====
$sub_stmt = $conn->prepare("SELECT subject_name FROM subjects WHERE subject_id = ?");
$sub_stmt->bind_param("i", $subject_id);
$sub_stmt->execute();
$sub_result   = $sub_stmt->get_result();
$sub_row      = $sub_result->fetch_assoc();
$subject_name = $sub_row ? $sub_row['subject_name'] : "Unknown Subject";

// ===== FETCH STUDENTS AND EXISTING GRADES =====
$sql = "
    SELECT s.student_id,
           CONCAT(s.last_name, ', ', s.first_name) AS full_name,
           g.q1, g.q2, g.first_sem_final, g.gwa_first_sem,
           g.q3, g.q4, g.second_sem_final, g.gwa_second_sem,
           g.final
    FROM students s
    LEFT JOIN grades g
      ON s.student_id = g.student_id
      AND g.subject_id = ?
      AND TRIM(g.school_year) = ?
    WHERE TRIM(s.year_level) = ?
      AND TRIM(s.section_name) = ?
      AND TRIM(s.school_year) = ?
    ORDER BY s.last_name, s.first_name
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $subject_id, $school_year, $year_level, $section_name, $school_year);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($r = $result->fetch_assoc()) {
    $students[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Grades — <?= htmlspecialchars($year_level . " - " . $section_name) ?></title>
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="input_grade.css">
</head>

<body>

    <!-- ===== HEADER ===== -->
    <header>
        <div class="header-left">
            <img src="./img/logo.jpg" alt="RSASHS Logo">
            <h2>RSASHS E-Portal</h2>
        </div>

        <button id="menuToggle" class="menu-toggle">&#9776;</button>

        <nav class="navbar" id="navbarLinks">
            <ul>
                <li><a href="teacher_dashboard.php">Dashboard</a></li>
                <li><a href="teacher_advisory.php">Advisory Class</a></li>
                <li><a href="logout.php" class="logout">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- ===== MAIN ===== -->
    <main>

        <!-- Info Card -->
        <div class="info-card">
            <div class="info-card-left">
                <h1>Grade Input — <?= htmlspecialchars($year_level . " · " . $section_name) ?></h1>
                <p>Manage quarterly grades for all enrolled students in this class.</p>
            </div>
            <div class="info-badges">
                <span class="badge badge-green">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                    </svg>
                    <?= htmlspecialchars($subject_name) ?>
                </span>
                <span class="badge badge-blue">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    SY <?= htmlspecialchars($school_year) ?>
                </span>
            </div>
        </div>

        <!-- Stats Row -->
        <?php $total = count($students); ?>
        <div class="stats-row" id="statsRow">
            <div class="stat-box">
                <div class="stat-num" id="statTotal"><?= $total ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-box">
                <div class="stat-num" id="statGraded">0</div>
                <div class="stat-label">Fully Graded</div>
            </div>
            <div class="stat-box">
                <div class="stat-num" id="statPending">0</div>
                <div class="stat-label">Incomplete</div>
            </div>
            <div class="stat-box">
                <div class="stat-num" id="statAvg">—</div>
                <div class="stat-label">Class Average</div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">

            <div class="table-toolbar">
                <div class="table-title">
                    <span class="dot"></span>
                    Student Grade Sheet
                </div>
                <div class="search-box">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input type="text" id="searchInput" placeholder="Search student…" autocomplete="off">
                </div>
            </div>

            <div class="table-scroll">
                <form id="gradesForm" onsubmit="return false;">
                    <table id="gradesTable">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width:40px">#</th>
                                <th rowspan="2" style="width:80px">ID</th>
                                <th rowspan="2" style="min-width:160px; text-align:left; padding-left:16px">Student Name</th>
                                <th colspan="4" class="sem-header">1st Semester</th>
                                <th colspan="4" class="sem-header" style="border-left:3px solid rgba(255,255,255,0.3)">2nd Semester</th>
                                <th rowspan="2">Final</th>
                            </tr>
                            <tr class="sub-header">
                                <th>Q1</th>
                                <th>Q2</th>
                                <th>Sem Final</th>
                                <th>GWA</th>
                                <th style="border-left:3px solid var(--green-200)">Q3</th>
                                <th>Q4</th>
                                <th>Sem Final</th>
                                <th>GWA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($total === 0): ?>
                                <tr>
                                    <td colspan="12">
                                        <div class="empty-state">
                                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#a5c9a7" stroke-width="1.5">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                                <circle cx="9" cy="7" r="4" />
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                            </svg>
                                            <p>No students found for this section.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $i = 1;
                                foreach ($students as $st): ?>
                                    <?php
                                    $sid   = (int)$st['student_id'];
                                    $q1    = $st['q1']               ?? '';
                                    $q2    = $st['q2']               ?? '';
                                    $f1    = $st['first_sem_final']  ?? '';
                                    $gwa1  = $st['gwa_first_sem']    ?? '';
                                    $q3    = $st['q3']               ?? '';
                                    $q4    = $st['q4']               ?? '';
                                    $f2    = $st['second_sem_final'] ?? '';
                                    $gwa2  = $st['gwa_second_sem']   ?? '';
                                    $final = $st['final']            ?? '';
                                    ?>
                                    <tr data-student-id="<?= $sid ?>"
                                        data-name="<?= strtolower(htmlspecialchars($st['full_name'])) ?>">

                                        <td data-label="#" class="num-cell"><?= $i++ ?></td>
                                        <td data-label="Student ID" class="id-cell"><?= htmlspecialchars($sid) ?></td>
                                        <td data-label="Name" class="name-cell"><?= htmlspecialchars($st['full_name']) ?></td>

                                        <td data-label="Q1">
                                            <input class="grade-input q1" data-student="<?= $sid ?>"
                                                value="<?= htmlspecialchars($q1) ?>" placeholder="—">
                                        </td>
                                        <td data-label="Q2">
                                            <input class="grade-input q2" data-student="<?= $sid ?>"
                                                value="<?= htmlspecialchars($q2) ?>" placeholder="—">
                                        </td>
                                        <td data-label="Sem Final">
                                            <input class="grade-input first_sem_final readonly" readonly
                                                value="<?= htmlspecialchars($f1) ?>" tabindex="-1">
                                        </td>
                                        <td data-label="GWA 1st">
                                            <input class="grade-input gwa_first_sem readonly" readonly
                                                value="<?= htmlspecialchars($gwa1) ?>" tabindex="-1">
                                        </td>

                                        <td data-label="Q3" class="sem-divider-left">
                                            <input class="grade-input q3" data-student="<?= $sid ?>"
                                                value="<?= htmlspecialchars($q3) ?>" placeholder="—">
                                        </td>
                                        <td data-label="Q4">
                                            <input class="grade-input q4" data-student="<?= $sid ?>"
                                                value="<?= htmlspecialchars($q4) ?>" placeholder="—">
                                        </td>
                                        <td data-label="Sem Final">
                                            <input class="grade-input second_sem_final readonly" readonly
                                                value="<?= htmlspecialchars($f2) ?>" tabindex="-1">
                                        </td>
                                        <td data-label="GWA 2nd">
                                            <input class="grade-input gwa_second_sem readonly" readonly
                                                value="<?= htmlspecialchars($gwa2) ?>" tabindex="-1">
                                        </td>

                                        <td data-label="Final">
                                            <input class="grade-input final readonly" readonly
                                                value="<?= htmlspecialchars($final) ?>" tabindex="-1">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <input type="hidden" id="subject_id" value="<?= htmlspecialchars($subject_id) ?>">
                    <input type="hidden" id="school_year" value="<?= htmlspecialchars($school_year) ?>">
                </form>
            </div>

            <!-- Save Bar -->
            <div class="table-footer">
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <button class="save-btn" id="saveBtn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        Save All Grades
                    </button>
                    <span class="pending-indicator" id="pendingIndicator">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        Unsaved changes
                    </span>
                </div>
                <div class="message" id="message"></div>
            </div>

        </div><!-- /.table-card -->
    </main>

    <script>
        (function() {

            // ── Helpers ────────────────────────────────────────────────────────────
            function parseGrade(val) {
                if (val === null || val === undefined) return null;
                val = String(val).trim().replace(',', '.');
                if (val === '' || val === '—') return null;
                const n = parseFloat(val);
                return isNaN(n) ? null : n;
            }

            function fmt(n) {
                if (n === null || n === undefined) return '';
                return (Math.round(n * 100) / 100).toFixed(2);
            }

            function clamp(g) {
                if (g === null) return null;
                return Math.min(100, Math.max(0, g));
            }

            // ── Per-row computation ────────────────────────────────────────────────
            function computeRow(studentId) {
                const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
                if (!row) return;

                const q1i = row.querySelector('.q1'),
                    q2i = row.querySelector('.q2'),
                    q3i = row.querySelector('.q3'),
                    q4i = row.querySelector('.q4');

                const q1 = clamp(parseGrade(q1i.value)),
                    q2 = clamp(parseGrade(q2i.value)),
                    q3 = clamp(parseGrade(q3i.value)),
                    q4 = clamp(parseGrade(q4i.value));

                q2i.disabled = (q1 === null);
                q4i.disabled = (q3 === null);

                const f1 = (q1 !== null && q2 !== null) ? (q1 + q2) / 2 : null;
                const f2 = (q3 !== null && q4 !== null) ? (q3 + q4) / 2 : null;
                const final = (f1 !== null && f2 !== null) ? (f1 + f2) / 2 : null;

                row.querySelector('.first_sem_final').value = fmt(f1);
                row.querySelector('.gwa_first_sem').value = fmt(f1);
                row.querySelector('.second_sem_final').value = fmt(f2);
                row.querySelector('.gwa_second_sem').value = fmt(f2);
                row.querySelector('.final').value = fmt(final);

                // Color-code final grade
                const finalInput = row.querySelector('.final');
                finalInput.classList.remove('grade-high', 'grade-mid', 'grade-low');
                if (final !== null) {
                    if (final >= 75) finalInput.classList.add('grade-high');
                    else if (final >= 70) finalInput.classList.add('grade-mid');
                    else finalInput.classList.add('grade-low');
                }
            }

            // ── Stats update ───────────────────────────────────────────────────────
            function updateStats() {
                const rows = document.querySelectorAll('tbody tr[data-student-id]');
                let graded = 0,
                    finals = [];
                rows.forEach(row => {
                    const f = parseGrade(row.querySelector('.final').value);
                    if (f !== null) {
                        graded++;
                        finals.push(f);
                    }
                });
                document.getElementById('statGraded').textContent = graded;
                document.getElementById('statPending').textContent = rows.length - graded;
                document.getElementById('statAvg').textContent =
                    finals.length > 0 ?
                    fmt(finals.reduce((a, b) => a + b, 0) / finals.length) :
                    '—';
            }

            // ── Save button & message refs ─────────────────────────────────────────
            const saveBtn = document.getElementById('saveBtn');
            const msgEl = document.getElementById('message');

            // ── Track unsaved changes ──────────────────────────────────────────────
            let hasChanges = false;

            function setSaveBtnState(enabled) {
                saveBtn.disabled = !enabled;
                saveBtn.classList.toggle('no-changes', !enabled);
            }

            function markChanged() {
                hasChanges = true;
                document.getElementById('pendingIndicator').classList.add('visible');
                setSaveBtnState(true);
            }

            // ── Init all rows on load ──────────────────────────────────────────────
            document.querySelectorAll('tr[data-student-id]').forEach(row => {
                computeRow(row.getAttribute('data-student-id'));
            });
            updateStats();
            setSaveBtnState(false); // disabled on initial load — no changes yet

            // ── Input events ───────────────────────────────────────────────────────
            document.querySelectorAll('input.q1, input.q2, input.q3, input.q4').forEach(el => {
                el.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/[^0-9\.,\-]/g, '');
                    computeRow(e.target.getAttribute('data-student'));
                    updateStats();
                    markChanged();
                });

                // Enter key moves focus to the next editable input
                el.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const inputs = Array.from(
                            document.querySelectorAll('input.q1, input.q2, input.q3, input.q4')
                        ).filter(i => !i.disabled && !i.closest('tr')?.style.display?.includes('none'));
                        const idx = inputs.indexOf(e.target);
                        if (idx < inputs.length - 1) inputs[idx + 1].focus();
                    }
                });
            });

            // ── Live search ────────────────────────────────────────────────────────
            document.getElementById('searchInput')?.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                document.querySelectorAll('tbody tr[data-student-id]').forEach(row => {
                    row.style.display = (row.getAttribute('data-name') || '').includes(q) ? '' : 'none';
                });
            });

            // ── Collect payload ────────────────────────────────────────────────────
            function collectGrades() {
                return Array.from(document.querySelectorAll('tr[data-student-id]')).map(row => {
                    const sid = parseInt(row.getAttribute('data-student-id'));
                    const g = cls => {
                        const v = parseGrade(row.querySelector(`.${cls}`)?.value);
                        return v === null ? '' : fmt(v);
                    };
                    return {
                        student_id: sid,
                        q1: g('q1'),
                        q2: g('q2'),
                        first_sem_final: g('first_sem_final'),
                        gwa_first_sem: g('gwa_first_sem'),
                        q3: g('q3'),
                        q4: g('q4'),
                        second_sem_final: g('second_sem_final'),
                        gwa_second_sem: g('gwa_second_sem'),
                        final: g('final')
                    };
                }).filter(g => g.student_id);
            }

            // ── Save grades ────────────────────────────────────────────────────────
            const SAVE_ICON = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
        <polyline points="17 21 17 13 7 13 7 21"/>
        <polyline points="7 3 7 8 15 8"/>
    </svg>`;
            const SPIN_ICON = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
    </svg>`;

            function showMsg(text, type) {
                msgEl.textContent = text;
                msgEl.className = `message ${type}`;
                if (type === 'success') {
                    setTimeout(() => {
                        msgEl.className = 'message';
                        msgEl.textContent = '';
                    }, 4000);
                }
            }

            saveBtn.addEventListener('click', function() {
                const subject_id = document.getElementById('subject_id').value;
                const school_year = document.getElementById('school_year').value;
                if (!subject_id || !school_year) {
                    showMsg('Missing subject or school year.', 'error');
                    return;
                }

                const grades = collectGrades();
                saveBtn.disabled = true;
                saveBtn.classList.add('saving');
                saveBtn.innerHTML = SPIN_ICON + ' Saving…';

                const form = new FormData();
                form.append('subject_id', subject_id);
                form.append('school_year', school_year);
                form.append('grades', JSON.stringify(grades));

                fetch('save_grades.php', {
                        method: 'POST',
                        body: form,
                        credentials: 'same-origin'
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.status) {
                            showMsg('Grades saved successfully.', 'success');
                            hasChanges = false;
                            document.getElementById('pendingIndicator').classList.remove('visible');
                            setSaveBtnState(false);
                            grades.forEach(g => {
                                const row = document.querySelector(`tr[data-student-id="${g.student_id}"]`);
                                if (!row) return;
                                ['q1', 'q2', 'first_sem_final', 'gwa_first_sem',
                                    'q3', 'q4', 'second_sem_final', 'gwa_second_sem', 'final'
                                ]
                                .forEach(k => {
                                    const el = row.querySelector(`.${k}`);
                                    if (el) el.value = g[k];
                                });
                                computeRow(g.student_id);
                            });
                            updateStats();
                        } else {
                            showMsg((data && data.message) ? data.message : 'Failed to save grades.', 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showMsg('Network error: ' + err.message, 'error');
                    })
                    .finally(() => {
                        saveBtn.disabled = false;
                        saveBtn.classList.remove('saving');
                        saveBtn.innerHTML = SAVE_ICON + ' Save All Grades';
                    });
            });

            // ── Warn on navigate away with unsaved changes ─────────────────────────
            window.addEventListener('beforeunload', e => {
                if (hasChanges) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            // ── Inactivity auto-logout ─────────────────────────────────────────────
            const LIMIT = 5 * 60 * 1000,
                WARN = 10 * 1000;
            let inactTimer, warnTimer;

            function resetTimer() {
                clearTimeout(inactTimer);
                clearTimeout(warnTimer);
                warnTimer = setTimeout(showWarning, LIMIT - WARN);
                inactTimer = setTimeout(logoutUser, LIMIT);
            }

            function showWarning() {
                if (document.getElementById('inactivityWarning')) return;
                const d = document.createElement('div');
                d.id = 'inactivityWarning';
                let cd = 10;
                d.innerHTML = `
            <h4>&#9888; Inactivity Warning</h4>
            <p>You'll be logged out in <strong id="countdown">10</strong> seconds.</p>
            <button id="stayLoggedIn">Stay Logged In</button>`;
                document.body.appendChild(d);
                const interval = setInterval(() => {
                    cd--;
                    const el = document.getElementById('countdown');
                    if (el) el.textContent = cd;
                    if (cd <= 0) clearInterval(interval);
                }, 1000);
                document.getElementById('stayLoggedIn').addEventListener('click', () => {
                    clearInterval(interval);
                    d.remove();
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

            ['mousemove', 'keydown', 'mousedown', 'touchstart']
            .forEach(e => document.addEventListener(e, resetTimer));
            resetTimer();

            // ── Mobile nav toggle ──────────────────────────────────────────────────
            document.getElementById('menuToggle').addEventListener('click', () => {
                document.getElementById('navbarLinks').classList.toggle('active');
            });

        })();
    </script>
</body>

</html>