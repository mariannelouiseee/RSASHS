<?php
include("connect.php");
header('Content-Type: application/json');

$id   = isset($_GET['id'])   ? trim($_GET['id'])   : '';
$role = isset($_GET['role']) ? trim($_GET['role']) : 'student';

if (empty($id)) {
    echo json_encode(['error' => 'No ID provided.']);
    exit;
}

$data = [
    'id'          => $id,
    'role'        => $role,
    'first_name'  => '',
    'middle_name' => '',
    'last_name'   => '',
    'birthday'    => null,
    'image'       => null,
    'section'     => null,
    'subjects'    => [],
];

if ($role === 'student') {
    $stmt = $conn->prepare("SELECT first_name, middle_name, last_name, birthday, student_image FROM students WHERE student_id = ? LIMIT 1");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $data['first_name']  = $row['first_name']  ?? '';
        $data['middle_name'] = $row['middle_name'] ?? '';
        $data['last_name']   = $row['last_name']   ?? '';
        $data['birthday']    = !empty($row['birthday']) ? date("F d, Y", strtotime($row['birthday'])) : null;
        $data['image']       = !empty($row['student_image']) ? $row['student_image'] : null;
    }
    $stmt->close();

    // Section info
    $stmt2 = $conn->prepare("
        SELECT sec.section_name, sec.year_level
        FROM student_sections ss
        JOIN sections sec ON ss.section_id = sec.section_id
        WHERE ss.student_id = ?
        LIMIT 1
    ");
    if ($stmt2) {
        $stmt2->bind_param("s", $id);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        if ($srow = $res2->fetch_assoc()) {
            $data['section'] = ($srow['year_level'] ?? '') . ' - ' . ($srow['section_name'] ?? '');
        }
        $stmt2->close();
    }
} else if ($role === 'teacher') {
    $stmt = $conn->prepare("SELECT first_name, middle_name, last_name, teacher_image FROM teachers WHERE teacher_id = ? LIMIT 1");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $data['first_name']  = $row['first_name']  ?? '';
        $data['middle_name'] = $row['middle_name'] ?? '';
        $data['last_name']   = $row['last_name']   ?? '';
        $data['image']       = !empty($row['teacher_image']) ? $row['teacher_image'] : null;
    }
    $stmt->close();

    // Subjects handled
    $stmt3 = $conn->prepare("
        SELECT DISTINCT s.subject_name
        FROM teacher_subjects ts
        JOIN subjects s ON ts.subject_id = s.subject_id
        WHERE ts.teacher_id = ?
        ORDER BY s.subject_name ASC
    ");
    if ($stmt3) {
        $stmt3->bind_param("s", $id);
        $stmt3->execute();
        $res3 = $stmt3->get_result();
        while ($srow = $res3->fetch_assoc()) {
            $data['subjects'][] = $srow['subject_name'];
        }
        $stmt3->close();
    }
}

echo json_encode($data);
