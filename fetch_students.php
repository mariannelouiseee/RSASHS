<?php
include("connect.php");
include("functions.php");
session_start(); // Needed to access admin_id for logging

$year_level = $_GET['year_level'] ?? '';
$section_name = $_GET['section_name'] ?? '';
$mode = $_GET['mode'] ?? 'assign';

// Validate inputs for reassign
if ($mode === 'reassign' && ($year_level === '' || $section_name === '')) {
    echo "Missing required parameters.";
    exit;
}

// Fetch students
if ($mode === 'assign') {
    // Only unassigned students
    $students = $conn->query("
        SELECT student_id, first_name, last_name 
        FROM students 
        WHERE section_name IS NULL OR section_name = ''
        ORDER BY last_name ASC
    ");
} else {
    // Reassign: unassigned + students already in this section
    $stmt = $conn->prepare("
        SELECT student_id, first_name, last_name, section_name 
        FROM students 
        WHERE section_name IS NULL OR section_name = '' 
           OR (section_name = ? AND year_level = ?)
        ORDER BY last_name ASC
    ");
    $stmt->bind_param("ss", $section_name, $year_level);
    $stmt->execute();
    $students = $stmt->get_result();
}

// Count total students fetched
$total_students = $students->num_rows;

// --- Admin log ---
if (isset($_SESSION['admin_id'])) {
    addLog(
        $conn,
        $_SESSION['admin_id'],
        'admin',
        ucfirst($mode) . " mode: Viewed student list for section '$section_name' (Year Level: $year_level) - $total_students student(s) retrieved"
    );
}

// Output student checkboxes table
echo "<table style='width:100%;'><thead><tr><th>Select</th><th>ID</th><th>Name</th></tr></thead><tbody>";
while ($s = $students->fetch_assoc()) {
    $studentSection = $s['section_name'] ?? '';
    $checked = ($studentSection === $section_name) ? "checked" : "";
    echo "<tr>
        <td><input type='checkbox' name='student_ids[]' value='" . htmlspecialchars($s['student_id']) . "' $checked></td>
        <td>" . htmlspecialchars($s['student_id']) . "</td>
        <td>" . htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) . "</td>
    </tr>";
}
echo "</tbody></table>";
?>