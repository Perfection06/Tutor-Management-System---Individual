<?php
include('../db_connect.php');
session_start();

// Ensure the user is authenticated and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'class') {
    header("Location: login.php");
    exit();
}

// Fetch all grades
$grades_query = "SELECT * FROM grades";
$grades_result = $conn->query($grades_query);

// Fetch classes for the selected grade
$selected_grade = isset($_GET['grade_id']) ? intval($_GET['grade_id']) : null;
$classes = [];
if ($selected_grade) {
    $classes_query = "SELECT c.id, c.title, c.fee, g.grade_name 
                      FROM classes c 
                      JOIN grades g ON c.grade = g.id 
                      WHERE c.grade = ?";
    $stmt = $conn->prepare($classes_query);
    $stmt->bind_param("i", $selected_grade);
    $stmt->execute();
    $classes = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Classes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4 text-center">View Classes</h1>

        <!-- Grade Selection Dropdown -->
        <form method="GET" class="mb-4">
            <div class="mb-3">
                <label for="grade" class="form-label">Select Grade:</label>
                <select name="grade_id" id="grade" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Select Grade --</option>
                    <?php while ($grade = $grades_result->fetch_assoc()): ?>
                        <option value="<?= $grade['id'] ?>" <?= $selected_grade == $grade['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($grade['grade_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>

        <!-- Display Classes as Cards -->
        <?php if ($selected_grade && $classes->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($class = $classes->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($class['title']) ?></h5>
                                <p class="card-text"><strong>Fee:</strong> Rs.<?= htmlspecialchars($class['fee']) ?></p>
                                <button class="btn btn-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#classModal<?= $class['id'] ?>">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for Class Details -->
                <div class="modal fade" id="classModal<?= $class['id'] ?>" tabindex="-1" aria-labelledby="classModalLabel<?= $class['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="classModalLabel<?= $class['id'] ?>">
                                    <?= htmlspecialchars($class['title']) ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Grade:</strong> <?= htmlspecialchars($class['grade_name']) ?></p>
                                <p><strong>Fee:</strong> Rs.<?= htmlspecialchars($class['fee']) ?></p>
                                <h6>Class Schedule:</h6>
                                <ul>
                                    <?php
                                    // Fetch the schedule for this class
                                    $schedule_query = "SELECT day, start_time, end_time 
                                                    FROM class_days_times 
                                                    WHERE class_id = ?";
                                    $schedule_stmt = $conn->prepare($schedule_query);
                                    $schedule_stmt->bind_param("i", $class['id']);
                                    $schedule_stmt->execute();
                                    $schedules = $schedule_stmt->get_result();
                                    while ($schedule = $schedules->fetch_assoc()):
                                        // Convert times to AM/PM format
                                        $start_time = date("g:i A", strtotime($schedule['start_time']));
                                        $end_time = date("g:i A", strtotime($schedule['end_time']));
                                    ?>
                                        <li>
                                            <?= htmlspecialchars($schedule['day']) ?>: 
                                            <?= $start_time ?> - <?= $end_time ?>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php elseif ($selected_grade): ?>
            <p class="alert alert-warning">No classes found for the selected grade.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
