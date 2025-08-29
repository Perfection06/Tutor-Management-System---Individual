<?php
include '../db_connect.php';

if (isset($_POST['class_id']) && !empty($_POST['class_id'])) {
    $class_id = intval($_POST['class_id']);

    // Fetch students enrolled in the class
    $query = $conn->prepare("
        SELECT s.id, s.name 
        FROM Student_details sd
        JOIN Student s ON sd.student_id = s.id
        WHERE sd.class_id = ?
    ");
    $query->bind_param("i", $class_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        echo '<form id="attendanceForm">';
        echo '<table><tr><th>Student Name</th><th>Attendance</th></tr>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['name'] . '</td>';
            echo '<td>';
            echo '<select name="attendance[' . $row['id'] . ']">';
            echo '<option value="Present">Present</option>';
            echo '<option value="Absent">Absent</option>';
            echo '</select>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '<button type="button" onclick="markAttendance()">Submit Attendance</button>';
        echo '</form>';
    } else {
        echo 'No students found for the selected class.';
    }
} else {
    echo 'Invalid request.';
}
?>
