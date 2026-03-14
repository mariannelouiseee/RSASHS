<?php
include("connect.php");
session_start();

if (!isset($_SESSION['teacher_id'])) {
    echo "<script>alert('You must be logged in as teacher.'); window.location='login.php';</script>";
    exit();
}

$teacher_id = trim($_SESSION['teacher_id']);

$teach_stmt = $conn->prepare("SELECT first_name, middle_name, last_name FROM teachers WHERE teacher_id = ?");
$teach_stmt->bind_param("s", $teacher_id);
$teach_stmt->execute();
$teach_row = $teach_stmt->get_result()->fetch_assoc();
$teach_stmt->close();

if (!$teach_row) {
    $full_name = "Unknown Teacher";
} else {
    $mi = $teach_row['middle_name'] ? strtoupper($teach_row['middle_name'][0]) . '.' : '';
    $full_name = trim("{$teach_row['first_name']} $mi {$teach_row['last_name']}");
}

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

$sec_stmt = $conn->prepare("SELECT school_year FROM sections WHERE year_level=? AND section_name=? LIMIT 1");
$sec_stmt->bind_param("ss", $year_level, $section_name);
$sec_stmt->execute();
$sec_row = $sec_stmt->get_result()->fetch_assoc();
$sec_stmt->close();

if (!$sec_row) {
    echo "Section not found.";
    exit();
}
$school_year = trim($sec_row['school_year']);

$sub_stmt = $conn->prepare("SELECT subject_name FROM subjects WHERE subject_id = ?");
$sub_stmt->bind_param("i", $subject_id);
$sub_stmt->execute();
$sub_row = $sub_stmt->get_result()->fetch_assoc();
$sub_stmt->close();
$subject_name = $sub_row ? $sub_row['subject_name'] : "Unknown Subject";

$sql = "
    SELECT s.student_id,
           CONCAT(s.last_name, ', ', s.first_name) AS full_name,
           g.q1, g.q2, g.first_sem_final, g.gwa_first_sem,
           g.q3, g.q4, g.second_sem_final, g.gwa_second_sem, g.final
    FROM students s
    LEFT JOIN grades g
      ON s.student_id = g.student_id
      AND g.subject_id = ?
      AND TRIM(g.school_year) = ?
    WHERE TRIM(s.year_level) = ?
      AND TRIM(s.section_name) = ?
      AND TRIM(s.school_year) = ?
    ORDER BY s.last_name, s.first_name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $subject_id, $school_year, $year_level, $section_name, $school_year);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($r = $result->fetch_assoc()) $students[] = $r;
$total = count($students);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Input Grades — <?= htmlspecialchars($year_level . " - " . $section_name) ?></title>
    <link rel="stylesheet" href="input_grade.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
</head>

