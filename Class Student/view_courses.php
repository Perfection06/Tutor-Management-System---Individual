<?php
include('../db_connect.php');
session_start();

// Ensure the user is authenticated and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'class') {
    header("Location: login.php");
    exit();
}

// Fetch all courses
$courses_query = "SELECT * FROM courses";
$courses_result = $conn->query($courses_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Courses</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4 text-center">View Courses</h1>

        <!-- Display Courses as Cards -->
        <?php if ($courses_result->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($course = $courses_result->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($course['course_name']) ?></h5>
                                <p class="card-text">
                                    <strong>Price:</strong> <?= htmlspecialchars($course['price_range']) ?><br>
                                    <strong>Duration:</strong> <?= htmlspecialchars($course['duration']) ?>
                                </p>
                                <button class="btn btn-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#courseModal<?= $course['id'] ?>">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for Course Details -->
                    <div class="modal fade" id="courseModal<?= $course['id'] ?>" tabindex="-1" aria-labelledby="courseModalLabel<?= $course['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="courseModalLabel<?= $course['id'] ?>">
                                        <?= htmlspecialchars($course['course_name']) ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Details:</strong> <?= nl2br(htmlspecialchars($course['course_details'])) ?></p>
                                    <p><strong>Price Range:</strong> <?= htmlspecialchars($course['price_range']) ?></p>
                                    <p><strong>Duration:</strong> <?= htmlspecialchars($course['duration']) ?></p>
                                    <p><strong>Start Date:</strong> <?= htmlspecialchars(date("F j, Y", strtotime($course['start_date']))) ?></p>
                                    <p><strong>End Date:</strong> <?= htmlspecialchars(date("F j, Y", strtotime($course['end_date']))) ?></p>
                                    <p><strong>Time:</strong> 
                                        <?= htmlspecialchars(date("g:i A", strtotime($course['start_time']))) ?> - 
                                        <?= htmlspecialchars(date("g:i A", strtotime($course['end_time']))) ?>
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="alert alert-warning">No courses available.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
