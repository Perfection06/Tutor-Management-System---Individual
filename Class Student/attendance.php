<?php
session_start();
include '../db_connect.php';

// Check if the user is logged in and is a class student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'class') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch classes the student is enrolled in
$query_classes = "SELECT cl.id, cl.title FROM classes cl 
                  JOIN student_classes sc ON cl.id = sc.class_id 
                  WHERE sc.student_id = ?";
$stmt_classes = $conn->prepare($query_classes);
$stmt_classes->bind_param("i", $student_id);
$stmt_classes->execute();
$result_classes = $stmt_classes->get_result();

// Fetch attendance records for the selected class
$class_attendance = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id'])) {
    $class_id = $_POST['class_id'];

    $query_attendance = "SELECT date, status FROM class_attendance WHERE class_id = ? AND student_id = ?";
    $stmt_attendance = $conn->prepare($query_attendance);
    $stmt_attendance->bind_param("ii", $class_id, $student_id);
    $stmt_attendance->execute();
    $result_attendance = $stmt_attendance->get_result();

    while ($row = $result_attendance->fetch_assoc()) {
        $class_attendance[] = $row;
    }

    $stmt_attendance->close();
}

$stmt_classes->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Attendance</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Page Title -->
            <h1>Class Attendance</h1>
            <!-- Back Button -->
            <a href="class_student_dashboard.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <!-- Class Selection Form -->
        <form method="POST" action="">
            <div class="mb-4">
                <label for="classDropdown" class="form-label">Select a Class:</label>
                <select id="classDropdown" name="class_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Select a class --</option>
                    <?php while ($row = $result_classes->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['id']); ?>" <?= isset($_POST['class_id']) && $_POST['class_id'] == $row['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['title']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>

        <!-- Attendance Table -->
        <?php if (!empty($class_attendance)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($class_attendance as $record): ?>
                            <tr>
                                <td><?= htmlspecialchars($record['date']); ?></td>
                                <td><?= htmlspecialchars($record['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id'])): ?>
            <div class="alert alert-warning text-center">
                No attendance records found for the selected class.
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
