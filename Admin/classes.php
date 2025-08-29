<?php
include '../db_connect.php';

// Get grade ID from the query string
$grade_id = isset($_GET['grade_id']) ? intval($_GET['grade_id']) : 0;

// Fetch classes for the selected grade
$query = "SELECT * FROM classes WHERE grade = $grade_id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .card-container { display: flex; flex-wrap: wrap; gap: 20px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center; width: 200px; box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); transition: transform 0.3s; }
        .card:hover { transform: scale(1.05); cursor: pointer; }
        .card h3 { margin: 10px 0; font-size: 18px; }
        a { text-decoration: none; color: inherit; }
    </style>
</head>
<body>
    <h1>Select a Class</h1>
    <div class="card-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <a href="class_students.php?class_id=<?php echo $row['id']; ?>">
                <div class="card">
                    <h3><?php echo $row['title']; ?></h3>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
</body>
</html>
