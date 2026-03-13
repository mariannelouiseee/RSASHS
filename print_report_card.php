<?php
// report_card_landscape.php
include("connect.php");
session_start();

// ===== CHECK TEACHER LOGIN =====
if (!isset($_SESSION['teacher_id'])) {
    echo "<script>alert('You must be logged in as teacher.'); window.location='login.php';</script>";
    exit();
}

$year_level = $_GET['year_level'] ?? '';
$section_name = $_GET['section_name'] ?? '';
$school_year = $_GET['school_year'] ?? '';
$student_id = $_GET['student_id'] ?? '';

if (empty($year_level) || empty($section_name) || empty($school_year) || empty($student_id)) {
    echo "Missing required parameters.";
    exit();
}

$year_level = trim($year_level);
$section_name = trim($section_name);
$school_year = $conn->real_escape_string($school_year);
$student_id = $conn->real_escape_string($student_id);

// ===== FETCH SPECIFIC STUDENT =====
$students_sql = "SELECT student_id, CONCAT(first_name,' ',last_name) AS full_name
                 FROM students
                 WHERE student_id = ? AND TRIM(year_level)=? AND TRIM(section_name)=?
                 ORDER BY last_name, first_name";
$stmt = $conn->prepare($students_sql);
$stmt->bind_param("sss", $student_id, $year_level, $section_name);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check if student exists
if (empty($students)) {
    echo "Student not found.";
    exit();
}

// ===== FETCH SUBJECTS FUNCTION (SEMESTER-BASED) =====
function getStudentSubjectsBySemester($student_id, $school_year, $conn)
{
    $sql = "
        SELECT sub.subject_name, sub.category, sub.semester,
               g.q1, g.q2, g.q3, g.q4
        FROM students stu
        INNER JOIN sections sec
            ON stu.section_name = sec.section_name AND stu.year_level = sec.year_level
        INNER JOIN section_subjects ss
            ON sec.section_id = ss.section_id
        INNER JOIN subjects sub
            ON ss.subject_id = sub.subject_id
        LEFT JOIN grades g
            ON g.student_id = stu.student_id
            AND g.subject_id = sub.subject_id
            AND g.school_year = ?
        WHERE stu.student_id = ?
        ORDER BY sub.semester ASC, 
                 CASE 
                    WHEN sub.category = 'Core Subjects' THEN 1
                    WHEN sub.category = 'Applied and Specialized Subjects' THEN 2
                    ELSE 3
                 END,
                 sub.subject_name ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $school_year, $student_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $subjects_by_semester = ['1st Semester' => [], '2nd Semester' => []];

    while ($row = $res->fetch_assoc()) {
        $sem = $row['semester'] ?? '1st Semester';
        $subjects_by_semester[$sem][$row['category']][] = $row;
    }
    $stmt->close();

    // Sort categories to ensure Core Subjects appear first
    foreach ($subjects_by_semester as $sem => &$categories) {
        $sorted = [];
        if (isset($categories['Core Subjects'])) {
            $sorted['Core Subjects'] = $categories['Core Subjects'];
        }
        if (isset($categories['Applied and Specialized Subjects'])) {
            $sorted['Applied and Specialized Subjects'] = $categories['Applied and Specialized Subjects'];
        }
        // Add any other categories
        foreach ($categories as $cat => $subs) {
            if (!isset($sorted[$cat])) {
                $sorted[$cat] = $subs;
            }
        }
        $categories = $sorted;
    }

    return $subjects_by_semester;
}

// ===== CALC FUNCTIONS =====
function calcSemesterAverage($q1, $q2)
{
    return ($q1 !== null && $q2 !== null) ? round(($q1 + $q2) / 2, 2) : null;
}

