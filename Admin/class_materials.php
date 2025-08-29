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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_material'])) {
    $grade_id = $_POST['grade'] ?? null;
    $class_id = $_POST['class'] ?? null;
    $title = $_POST['title'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    if ($grade_id && $class_id && $title && $end_date && isset($_FILES['material'])) {
        $file = $_FILES['material'];
        $file_name = $file['name'];
        $file_size = $file['size'];
        $file_tmp = $file['tmp_name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $allowed_exts = ['pdf', 'doc', 'docx', 'zip', 'rar'];

        if (in_array(strtolower($file_ext), $allowed_exts) && $file_size <= 104857600) { // 100MB limit
            $upload_dir = '../uploads/materials/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_file_name = uniqid() . "_" . basename($file_name);
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                $stmt = $conn->prepare("INSERT INTO class_materials (class_id, title, file_path, end_date) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $class_id, $title, $new_file_name, $end_date);
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

// Handle material deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_material'])) {
    $material_id = intval($_POST['material_id']);
    
    // Fetch the file path before deletion
    $stmt = $conn->prepare("SELECT file_path FROM class_materials WHERE id = ?");
    $stmt->bind_param("i", $material_id);
    $stmt->execute();
    $stmt->bind_result($file_path);
    $stmt->fetch();
    $stmt->close();

    if ($file_path) {
        // Delete the file from the server
        $file_to_delete = '../uploads/materials/' . $file_path;
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }

        // Delete the record from the database
        $stmt = $conn->prepare("DELETE FROM class_materials WHERE id = ?");
        $stmt->bind_param("i", $material_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Delete expired materials
$conn->query("DELETE FROM class_materials WHERE end_date < CURDATE()");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Material Upload</title>
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
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f9;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Upload Class Material</h1>
    <?php if ($upload_error): ?>
        <p class="error"><?php echo htmlspecialchars($upload_error); ?></p>
    <?php endif; ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="grade">Select Grade:</label>
        <select id="grade" name="grade" required>
            <option value="">-- Select a grade --</option>
            <?php
            $grades = $conn->query("SELECT id, grade_name FROM grades");
            while ($grade = $grades->fetch_assoc()) {
                echo "<option value='" . $grade['id'] . "'>" . htmlspecialchars($grade['grade_name']) . "</option>";
            }
            ?>
        </select>

        <label for="class">Select Class:</label>
        <select id="class" name="class" required>
            <option value="">-- Select a class --</option>
        </select>

        <label for="title">Material Title:</label>
        <input type="text" id="title" name="title" required>

        <label for="material">Upload Material:</label>
        <input type="file" id="material" name="material" accept=".pdf,.doc,.docx,.zip,.rar" required>

        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>

        <button type="submit" name="upload_material">Upload</button>
    </form>

    <h2>Uploaded Materials</h2>
    <table>
        <thead>
        <tr>
            <th>Title</th>
            <th>Class</th>
            <th>File</th>
            <th>End Date</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $materials = $conn->query("SELECT cm.id, cm.title, cm.file_path, cm.end_date, c.title AS class_title FROM class_materials cm JOIN classes c ON cm.class_id = c.id");
        while ($material = $materials->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($material['title']); ?></td>
                <td><?php echo htmlspecialchars($material['class_title']); ?></td>
                <td><a href="../uploads/materials/<?php echo htmlspecialchars($material['file_path']); ?>" target="_blank">Download</a></td>
                <td><?php echo htmlspecialchars($material['end_date']); ?></td>
                <td>
                    <form action="" method="POST" style="display:inline;">
                        <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                        <button type="submit" name="delete_material">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    document.getElementById('grade').addEventListener('change', function() {
        var grade_id = this.value;
        var classSelect = document.getElementById('class');

        if (grade_id) {
            // Send AJAX request to fetch classes for the selected grade
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'fetch_classes.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var classes = JSON.parse(xhr.responseText);
                    // Clear previous options
                    classSelect.innerHTML = '<option value="">-- Select a class --</option>';
                    // Add new class options
                    classes.forEach(function(classItem) {
                        var option = document.createElement('option');
                        option.value = classItem.id;
                        option.textContent = classItem.title + ' (' + classItem.subject_name + ')';
                        classSelect.appendChild(option);
                    });
                }
            };
            xhr.send('grade_id=' + grade_id);
        } else {
            // Reset class dropdown if no grade is selected
            classSelect.innerHTML = '<option value="">-- Select a class --</option>';
        }
    });
</script>
</body>
</html>
