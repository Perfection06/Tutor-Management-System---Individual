<?php
session_start();
include '../db_connect.php';

// Check if the user is logged in and belongs to the 'class' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'class') {
    header("Location: login.php");
    exit();
}

// Get the logged-in student's ID
$student_id = $_SESSION['user_id'];

// Fetch class materials for the student's enrolled classes
$query = "
    SELECT 
        cm.title AS material_title,
        cm.file_path,
        cm.end_date,
        s.subject_name,
        c.title AS class_title
    FROM class_materials cm
    JOIN classes c ON cm.class_id = c.id
    JOIN subjects s ON c.subject = s.id
    JOIN student_classes sc ON c.id = sc.class_id
    WHERE sc.student_id = ?
    AND cm.end_date >= CURDATE() -- Show only materials that have not expired
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$materials = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Materials</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ccc;
        }
        th {
            background-color: #f4f4f4;
        }
        a {
            text-decoration: none;
            color: #007bff;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Class Materials</h1>
    <table>
        <thead>
        <tr>
            <th>Subject</th>
            <th>Class Title</th>
            <th>Material Title</th>
            <th>File</th>
            <th>End Date</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($materials)): ?>
            <?php foreach ($materials as $material): ?>
                <tr>
                    <td><?= htmlspecialchars($material['subject_name']) ?></td>
                    <td><?= htmlspecialchars($material['class_title']) ?></td>
                    <td><?= htmlspecialchars($material['material_title']) ?></td>
                    <td>
                        <a href="../uploads/materials/<?= htmlspecialchars($material['file_path']) ?>" target="_blank">View</a>
                    </td>
                    <td><?= htmlspecialchars($material['end_date']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">No materials available.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
