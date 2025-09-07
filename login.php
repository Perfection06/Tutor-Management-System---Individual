<?php
session_start();
include 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the user is an admin
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        // Verify admin password
        if (password_verify($password, $admin['password'])) {

            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = 'admin';
            header("Location: ./Admin/admin_dashboard.php"); 
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        // Check if the user is a student
        $stmt = $conn->prepare("SELECT * FROM Student WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $student = $result->fetch_assoc();

            // Verify student password
            if (password_verify($password, $student['password'])) {

                $_SESSION['user_id'] = $student['id'];
                $_SESSION['username'] = $student['username'];
                $_SESSION['role'] = $student['program']; 

                if ($student['program'] === 'class') {
                    header("Location: ./Class Student/class_student_dashboard.php"); 
                } else {
                    header("Location: ./Course Student/course_student_dashboard.php"); 
                }
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found.";
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tutor Management System</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Floating animation for background icons */
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        /* Different delays for varied animation */
        .float-delay-1 { animation-delay: 0s; }
        .float-delay-2 { animation-delay: 1s; }
        .float-delay-3 { animation-delay: 2s; }
        .float-delay-4 { animation-delay: 3s; }
        .float-delay-5 { animation-delay: 4s; }
        .float-delay-6 { animation-delay: 5s; }
        .float-delay-7 { animation-delay: 1.5s; }
        .float-delay-8 { animation-delay: 2.5s; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center overflow-hidden relative">

    <!-- Background Floating Icons -->
    <div class="absolute inset-0 pointer-events-none">
        <i data-lucide="book-open" class="absolute text-blue-300 opacity-20 w-16 h-16 top-10 left-10 float-animation float-delay-1"></i>
        <i data-lucide="graduation-cap" class="absolute text-blue-400 opacity-20 w-20 h-20 bottom-20 right-20 float-animation float-delay-2"></i>
        <i data-lucide="pen-tool" class="absolute text-blue-500 opacity-20 w-24 h-24 top-1/3 left-1/4 float-animation float-delay-3"></i>
        <i data-lucide="award" class="absolute text-blue-600 opacity-20 w-18 h-18 bottom-1/4 right-1/3 float-animation float-delay-4"></i>
        <i data-lucide="school" class="absolute text-blue-300 opacity-20 w-20 h-20 top-20 right-1/4 float-animation float-delay-5"></i>
        <i data-lucide="book-marked" class="absolute text-blue-400 opacity-20 w-18 h-18 bottom-10 left-1/3 float-animation float-delay-6"></i>
        <i data-lucide="chalkboard" class="absolute text-blue-500 opacity-20 w-22 h-22 top-1/2 left-1/2 float-animation float-delay-7"></i>
        <i data-lucide="lightbulb" class="absolute text-blue-600 opacity-20 w-16 h-16 bottom-1/3 left-1/5 float-animation float-delay-8"></i>
    </div>

    <!-- Login Card -->
    <div class="relative bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md z-10">
        <h2 class="text-3xl font-bold text-center mb-6 text-gray-800">Tutor Management System</h2>

        <!-- Display error message if exists -->
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <div class="relative">
                    <i data-lucide="user" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="username" name="username" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <i data-lucide="lock" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" id="password" name="password" class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <i id="togglePassword" data-lucide="eye" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 cursor-pointer"></i>
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-200">Login</button>
        </form>
    </div>

    <!-- Initialize Lucide Icons and Password Toggle Functionality -->
    <script>
        lucide.createIcons();

        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', () => {
            const isPasswordVisible = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPasswordVisible ? 'text' : 'password');
            
            // Remove the previous icon and create a new one
            togglePassword.setAttribute('data-lucide', isPasswordVisible ? 'eye-off' : 'eye');
            lucide.createIcons({ icons: { 'togglePassword': togglePassword } });
        });
    </script>
</body>
</html>