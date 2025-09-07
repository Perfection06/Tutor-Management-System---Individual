<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'course') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Course Student Dashboard - Tutor Management System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-12px); }
    }
    .animate-float { animation: float 6s ease-in-out infinite; }
  </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen flex flex-col relative">

  <!-- Background Floating Icons -->
  <div class="absolute inset-0 pointer-events-none overflow-hidden">
    <i data-lucide="book-open" class="absolute text-blue-300 opacity-10 w-24 h-24 top-10 left-10 animate-float"></i>
    <i data-lucide="graduation-cap" class="absolute text-blue-400 opacity-10 w-32 h-32 bottom-20 right-20 animate-float delay-1000"></i>
    <i data-lucide="pen-tool" class="absolute text-blue-500 opacity-10 w-28 h-28 top-1/3 left-1/4 animate-float delay-2000"></i>
    <i data-lucide="award" class="absolute text-blue-600 opacity-10 w-20 h-20 bottom-1/4 right-1/3 animate-float delay-3000"></i>
    <i data-lucide="school" class="absolute text-blue-300 opacity-10 w-28 h-28 top-20 right-1/4 animate-float delay-4000"></i>
  </div>

  <!-- Header -->
  <header class="bg-gradient-to-r from-blue-800 to-teal-600 text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
      <h1 class="text-2xl font-bold flex items-center space-x-2">
        <i data-lucide="layout-dashboard" class="w-7 h-7"></i>
        <span>Course Student Dashboard</span>
      </h1>
      <div class="flex items-center space-x-4">
        <a href="profile.php" 
           class="flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white px-5 py-2 rounded-lg transition-all duration-300 shadow-md">
          <i data-lucide="user" class="w-5 h-5"></i>
          <span>Profile</span>
        </a>
        <a href="../login.php" 
           class="flex items-center space-x-2 bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-lg transition-all duration-300 shadow-md">
          <i data-lucide="log-out" class="w-5 h-5"></i>
          <span>Logout</span>
        </a>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="flex-1 w-full max-w-7xl mx-auto px-6 py-12 z-10">

    <!-- Welcome Message -->
    <div class="text-center bg-gradient-to-r from-blue-800 to-teal-500 text-white p-10 rounded-2xl shadow-xl mb-12 transform hover:scale-[1.02] hover:shadow-2xl transition-all duration-500">
      <h2 class="text-4xl font-extrabold drop-shadow-lg">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>
      <p class="text-lg mt-3">Access all your student portal features below.</p>
    </div>

    <!-- Dashboard Widgets -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8 place-items-center">
      <?php
      $links = [
        ["./attendance.php", "calendar", "Attendance"],
        ["./payments.php", "credit-card", "Payments"],
        ["./course_materials.php", "book", "Materials"],
        ["./message.php", "message-square", "Messages"],
        ["./admin_freetime.php", "clock", "Teacher Availability"],
        ["./programms.php", "list", "Available Programs"],
      ];

      foreach ($links as $link) {
        echo "
        <a href='{$link[0]}' class='group bg-white p-8 rounded-2xl shadow-md flex flex-col items-center justify-center w-48 h-48 
            transform transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:bg-gradient-to-br hover:from-teal-50 hover:to-blue-50'>
          <i data-lucide='{$link[1]}' class='w-12 h-12 text-teal-600 mb-3 transition-transform duration-500 group-hover:scale-125'></i>
          <span class='text-lg font-bold text-gray-700 text-center group-hover:text-teal-700'>{$link[2]}</span>
        </a>
        ";
      }
      ?>
    </div>
  </main>

  <!-- Lucide Icons -->
  <script>
    lucide.createIcons();
  </script>
</body>
</html>
