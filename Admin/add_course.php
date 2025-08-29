<?php
session_start();
// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection
include('../db_connect.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_course'])) {
        $course_name = $_POST['course_name'];
        $course_details = $_POST['course_details'];
        $price_range = $_POST['price_range'];
        $duration = $_POST['duration'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        // Insert course into the database
        $stmt = $conn->prepare("INSERT INTO courses (course_name, course_details, price_range, duration, start_date, end_date, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssss', $course_name, $course_details, $price_range, $duration, $start_date, $end_date, $start_time, $end_time);

        if ($stmt->execute()) {
            echo "Course added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } elseif (isset($_POST['update_course'])) {
        $course_id = $_POST['course_id'];
        $course_name = $_POST['course_name'];
        $course_details = $_POST['course_details'];
        $price_range = $_POST['price_range'];
        $duration = $_POST['duration'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        // Update the course details in the database
        $stmt = $conn->prepare("UPDATE courses SET course_name = ?, course_details = ?, price_range = ?, duration = ?, start_date = ?, end_date = ?, start_time = ?, end_time = ? WHERE id = ?");
        $stmt->bind_param('ssssssssi', $course_name, $course_details, $price_range, $duration, $start_date, $end_date, $start_time, $end_time, $course_id);

        if ($stmt->execute()) {
            echo "<script>alert('Course updated successfully!'); window.location.href='add_course.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } elseif (isset($_POST['delete_course'])) {
        $course_id = $_POST['course_id'];

        // Delete the course from the database
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param('i', $course_id);

        if ($stmt->execute()) {
            echo "<script>alert('Course deleted successfully!'); window.location.href='add_course.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Fetch all courses for display
$result = $conn->query("SELECT * FROM courses");
$courses = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Custom color palette */
        :root {
            --primary-blue: #1E3A8A; /* Deep blue for trust */
            --secondary-teal: #2DD4BF; /* Teal for engagement */
            --accent-green: #10B981; /* Green for growth */
            --neutral-gray: #F7FAFC; /* Soft gray for background */
            --text-dark: #1A202C; /* Dark text for contrast */
            --shadow-teal: rgba(45, 212, 191, 0.25); /* Teal shadow for depth */
        }
        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        /* Card styling */
        .card {
            border: none;
            border-radius: 12px;
            background: var(--neutral-gray);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px var(--shadow-teal);
        }
        /* Form input styling */
        .form-control {
            border-radius: 8px;
            border: 1px solid #D1D5DB;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            border-color: var(--secondary-teal);
            box-shadow: 0 0 0 0.2rem var(--shadow-teal);
        }
        /* Button styling */
        .btn-primary {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-teal));
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(to right, var(--secondary-teal), var(--primary-blue));
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--shadow-teal);
        }
        .btn-secondary {
            background: var(--primary-blue);
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background: #1E40AF;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--shadow-teal);
        }
        .btn-danger {
            background: #DC3545;
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-danger:hover {
            background: #B91C1C;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.25);
        }
        /* Modal styling */
        .modal-content {
            border-radius: 12px;
            background: var(--neutral-gray);
            box-shadow: 0 8px 16px var(--shadow-teal);
        }
        .modal-header, .modal-footer {
            border: none;
        }
        /* Typography */
        h1, h2, .card-title {
            color: var(--text-dark);
        }
        .card-text {
            color: #4B5563;
        }
        .form-label {
            color: var(--text-dark);
            font-weight: 500;
        }
        /* Search input */
        .form-control.search-input {
            border-radius: 8px;
        }
        /* Container */
        body {
            background: #F3F4F6;
        }
        .container {
            max-width: 1200px;
        }
        /* Sidebar styling */
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: var(--secondary-teal) var(--neutral-gray);
        }
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: var(--neutral-gray);
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--secondary-teal);
            border-radius: 4px;
        }
        .dropdown-content {
            transition: max-height 0.3s ease, opacity 0.3s ease;
        }
        .dropdown-content.max-h-0 {
            max-height: 0;
            opacity: 0;
        }
        .dropdown-content.max-h-96 {
            max-height: 24rem;
            opacity: 1;
        }
        .nav-link {
            transition: all 0.2s ease-in-out;
        }
        .nav-link:hover {
            transform: translateX(5px) scale(1.02);
            background-color: var(--shadow-teal);
            color: var(--primary-blue);
        }
        .dropdown-toggle i {
            transition: transform 0.3s ease;
        }
        .dropdown-toggle.active i {
            transform: rotate(90deg);
        }
        .logout-link:hover {
            background-color: #DC3545;
            color: white;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .sidebar {
            animation: slideIn 0.5s ease-out;
        }
        .main-content {
            margin-left: 16rem;
            transition: margin-left 0.3s ease;
        }
        #sidebar-toggle {
            display: none;
        }
        #sidebar-toggle:checked ~ .sidebar {
            transform: translateX(0);
        }
        #sidebar-toggle:checked ~ .main-content {
            margin-left: 0;
        }
        #sidebar-toggle:checked ~ .overlay {
            display: block;
        }
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10;
        }
        @media (max-width: 767px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
            .mobile-toggle {
                display: block;
            }
        }
    </style>
