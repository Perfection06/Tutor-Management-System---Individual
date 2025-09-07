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
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(15px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .fade-in { animation: fadeIn 0.8s ease forwards; }
  </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen flex flex-col relative">

  <!-- Header -->
  <header class="bg-gradient-to-r from-blue-800 to-teal-600 text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
      <h1 class="text-2xl font-bold flex items-center space-x-2">
        <i data-lucide="calendar-check" class="w-7 h-7"></i>
        <span>Course Attendance</span>
      </h1>
      <a href="course_student_dashboard.php" 
         class="flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white px-5 py-2 rounded-lg transition-all duration-300 shadow-md">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
        <span>Back</span>
      </a>
    </div>
  </header>

  <!-- Main Content -->
  <main class="flex-1 max-w-6xl mx-auto px-6 py-10 w-full fade-in">

    <!-- Course Selector -->
    <div class="bg-white p-6 rounded-2xl shadow-lg mb-8">
      <form method="POST" class="flex flex-col md:flex-row md:items-center md:space-x-6 space-y-4 md:space-y-0">
        <label for="courseDropdown" class="text-lg font-semibold text-gray-700 flex items-center space-x-2">
          <i data-lucide="book-open" class="w-5 h-5 text-blue-600"></i>
          <span>Select a Course:</span>
        </label>
        <select name="course_id" id="courseDropdown" 
                class="flex-1 border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all cursor-pointer"
                onchange="this.form.submit()">
          <option value="">-- Select a course --</option>
          <?php while ($course = $courses_result->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($course['id']) ?>" <?= isset($_POST['course_id']) && $_POST['course_id'] == $course['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($course['course_name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </form>
    </div>

    <!-- Attendance Table -->
    <?php if (!empty($attendance)): ?>
      <div class="bg-white p-6 rounded-2xl shadow-lg overflow-x-auto fade-in">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gradient-to-r from-blue-600 to-teal-500 text-white">
              <th class="py-3 px-4 text-left">Date</th>
              <th class="py-3 px-4 text-left">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($attendance as $record): ?>
              <tr class="border-b hover:bg-gray-50 transition">
                <td class="py-3 px-4"><?= htmlspecialchars($record['date']) ?></td>
                <td class="py-3 px-4">
                  <?php if ($record['status'] === 'Present'): ?>
                    <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-700 font-medium">Present</span>
                  <?php elseif ($record['status'] === 'Absent'): ?>
                    <span class="px-3 py-1 rounded-full text-sm bg-red-100 text-red-700 font-medium">Absent</span>
                  <?php else: ?>
                    <span class="px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-700 font-medium"><?= htmlspecialchars($record['status']) ?></span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])): ?>
      <div class="bg-yellow-50 border-l-4 border-yellow-400 p-5 rounded-xl text-yellow-700 shadow-md fade-in">
        <p class="flex items-center space-x-2">
          <i data-lucide="info" class="w-5 h-5"></i>
          <span>No attendance records found for the selected course.</span>
        </p>
      </div>
    <?php endif; ?>
  </main>

  <!-- Lucide Icons -->
  <script>
    lucide.createIcons();
  </script>
</body>
</html>
