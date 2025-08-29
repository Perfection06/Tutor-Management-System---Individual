<?php
session_start();
include '../db_connect.php';

// Check if the user is logged in and is a course student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'course') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch enrolled courses
$query = "SELECT c.id, c.course_name FROM courses c 
          JOIN student_courses sc ON c.id = sc.course_id 
          WHERE sc.student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$courses_result = $stmt->get_result();

// Fetch attendance records for the selected course
$attendance = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
    $attendance_query = "SELECT date, status FROM course_attendance WHERE course_id = ? AND student_id = ?";
    $attendance_stmt = $conn->prepare($attendance_query);
    $attendance_stmt->bind_param("ii", $course_id, $student_id);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();
    while ($row = $attendance_result->fetch_assoc()) {
        $attendance[] = $row;
    }
    $attendance_stmt->close();
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Attendance</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Page Title -->
            <h1>Course Attendance</h1>
            <!-- Back Button -->
            <a href="course_student_dashboard.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <!-- Course Selection Form -->
        <form method="POST" class="mb-4">
            <label for="courseDropdown" class="form-label">Select a Course:</label>
            <select name="course_id" id="courseDropdown" class="form-select" onchange="this.form.submit()">
                <option value="">-- Select a course --</option>
                <?php while ($course = $courses_result->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($course['id']) ?>" <?= isset($_POST['course_id']) && $_POST['course_id'] == $course['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($course['course_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <!-- Attendance Table -->
        <?php if (!empty($attendance)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance as $record): ?>
                            <tr>
                                <td><?= htmlspecialchars($record['date']) ?></td>
                                <td><?= htmlspecialchars($record['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])): ?>
            <div class="alert alert-warning text-center">
                No attendance records found for the selected course.
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
