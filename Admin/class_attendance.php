<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'fetch_classes' && isset($_GET['grade'])) {
        $grade_id = intval($_GET['grade']);
        $classes = [];
        if ($grade_id) {
            $query = $conn->prepare("SELECT id, title FROM classes WHERE grade = ?");
            $query->bind_param("i", $grade_id);
            $query->execute();
            $classes = $query->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        echo json_encode($classes);
        exit;
    }

    if ($_GET['action'] === 'fetch_students' && isset($_GET['class_id'])) {
        $class_id = intval($_GET['class_id']);
        $date = date('Y-m-d');
        $students = [];
        if ($class_id) {
            $query = $conn->prepare("
                SELECT s.id, s.name, s.username, ca.status
                FROM student_classes sc
                JOIN Student s ON sc.student_id = s.id
                LEFT JOIN class_attendance ca ON s.id = ca.student_id AND ca.class_id = ? AND ca.date = ?
                WHERE sc.class_id = ?
            ");
            $query->bind_param("isi", $class_id, $date, $class_id);
            $query->execute();
            $students = $query->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        echo json_encode($students);
        exit;
    }
}

// Fetch grades
$grades = $conn->query("SELECT id, grade_name FROM grades");

// Handle attendance submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance']) && isset($_POST['class_id'])) {
    $class_id = intval($_POST['class_id']);
    $attendance = $_POST['attendance'];
    $date = date('Y-m-d');

    foreach ($attendance as $student_id => $status) {
        if ($status === "") continue; // Skip if "Select" is chosen
        $student_id = intval($student_id);

        // Check if attendance exists
        $check = $conn->prepare("
            SELECT id FROM class_attendance 
            WHERE class_id = ? AND student_id = ? AND date = ?
        ");
        $check->bind_param("iis", $class_id, $student_id, $date);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            // Update existing attendance
            $update = $conn->prepare("
                UPDATE class_attendance 
                SET status = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE class_id = ? AND student_id = ? AND date = ?
            ");
            $update->bind_param("siis", $status, $class_id, $student_id, $date);
            $update->execute();
        } else {
            // Insert new attendance
            $insert = $conn->prepare("
                INSERT INTO class_attendance (class_id, student_id, date, status) 
                VALUES (?, ?, ?, ?)
            ");
            $insert->bind_param("iiss", $class_id, $student_id, $date, $status);
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
    <title>Class Attendance</title>
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
            to { opacity: 1; transform: translateY(0); }
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
    selectedGrade: null,
    selectedClass: null,
    classes: [],
    students: [],
    async fetchClasses(gradeId) {
        const response = await fetch('?action=fetch_classes&grade=' + gradeId);
        this.classes = await response.json();
    },
    async fetchStudents(classId) {
        const response = await fetch('?action=fetch_students&class_id=' + classId);
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

        <!-- Grade Selection -->
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Select Grade</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php while ($row = $grades->fetch_assoc()): ?>
                    <div class="card-hover bg-white p-4 rounded-lg shadow-md cursor-pointer fade-in"
                         :class="{ 'ring-2 ring-teal-500': selectedGrade == <?= $row['id'] ?> }"
                         @click="selectedGrade = <?= $row['id'] ?>; selectedClass = null; fetchClasses(<?= $row['id'] ?>)">
                        <h3 class="text-gray-700 font-medium"><?= htmlspecialchars($row['grade_name']) ?></h3>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Class Selection -->
        <div x-show="selectedGrade" class="mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Select Class</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <template x-for="cls in classes" :key="cls.id">
                    <div class="card-hover bg-white p-4 rounded-lg shadow-md cursor-pointer fade-in"
                         :class="{ 'ring-2 ring-teal-500': selectedClass == cls.id }"
                         @click="selectedClass = cls.id; fetchStudents(cls.id)">
                        <h3 class="text-gray-700 font-medium" x-text="cls.title"></h3>
                    </div>
                </template>
            </div>
        </div>

        <!-- Students Table -->
        <div x-show="selectedClass" class="mt-6">
            <form method="POST" action="" id="attendanceForm">
                <input type="hidden" name="class_id" x-bind:value="selectedClass">
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