<body>

    <!-- ===== HEADER ===== -->
    <header>
        <div class="header-left">
            <img src="./img/logo.jpg" alt="RSASHS Logo">
            <h2>RSASHS E-PORTAL</h2>
        </div>
        <button id="menuToggle" class="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <nav class="navbar" id="navbarLinks">
            <ul>
                <li><a href="teacher_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="teacher_advisory.php"><i class="fas fa-users"></i> Advisory Class</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- ===== MAIN ===== -->
    <main>

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="teacher_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <span class="bc-sep"><i class="fas fa-chevron-right"></i></span>
            <span>Input Grades</span>
        </div>

        <!-- Info Card -->
        <div class="info-card">
            <div class="info-card-left">
                <h1>
                    <i class="fas fa-clipboard-list" style="color:#4caf50; font-size:18px; margin-right:8px;"></i>
                    <?= htmlspecialchars($year_level . " · " . $section_name) ?>
                </h1>
                <p>Enter and save quarterly grades for all enrolled students in this class.</p>
            </div>
            <div class="info-badges">
                <span class="badge badge-green">
                    <i class="fas fa-book-open"></i>
                    <?= htmlspecialchars($subject_name) ?>
                </span>
                <span class="badge badge-blue">
                    <i class="fas fa-calendar-alt"></i>
                    S.Y. <?= htmlspecialchars($school_year) ?>
                </span>
                <span class="badge badge-purple">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <?= htmlspecialchars($full_name) ?>
                </span>
            </div>
        </div>

        <!-- Stats Row -->
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
                    <i class="fas fa-search" style="font-size:12px; color:#81a881;"></i>
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
                                <th rowspan="2" class="th-name">Student Name</th>
                                <th colspan="4" class="sem-header">1st Semester</th>
                                <th colspan="4" class="sem-header sem-border">2nd Semester</th>
                                <th rowspan="2">Final</th>
                            </tr>
                            <tr class="sub-header">
                                <th>Q1</th>
                                <th>Q2</th>
                                <th>Sem Final</th>
                                <th>GWA</th>
                                <th class="sem-border">Q3</th>
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
                                            <i class="fas fa-user-slash" style="font-size:36px; color:#c8e6c9; margin-bottom:12px; display:block;"></i>
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
                                            <input class="grade-input q1" data-student="<?= $sid ?>" value="<?= htmlspecialchars($q1) ?>" placeholder="—">
                                        </td>
                                        <td data-label="Q2">
                                            <input class="grade-input q2" data-student="<?= $sid ?>" value="<?= htmlspecialchars($q2) ?>" placeholder="—">
                                        </td>
                                        <td data-label="Sem Final">
                                            <input class="grade-input first_sem_final readonly" readonly value="<?= htmlspecialchars($f1) ?>" tabindex="-1">
                                        </td>
                                        <td data-label="GWA 1st">
                                            <input class="grade-input gwa_first_sem readonly" readonly value="<?= htmlspecialchars($gwa1) ?>" tabindex="-1">
                                        </td>
                                        <td data-label="Q3" class="sem-divider-left">
                                            <input class="grade-input q3" data-student="<?= $sid ?>" value="<?= htmlspecialchars($q3) ?>" placeholder="—">
                                        </td>
                                        <td data-label="Q4">
                                            <input class="grade-input q4" data-student="<?= $sid ?>" value="<?= htmlspecialchars($q4) ?>" placeholder="—">
                                        </td>
                                        <td data-label="Sem Final">
                                            <input class="grade-input second_sem_final readonly" readonly value="<?= htmlspecialchars($f2) ?>" tabindex="-1">
                                        </td>
                                        <td data-label="GWA 2nd">
                                            <input class="grade-input gwa_second_sem readonly" readonly value="<?= htmlspecialchars($gwa2) ?>" tabindex="-1">
                                        </td>
                                        <td data-label="Final">
                                            <input class="grade-input final readonly" readonly value="<?= htmlspecialchars($final) ?>" tabindex="-1">
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
                        <i class="fas fa-save"></i>
                        Save All Grades
                    </button>
                    <span class="pending-indicator" id="pendingIndicator">
                        <i class="fas fa-circle" style="font-size:8px;"></i>
                        Unsaved changes
                    </span>
                </div>
                <div class="message" id="message"></div>
            </div>

        </div><!-- /.table-card -->
    </main>

    <script>
        (function() {

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

                const fi = row.querySelector('.final');
                fi.classList.remove('grade-high', 'grade-mid', 'grade-low');
                if (final !== null) {
                    if (final >= 75) fi.classList.add('grade-high');
                    else if (final >= 70) fi.classList.add('grade-mid');
                    else fi.classList.add('grade-low');
                }
            }

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
                    finals.length > 0 ? fmt(finals.reduce((a, b) => a + b, 0) / finals.length) : '—';
            }

            const saveBtn = document.getElementById('saveBtn');
            const msgEl = document.getElementById('message');
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

            document.querySelectorAll('tr[data-student-id]').forEach(row => {
                computeRow(row.getAttribute('data-student-id'));
            });
            updateStats();
            setSaveBtnState(false);

            document.querySelectorAll('input.q1, input.q2, input.q3, input.q4').forEach(el => {
                el.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/[^0-9\.,\-]/g, '');
                    computeRow(e.target.getAttribute('data-student'));
                    updateStats();
                    markChanged();
                });
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

            document.getElementById('searchInput')?.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                document.querySelectorAll('tbody tr[data-student-id]').forEach(row => {
                    row.style.display = (row.getAttribute('data-name') || '').includes(q) ? '' : 'none';
                });
            });

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

            function showMsg(text, type) {
                msgEl.textContent = text;
                msgEl.className = `message ${type}`;
                if (type === 'success') setTimeout(() => {
                    msgEl.className = 'message';
                    msgEl.textContent = '';
                }, 4000);
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
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

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
                                ['q1', 'q2', 'first_sem_final', 'gwa_first_sem', 'q3', 'q4', 'second_sem_final', 'gwa_second_sem', 'final']
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
                    .catch(err => showMsg('Network error: ' + err.message, 'error'))
                    .finally(() => {
                        saveBtn.disabled = false;
                        saveBtn.classList.remove('saving');
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save All Grades';
                    });
            });

            window.addEventListener('beforeunload', e => {
                if (hasChanges) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            // Inactivity logout
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
                d.innerHTML = `
                    <strong style="font-size:16px;color:#1b5e20;">Inactivity Warning</strong>
                    <span>You will be logged out in <span id="countdown">10</span> seconds.</span>
                    <button id="stayLoggedIn" style="padding:8px 12px;background:#2e7d32;color:white;border:none;border-radius:6px;font-weight:bold;cursor:pointer;align-self:flex-end;">Stay Logged In</button>`;
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
                    })
                    .then(r => r.json())
                    .then(d => {
                        alert(d.message || 'Logged out.');
                        window.location.href = 'login.php';
                    })
                    .catch(() => {
                        window.location.href = 'login.php';
                    });
            }

            ['mousemove', 'keydown', 'mousedown', 'touchstart'].forEach(e => document.addEventListener(e, resetTimer));
            resetTimer();

            // Navbar toggle
            document.getElementById('menuToggle').addEventListener('click', () => {
                document.querySelector('#navbarLinks ul').classList.toggle('active');
            });
        })();
    </script>
</body>

</html>