<?php
include '../db_connect.php';

// Fetch courses from the database
$query = "SELECT * FROM courses";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Courses</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .card-container { display: flex; flex-wrap: wrap; gap: 20px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center; width: 250px; box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); transition: transform 0.3s; }
        .card:hover { transform: scale(1.05); cursor: pointer; }
        .card h3 { margin: 10px 0; font-size: 20px; }
        .card p { margin: 5px 0; color: #555; }
        a { text-decoration: none; color: inherit; }
    </style>
</head>
<body>
    <h1>Available Courses</h1>
    <div class="card-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <a href="course_students.php?course_id=<?php echo $row['id']; ?>">
                <div class="card">
                    <h3><?php echo $row['course_name']; ?></h3>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
</body>
</html>