</head>
<body class="min-h-screen flex">
    <!-- Mobile Sidebar Toggle -->
    <input type="checkbox" id="sidebar-toggle" class="hidden">
    <label for="sidebar-toggle" class="mobile-toggle md:hidden p-4 bg-[var(--primary-blue)] text-white flex justify-between items-center cursor-pointer">
        <span class="text-xl font-semibold">Manage Courses</span>
        <i class="fas fa-bars text-xl"></i>
    </label>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Overlay for mobile -->
    <div class="overlay"></div>

    <!-- Main Content -->
    <div class="main-content mt-5 mb-5">
        <div class="container">
            <h1 class="mb-4 text-center fw-bold">Manage Courses</h1>

            <!-- Add Course Form -->
            <div class="card shadow p-4 mb-5 fade-in">
                <h2 class="card-title mb-3 fw-semibold">Add Course</h2>
                <form action="add_course.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="course_name" class="form-label">Course Name</label>
                            <input type="text" name="course_name" id="course_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="price_range" class="form-label">Price Range</label>
                            <input type="text" name="price_range" id="price_range" class="form-control" placeholder="e.g., $100 - $500">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="duration" class="form-label">Course Duration</label>
                            <input type="text" name="duration" id="duration" class="form-control" placeholder="e.g., 3 months, 6 weeks" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" name="start_time" id="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" name="end_time" id="end_time" class="form-control" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="course_details" class="form-label">Course Details</label>
                            <textarea name="course_details" id="course_details" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <button type="submit" name="add_course" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i> Add Course
                    </button>
                </form>
            </div>

            <!-- Courses Section -->
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <h2 class="fw-semibold">Courses</h2>
                <input type="text" class="form-control w-25 search-input" placeholder="Search by name" id="searchInput" onkeyup="filterCourses()">
            </div>
            <div class="row" id="coursesContainer">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4 mb-4 course-card">
                        <div class="card shadow fade-in">
                            <div class="card-body">
                                <h5 class="card-title fw-semibold"><?= htmlspecialchars($course['course_name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($course['course_details']) ?></p>
                                <p><strong>Price:</strong> <?= htmlspecialchars($course['price_range']) ?></p>
                                <p><strong>Duration:</strong> <?= htmlspecialchars($course['duration']) ?></p>
                                <p><strong>Start:</strong> <?= htmlspecialchars($course['start_date']) ?> <?= htmlspecialchars($course['start_time']) ?></p>
                                <p><strong>End:</strong> <?= htmlspecialchars($course['end_date']) ?> <?= htmlspecialchars($course['end_time']) ?></p>
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-secondary me-2" data-bs-toggle="modal" data-bs-target="#updateModal" 
                                        onclick="populateModal(<?= htmlspecialchars(json_encode($course)) ?>)">
                                        <i class="fas fa-edit me-2"></i> Update
                                    </button>
                                    <form action="add_course.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                        <input type="hidden" name="course_id" value="<?= htmlspecialchars($course['id']) ?>">
                                        <button type="submit" name="delete_course" class="btn btn-danger">
                                            <i class="fas fa-trash me-2"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Update Modal -->
        <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-semibold" id="updateModalLabel">Update Course</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="add_course.php" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="course_id" id="updateCourseId">
                            <div class="mb-3">
                                <label for="updateCourseName" class="form-label">Course Name</label>
                                <input type="text" name="course_name" id="updateCourseName" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="updatePriceRange" class="form-label">Price Range</label>
                                <input type="text" name="price_range" id="updatePriceRange" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="updateDuration" class="form-label">Duration</label>
                                <input type="text" name="duration" id="updateDuration" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="updateStartDate" class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="updateStartDate" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="updateEndDate" class="form-label">End Date</label>
                                <input type="date" name="end_date" id="updateEndDate" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="updateStartTime" class="form-label">Start Time</label>
                                <input type="time" name="start_time" id="updateStartTime" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="updateEndTime" class="form-label">End Time</label>
                                <input type="time" name="end_time" id="updateEndTime" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="updateCourseDetails" class="form-label">Course Details</label>
                                <textarea name="course_details" id="updateCourseDetails" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="update_course" class="btn btn-primary">Save Changes</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function populateModal(course) {
            document.getElementById('updateCourseId').value = course.id;
            document.getElementById('updateCourseName').value = course.course_name;
            document.getElementById('updatePriceRange').value = course.price_range;
            document.getElementById('updateDuration').value = course.duration;
            document.getElementById('updateStartDate').value = course.start_date;
            document.getElementById('updateEndDate').value = course.end_date;
            document.getElementById('updateStartTime').value = course.start_time;
            document.getElementById('updateEndTime').value = course.end_time;
            document.getElementById('updateCourseDetails').value = course.course_details;
        }

        function filterCourses() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const courses = document.querySelectorAll('.course-card');

            courses.forEach(card => {
                const courseName = card.querySelector('.card-title').textContent.toLowerCase();
                card.style.display = courseName.includes(input) ? 'block' : 'none';
            });
        }
    </script>
</body>
</html>
