<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Fetch statistics
$total_students = $conn->query("SELECT COUNT(*) as count FROM Student")->fetch_assoc()['count'];
$total_courses = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];
$total_classes = $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'];
$recent_messages = $conn->query("
    SELECT m.message, m.created_at, s.username 
    FROM Message m 
    JOIN Student s ON m.username = s.username 
    ORDER BY m.created_at DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Fetch today's attendance summary
$date = date('Y-m-d');
$class_attendance = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM class_attendance 
    WHERE date = '$date' 
    GROUP BY status
")->fetch_all(MYSQLI_ASSOC);
$course_attendance = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM course_attendance 
    WHERE date = '$date' 
    GROUP BY status
")->fetch_all(MYSQLI_ASSOC);
$total_class_students = $conn->query("SELECT COUNT(*) as count FROM student_classes")->fetch_assoc()['count'];
$total_course_students = $conn->query("SELECT COUNT(*) as count FROM student_courses")->fetch_assoc()['count'];
$class_attendance_total = array_sum(array_column($class_attendance, 'count'));
$course_attendance_total = array_sum(array_column($course_attendance, 'count'));
$not_marked = ($total_class_students + $total_course_students) - ($class_attendance_total + $course_attendance_total);

$attendance_summary = ['present' => 0, 'absent' => 0, 'late' => 0];
foreach ($class_attendance as $att) {
    $attendance_summary[$att['status']] += $att['count'];
}
foreach ($course_attendance as $att) {
    $attendance_summary[$att['status']] += $att['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        .sidebar a:hover {
            background: #E5E7EB;
        }
        /* Sidebar and content layout */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 16rem; /* 256px */
            height: 100%;
            background: #F9FAFB;
            z-index: 20;
            transition: transform 0.3s ease;
        }
        .main-content {
            margin-left: 16rem; /* Match sidebar width */
            transition: margin-left 0.3s ease;
        }
        /* Mobile styles */
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
<body class="bg-gray-100 min-h-screen flex">
    <!-- Mobile Sidebar Toggle -->
    <input type="checkbox" id="sidebar-toggle" class="hidden">
    <label for="sidebar-toggle" class="mobile-toggle md:hidden p-4 bg-gray-800 text-white flex justify-between items-center cursor-pointer">
        <span class="text-xl font-semibold">Admin Dashboard</span>
        <i class="fas fa-bars text-xl"></i>
    </label>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Overlay for mobile -->
    <div class="overlay"></div>

    <!-- Main Content -->
    <div class="flex-1 main-content p-6">
        <div class="container max-w-6xl mx-auto">
            <!-- Welcome Message -->
            <div class="bg-gradient-to-r from-blue-800 to-teal-500 text-white p-8 rounded-lg shadow-lg mb-6 fade-in">
                <h1 class="text-4xl font-bold">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
                <p class="text-lg mt-2">Manage your educational platform with ease. Navigate using the sidebar.</p>
            </div>

            <!-- Statistics Widgets -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="card-hover bg-white p-6 rounded-lg shadow-md fade-in">
                    <h3 class="text-lg font-medium text-gray-700">Total Students</h3>
                    <p class="text-3xl font-bold text-teal-600"><?= $total_students ?></p>
                </div>
                <div class="card-hover bg-white p-6 rounded-lg shadow-md fade-in">
                    <h3 class="text-lg font-medium text-gray-700">Total Courses</h3>
                    <p class="text-3xl font-bold text-teal-600"><?= $total_courses ?></p>
                </div>
                <div class="card-hover bg-white p-6 rounded-lg shadow-md fade-in">
                    <h3 class="text-lg font-medium text-gray-700">Total Classes</h3>
                    <p class="text-3xl font-bold text-teal-600"><?= $total_classes ?></p>
                </div>
            </div>

            <!-- Today's Attendance Summary -->
            <div class="card-hover bg-white p-6 rounded-lg shadow-md mb-6 fade-in">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Today's Attendance Summary (<?= date('M d, Y') ?>)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="border-l-4 border-green-500 pl-4">
                        <h4 class="text-lg font-medium text-gray-600">Present</h4>
                        <p class="text-2xl font-bold text-teal-600"><?= $attendance_summary['present'] ?></p>
                    </div>
                    <div class="border-l-4 border-red-500 pl-4">
                        <h4 class="text-lg font-medium text-gray-600">Absent</h4>
                        <p class="text-2xl font-bold text-teal-600"><?= $attendance_summary['absent'] ?></p>
                    </div>
                    <div class="border-l-4 border-yellow-500 pl-4">
                        <h4 class="text-lg font-medium text-gray-600">Late</h4>
                        <p class="text-2xl font-bold text-teal-600"><?= $attendance_summary['late'] ?></p>
                    </div>
                    <div class="border-l-4 border-gray-500 pl-4">
                        <h4 class="text-lg font-medium text-gray-600">Not Marked</h4>
                        <p class="text-2xl font-bold text-teal-600"><?= $not_marked ?></p>
                    </div>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="card-hover bg-white p-6 rounded-lg shadow-md fade-in">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Recent Messages</h3>
                <?php if ($recent_messages): ?>
                    <ul class="space-y-3">
                        <?php foreach ($recent_messages as $msg): ?>
                            <li class="text-gray-600 border-b border-gray-200 pb-2">
                                <span class="font-medium"><?= htmlspecialchars($msg['username']) ?>:</span> 
                                <?= htmlspecialchars(substr($msg['message'], 0, 50)) . (strlen($msg['message']) > 50 ? '...' : '') ?>
                                <span class="text-sm text-gray-400">(@<?= date('M d, H:i', strtotime($msg['created_at'])) ?>)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500">No recent messages</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>