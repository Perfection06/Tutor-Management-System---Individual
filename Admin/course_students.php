<?php
include '../db_connect.php';

// Get course ID from the query string
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Fetch students for the selected course
$query = "
    SELECT s.id, s.name, s.username, sd.profile_image 
    FROM Student s
    JOIN student_courses sc ON s.id = sc.student_id
    JOIN Student_details sd ON s.id = sd.student_id
    WHERE sc.course_id = $course_id";
$result = $conn->query($query);

// Fetch the course name for the title
$courseQuery = $conn->query("SELECT course_name FROM courses WHERE id = $course_id");
$courseName = $courseQuery->fetch_assoc()['course_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students in <?php echo htmlspecialchars($courseName); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .card-container { display: flex; flex-wrap: wrap; gap: 20px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center; width: 200px; box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); transition: transform 0.3s; }
        .card:hover { transform: scale(1.05); cursor: pointer; }
        .card img { border-radius: 50%; width: 80px; height: 80px; object-fit: cover; margin-bottom: 10px; }
        .card h3 { margin: 5px 0; font-size: 16px; }
        .card p { margin: 5px 0; font-size: 14px; color: #555; }
        a { text-decoration: none; color: inherit; }
    </style>
</head>
<body>
    <h1>Students in <?php echo htmlspecialchars($courseName); ?></h1>
    <div class="card-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <a href="course_student_profile.php?username=<?php echo $row['username']; ?>">
                <div class="card">
                    <img src="<?php echo $row['profile_image'] ? $row['profile_image'] : '../uploads/default-profile.png'; ?>" alt="Profile Image">
                    <h3><?php echo $row['name']; ?></h3>
                    <p>@<?php echo $row['username']; ?></p>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
</body>
</html>