function calcGeneralAverage($sem1, $sem2)
{
    if ($sem1 === null && $sem2 === null) return null;
    if ($sem1 !== null && $sem2 !== null) return round(($sem1 + $sem2) / 2, 2);
    return $sem1 ?? $sem2;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Report Card - <?= htmlspecialchars($year_level . " " . $section_name) ?></title>
    <link rel="stylesheet" href="report_card.css">
    <link rel="icon" type="image/x-icon" href="./img/logo.jpg">
</head>

<body>

    <div style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #e0e0e0;">
        <div style="background-color: white;">
            <?php foreach ($students as $student): ?>
                <div class="page">
                    <div class="container">
                        <!-- LEFT SECTION: Learning Progress and Achievement -->
                        <div class="left-section">
                            <h2>Report on Learning Progress and Achievement</h2>

                            <?php
                            $subjects_by_semester = getStudentSubjectsBySemester($student['student_id'], $school_year, $conn);
                            $sem1_avg = null;
                            $sem2_avg = null;
                            ?>

                            <!-- First Semester -->
                            <?php
                            $semester_data = $subjects_by_semester['1st Semester'] ?? [];
                            $sem_grades = [];
                            ?>
                            <h3>First Semester</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="subject-col" style="text-align: center; font-weight: bold;">Subjects</th>
                                        <th colspan="2">Quarter</th>
                                        <th rowspan="2">Semester<br>Final Grade</th>
                                    </tr>
                                    <tr>
                                        <th>1</th>
                                        <th>2</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($semester_data as $category => $subjects): ?>
                                        <tr>
                                            <td class="category-header" colspan="4"><?= htmlspecialchars($category) ?></td>
                                        </tr>
                                        <?php foreach ($subjects as $sub):
                                            $q1 = $sub['q1'];
                                            $q2 = $sub['q2'];
                                            $sem_grade = calcSemesterAverage($q1, $q2);
                                            if ($sem_grade !== null) $sem_grades[] = $sem_grade;
                                        ?>
                                            <tr>
                                                <td class="subject-col indent"><?= htmlspecialchars($sub['subject_name']) ?></td>
                                                <td><?= $q1 ?? '' ?></td>
                                                <td><?= $q2 ?? '' ?></td>
                                                <td><?= $sem_grade ?? '' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                    <tr class="average-row">
                                        <td class="subject-col no-border" colspan="3" style="text-align: right; padding-right: 10px; font-weight: bold;">General Average for the Semester</td>
                                        <td class="with-border"><?php
                                                                if (!empty($sem_grades)) {
                                                                    $sem1_avg = round(array_sum($sem_grades) / count($sem_grades), 2);
                                                                    echo $sem1_avg;
                                                                }
                                                                ?></td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Second Semester -->
                            <?php
                            $semester_data = $subjects_by_semester['2nd Semester'] ?? [];
                            $sem_grades = [];
                            ?>
                            <h3>Second Semester</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="subject-col" style="text-align: center; font-weight: bold;">Subjects</th>
                                        <th colspan="2">Quarter</th>
                                        <th rowspan="2">Semester<br>Final Grade</th>
                                    </tr>
                                    <tr>
                                        <th>3</th>
                                        <th>4</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($semester_data as $category => $subjects): ?>
                                        <tr>
                                            <td class="category-header" colspan="4"><?= htmlspecialchars($category) ?></td>
                                        </tr>
                                        <?php foreach ($subjects as $sub):
                                            $q3 = $sub['q3'];
                                            $q4 = $sub['q4'];
                                            $sem_grade = calcSemesterAverage($q3, $q4);
                                            if ($sem_grade !== null) $sem_grades[] = $sem_grade;
                                        ?>
                                            <tr>
                                                <td class="subject-col indent"><?= htmlspecialchars($sub['subject_name']) ?></td>
                                                <td><?= $q3 ?? '' ?></td>
                                                <td><?= $q4 ?? '' ?></td>
                                                <td><?= $sem_grade ?? '' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                    <tr class="average-row">
                                        <td class="subject-col no-border" colspan="3" style="text-align: right; padding-right: 10px; font-weight: bold;">General Average for the Semester</td>
                                        <td class="with-border"><?php
                                                                if (!empty($sem_grades)) {
                                                                    $sem2_avg = round(array_sum($sem_grades) / count($sem_grades), 2);
                                                                    echo $sem2_avg;
                                                                }
                                                                ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- RIGHT SECTION: Observed Values -->
                        <div class="right-section">
                            <div class="values-content">
                                <h2>Report on Learner's Observed Values</h2>

                                <table class="values-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="core-value-col">Core Values</th>
                                            <th rowspan="2" class="behavior-col">Behavior Statements</th>
                                            <th colspan="4">Quarter</th>
                                        </tr>
                                        <tr>
                                            <th>1</th>
                                            <th>2</th>
                                            <th>3</th>
                                            <th>4</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td rowspan="2" class="core-value-col">1. Maka-Diyos</td>
                                            <td class="behavior-col">Expresses one's spiritual beliefs while respecting the spiritual beliefs of others</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td class="behavior-col">Shows adherence to ethical principles by upholding truth</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td rowspan="2" class="core-value-col">2. Makatao</td>
                                            <td class="behavior-col">Is sensitive to individual, social and cultural differences</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td class="behavior-col">Demonstrates contributions towards solidarity</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td rowspan="2" class="core-value-col">3. Maka-kalikasan</td>
                                            <td class="behavior-col">Cares for the environment and utilizes resources wisely, judiciously, and economically</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td class="behavior-col">Demonstrates appropriate behavior in carrying out activities in the school, community and country</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td rowspan="2" class="core-value-col">4. Makabansa</td>
                                            <td class="behavior-col">Demonstrates pride in being a Filipino, exercises the rights and responsibilities of a Filipino citizen</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td class="behavior-col">Demonstrates appropriate behavior in carrying out activities in the school, community and country</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Legend Section -->
                                <div class="legend">
                                    <div class="legend-row">
                                        <div class="legend-column">
                                            <h4>Observed Values Marking</h4>
                                            <div class="legend-item">AO - Always Observed</div>
                                            <div class="legend-item">SO - Sometimes Observed</div>
                                            <div class="legend-item">RO - Rarely Observed</div>
                                            <div class="legend-item">NO - Not Observed</div>
                                        </div>
                                        <div class="legend-column">
                                            <h4>Non-numerical Rating</h4>
                                            <div class="legend-item">O - Outstanding</div>
                                            <div class="legend-item">VS - Very Satisfactory</div>
                                            <div class="legend-item">S - Satisfactory</div>
                                            <div class="legend-item">NI - Needs Improvement</div>
                                        </div>
                                    </div>

                                    <h4>Learner Progress and Achievement Descriptors</h4>
                                    <table class="grading-table">
                                        <tr>
                                            <td style="width: 30%;">Outstanding</td>
                                            <td style="width: 20%; font-weight: bold;">Grading Scale</td>
                                            <td style="width: 25%; font-weight: bold;">Remarks</td>
                                        </tr>
                                        <tr>
                                            <td>Very Satisfactory</td>
                                            <td>90 – 100</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>Satisfactory</td>
                                            <td>85 – 89</td>
                                            <td>Passed</td>
                                        </tr>
                                        <tr>
                                            <td>Fairly Satisfactory</td>
                                            <td>80 – 84</td>
                                            <td>Passed</td>
                                        </tr>
                                        <tr>
                                            <td>Did Not Meet Expectation</td>
                                            <td>75 – 79</td>
                                            <td>Passed</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>Below 75</td>
                                            <td>Failed</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="button-container">
        <button onclick="window.location.href='teacher_advisory.php'" class="btn btn-close">Close</button>
        <button onclick="window.print()" class="btn btn-print">Print Report Cards</button>
    </div>
</body>

</html>