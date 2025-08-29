<?php
include '../db_connect.php';

if (isset($_POST['grade_id']) && !empty($_POST['grade_id'])) {
    $grade_id = intval($_POST['grade_id']); // Sanitize input

    // Prepare the query to fetch classes with their subjects for the selected grade
    $stmt = $conn->prepare("
        SELECT c.id, c.title, s.subject_name 
        FROM classes c
        JOIN subjects s ON c.subject = s.id
        WHERE c.grade = ?
    ");
    $stmt->bind_param("i", $grade_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }

    // Return JSON response
    echo json_encode($classes);
} else {
    echo json_encode([]); // Return empty array if grade_id is missing
}
?>
