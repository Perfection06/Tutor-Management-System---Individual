<?php
session_start();
include '../db_connect.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$upload_error = "";

// Handle material upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course'] ?? null;
    $title = $_POST['title'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    if ($course_id && $title && $end_date && isset($_FILES['material'])) {
        $file = $_FILES['material'];
        $file_name = $file['name'];
        $file_size = $file['size'];
        $file_tmp = $file['tmp_name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $allowed_exts = ['pdf', 'doc', 'docx', 'zip', 'rar'];

        if (in_array(strtolower($file_ext), $allowed_exts) && $file_size <= 104857600) { // 100MB limit
            $upload_dir = '../uploads/course_materials/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_file_name = uniqid() . "_" . basename($file_name);
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                $stmt = $conn->prepare("INSERT INTO course_materials (course_id, title, file_path, end_date) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $course_id, $title, $new_file_name, $end_date);
                $stmt->execute();
                $stmt->close();
            } else {
                $upload_error = "Failed to upload the material.";
            }
        } else {
            $upload_error = "Invalid file type or size exceeds 100MB.";
        }
    } else {
        $upload_error = "All fields are required.";
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Retrieve file path
    $stmt = $conn->prepare("SELECT file_path FROM course_materials WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->bind_result($file_path);
    $stmt->fetch();
    $stmt->close();

    if ($file_path) {
        // Delete the file from the server
        $file_full_path = '../uploads/course_materials/' . $file_path;
        if (file_exists($file_full_path)) {
            unlink($file_full_path);
        }

        // Delete the record from the database
        $stmt = $conn->prepare("DELETE FROM course_materials WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect to avoid duplicate deletion requests
    header("Location: course_materials.php");
    exit();
}

// Delete expired materials
$conn->query("DELETE FROM course_materials WHERE end_date < CURDATE()");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Material Upload</title>
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
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        select, input, button {
            width: 100%;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .error {
            color: red;
            text-align: center;
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
        .delete-button {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Upload Course Material</h1>
    <?php if ($upload_error): ?>
        <p class="error"><?php echo htmlspecialchars($upload_error); ?></p>
    <?php endif; ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="course">Select Course:</label>
        <select id="course" name="course" required>
            <option value="">-- Select a course --</option>
            <?php
            $courses = $conn->query("SELECT id, course_name FROM courses");
            while ($course = $courses->fetch_assoc()) {
                echo "<option value='" . $course['id'] . "'>" . htmlspecialchars($course['course_name']) . "</option>";
            }
            ?>
        </select>

        <label for="title">Material Title:</label>
        <input type="text" id="title" name="title" required>

        <label for="material">Upload Material:</label>
        <input type="file" id="material" name="material" accept=".pdf,.doc,.docx,.zip,.rar" required>

        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>

        <button type="submit">Upload</button>
    </form>

    <h2>Uploaded Materials</h2>
    <table>
        <thead>
        <tr>
            <th>Title</th>
            <th>File</th>
            <th>Course</th>
            <th>End Date</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $materials = $conn->query("
            SELECT cm.id, cm.title, cm.file_path, cm.end_date, c.course_name 
            FROM course_materials cm
            JOIN courses c ON cm.course_id = c.id
        ");
        while ($material = $materials->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($material['title']) . "</td>";
            echo "<td><a href='../uploads/course_materials/" . htmlspecialchars($material['file_path']) . "' target='_blank'>View</a></td>";
            echo "<td>" . htmlspecialchars($material['course_name']) . "</td>";
            echo "<td>" . htmlspecialchars($material['end_date']) . "</td>";
            echo "<td><a href='?delete_id=" . $material['id'] . "' class='delete-button' onclick='return confirm(\"Are you sure?\");'>Delete</a></td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>
