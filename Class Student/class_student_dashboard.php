<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'class') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }
        .sidebar {
            width: 200px;
            background-color: #f4f4f4;
            position: fixed;
            height: 100%;
            padding: 20px 10px;
        }
        .sidebar a {
            display: block;
            color: #333;
            text-decoration: none;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 4px;
        }
        .sidebar a:hover {
            background-color: #ddd;
        }
        .main {
            margin-left: 220px;
            padding: 20px;
        }
        .card {
            background-color: #fff;
            padding: 20px;
            margin: 10px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <span>Class Student Dashboard</span>
        <div>
            <a href="profile.php">Profile</a>
            <a href="../login.php">Logout</a>
        </div>
    </div>
    <div class="sidebar">
        <a href="./class_student_dashboard.php">Home</a>
        <a href="./attendance.php">Attendance</a>
        <a href="#">Payments</a>
        <a href="./class_materials.php">Materials</a>
        <a href="./message.html">Messages</a>
        <a href="./admin_freetime.php">Teacher Available Days</a>
        <a href="./programms.html">Available Programms</a>
    </div>  
</body>
</html>
