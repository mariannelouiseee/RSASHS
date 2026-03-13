<?php
// save_grades.php
include("connect.php");
session_start();
header('Content-Type: application/json');

// ===== Check teacher session =====
if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['status' => false, 'message' => 'Not authenticated.']);
    exit();
}

$subject_id = $_POST['subject_id'] ?? '';
$school_year = $_POST['school_year'] ?? '';
$grades_json = $_POST['grades'] ?? '';

if (empty($subject_id) || empty($school_year) || empty($grades_json)) {
    echo json_encode(['status' => false, 'message' => 'Missing parameters.']);
    exit();
}

$subject_id = (int)$subject_id;
$school_year = $conn->real_escape_string($school_year);

// Decode grades
$grades = json_decode($grades_json, true);
if (!is_array($grades)) {
    echo json_encode(['status' => false, 'message' => 'Invalid grades payload.']);
    exit();
}

// Prepare insert/update statement
$sql = "
INSERT INTO grades (
    student_id, subject_id, school_year,
    q1, q2, first_sem_final, gwa_first_sem,
    q3, q4, second_sem_final, gwa_second_sem,
    final, created_at, updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    q1=VALUES(q1),
    q2=VALUES(q2),
    first_sem_final=VALUES(first_sem_final),
    gwa_first_sem=VALUES(gwa_first_sem),
    q3=VALUES(q3),
    q4=VALUES(q4),
    second_sem_final=VALUES(second_sem_final),
    gwa_second_sem=VALUES(gwa_second_sem),
    final=VALUES(final),
    updated_at=NOW()
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['status' => false, 'message' => 'DB prepare failed: ' . $conn->error]);
    exit();
}

$success_count = 0;
$errors = [];

// Format function
$format = function ($v) {
    if ($v === null || $v === '') return null;
    $v = str_replace(',', '.', $v);
    return is_numeric($v) ? number_format((float)$v, 2, '.', '') : null;
};

foreach ($grades as $g) {
    $student_id = (int)($g['student_id'] ?? 0);
    if (!$student_id) continue;

    $q1 = $format($g['q1'] ?? null);
    $q2 = $format($g['q2'] ?? null);
    $f1 = $format($g['first_sem_final'] ?? null);
    $gwa1 = $format($g['gwa_first_sem'] ?? null);
    $q3 = $format($g['q3'] ?? null);
    $q4 = $format($g['q4'] ?? null);
    $f2 = $format($g['second_sem_final'] ?? null);
    $gwa2 = $format($g['gwa_second_sem'] ?? null);
    $final = $format($g['final'] ?? null);

    $stmt->bind_param(
        "iissssssssss",
        $student_id,
        $subject_id,
        $school_year,
        $q1,
        $q2,
        $f1,
        $gwa1,
        $q3,
        $q4,
        $f2,
        $gwa2,
        $final
    );

    if ($stmt->execute()) {
        $success_count++;
    } else {
        $errors[] = "Student $student_id: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();

if (count($errors) === 0) {
    echo json_encode(['status' => true, 'message' => "Saved grades for $success_count students."]);
} else {
    echo json_encode(['status' => false, 'message' => "Saved $success_count students, errors: " . implode('; ', $errors)]);
}
