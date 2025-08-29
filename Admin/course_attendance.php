<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Handle AJAX request for students
if (isset($_GET['action']) && $_GET['action'] === 'fetch_students' && isset($_GET['course_id'])) {
    header('Content-Type: application/json');
    $course_id = intval($_GET['course_id']);
    $date = date('Y-m-d');
    $students = [];
    if ($course_id) {
        $query = $conn->prepare("
            SELECT s.id, s.name, s.username, ca.status
            FROM student_courses sc
            JOIN Student s ON sc.student_id = s.id
            LEFT JOIN course_attendance ca ON s.id = ca.student_id AND ca.course_id = ? AND ca.date = ?
            WHERE sc.course_id = ?
        ");
        $query->bind_param("isi", $course_id, $date, $course_id);
        $query->execute();
        $students = $query->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    echo json_encode($students);
    exit;
}

// Fetch courses
$courses = $conn->query("SELECT id, course_name FROM courses");

// Handle attendance submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance']) && isset($_POST['course_id'])) {
    $course_id = intval($_POST['course_id']);
    $attendance = $_POST['attendance'];
    $date = date('Y-m-d');

    foreach ($attendance as $student_id => $status) {
        if ($status === "") continue; // Skip if "Select" is chosen
        $student_id = intval($student_id);

        // Check if attendance exists
        $check = $conn->prepare("
            SELECT id FROM course_attendance 
            WHERE course_id = ? AND student_id = ? AND date = ?
        ");
        $check->bind_param("iis", $course_id, $student_id, $date);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            // Update existing attendance
            $update = $conn->prepare("
                UPDATE course_attendance 
                SET status = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE course_id = ? AND student_id = ? AND date = ?
            ");
            $update->bind_param("siis", $status, $course_id, $student_id, $date);
            $update->execute();
        } else {
            // Insert new attendance
            $insert = $conn->prepare("
               glyc INSERT INTO course_attendance (course_id, student_id, date, status) 
                VALUES (?, ?, ?, ?)
            ");
            $insert->bind_param("iiss", $course_id, $student_id, $date, $status);
            $insert->execute();
        }
    }
    $message = "Attendance marked successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <style>
        :root {
            --primary-blue: #1E3A8A;
            --secondary-teal: #2DD4BF;
            --accent-green: #10B981;
            --neutral-gray: #F7FAFC;
            --text-dark: #1A202C;
            --shadow-teal: rgba(45, 212, 191, 0.25);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: word-wrap(0); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px var(--shadow-teal);
        }
        .table-row:hover {
            background: #E5E7EB;
        }
        .btn-gradient {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-teal));
        }
        .btn-gradient:hover {
            background: linear-gradient(to right, var(--secondary-teal), var(--primary-blue));
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--shadow-teal);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{
    selectedCourse: null,
    students: [],
    async fetchStudents(courseId) {
        const response = await fetch('?action=fetch_students&course_id=' + courseId);
        this.students = await response.json();
    }
}">
    <div class="container max-w-4xl mx-auto p-6">
        <a href="attendance.html" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 hover:shadow-lg transition-all duration-200">
            <i class="fas fa-arrow-left mr-2"></i> Back to Attendance Menu
        </a>

        <!-- Success Message -->
        <?php if ($message): ?>
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
                 x-transition:enter="fade-in" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mt-4 rounded-md">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Course Selection -->
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Select Course</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php while ($row = $courses->fetch_assoc()): ?>
                    <div class="card-hover bg-white p-4 rounded-lg shadow-md cursor-pointer fade-in"
                         :class="{ 'ring-2 ring-teal-500': selectedCourse == <?= $row['id'] ?> }"
                         @click="selectedCourse = <?= $row['id'] ?>; fetchStudents(<?= $row['id'] ?>)">
                        <h3 class="text-gray-700 font-medium"><?= htmlspecialchars($row['course_name']) ?></h3>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Students Table -->
        <div x-show="selectedCourse" class="mt-6">
            <form method="POST" action="" id="attendanceForm">
                <input type="hidden" name="course_id" x-bind:value="selectedCourse">
                <div class="overflow-x-auto bg-white rounded-lg shadow-md p-6">
                    <table class="w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-800 text-white">
                                <th class="px-6 py-3 text-left">Student Name</th>
                                <th class="px-6 py-3 text-left">Username</th>
                                <th class="px-6 py-3 text-left">Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="student in students" :key="student.id">
                                <tr class="table-row border-b transition-colors duration-200">
                                    <td class="px-6 py-4" x-text="student.name"></td>
                                    <td class="px-6 py-4" x-text="student.username"></td>
                                    <td class="px-6 py-4">
                                        <select :name="'attendance[' + student.id + ']'" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 transition-all duration-200">
                                            <option value="" x-bind:selected="student.status == null">Select</option>
                                            <option value="present" x-bind:selected="student.status == 'present'">Present</option>
                                            <option value="absent" x-bind:selected="student.status == 'absent'">Absent</option>
                                            <option value="late" x-bind:selected="student.status == 'late'">Late</option>
                                        </select>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <button type="submit" class="mt-4 w-full px-4 py-2 btn-gradient text-white rounded-lg hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-check mr-2"></i> Submit Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>