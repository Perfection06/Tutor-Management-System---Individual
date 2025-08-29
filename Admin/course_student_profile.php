<?php
include '../db_connect.php';

// Get username from the query string
$username = isset($_GET['username']) ? $conn->real_escape_string($_GET['username']) : '';

// Fetch student details
$query = "
    SELECT s.id, s.name, s.username, sd.age, sd.dob, sd.nic, sd.gender, sd.contact, sd.email, sd.mom_name, sd.dad_name, 
           sd.parent_contact, sd.parent_email, sd.street, sd.city, sd.state, sd.postal_code, sd.profile_image 
    FROM Student s
    JOIN Student_details sd ON s.id = sd.student_id
    WHERE s.username = '$username'";
$result = $conn->query($query);
$student = $result->fetch_assoc();

// Handle the deletion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $conn->query("DELETE FROM Student WHERE username = '$username'");
    header("Location: students.php"); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .profile-container { display: flex; align-items: center; gap: 20px; }
        .profile-image { border-radius: 50%; width: 150px; height: 150px; object-fit: cover; }
        .profile-details { max-width: 600px; }
        .profile-details h2 { margin: 10px 0; }
        .profile-details p { margin: 5px 0; color: #555; }
        .actions { margin-top: 20px; }
        .actions button { padding: 10px 15px; margin-right: 10px; border: none; border-radius: 5px; cursor: pointer; }
        .edit-btn { background-color: #4CAF50; color: white; }
        .delete-btn { background-color: #f44336; color: white; }
    </style>
</head>
<body>
    <?php if ($student): ?>
        <div class="profile-container">
            <img class="profile-image" src="<?php echo $student['profile_image'] ? $student['profile_image'] : '../uploads/default-profile.png'; ?>" alt="Profile Image">
            <div class="profile-details">
                <h2><?php echo $student['name']; ?></h2>
                <p><strong>Username:</strong> @<?php echo $student['username']; ?></p>
                <p><strong>Age:</strong> <?php echo $student['age']; ?></p>
                <p><strong>Date of Birth:</strong> <?php echo $student['dob']; ?></p>
                <p><strong>NIC:</strong> <?php echo $student['nic']; ?></p>
                <p><strong>Gender:</strong> <?php echo $student['gender']; ?></p>
                <p><strong>Contact:</strong> <?php echo $student['contact']; ?></p>
                <p><strong>Email:</strong> <?php echo $student['email']; ?></p>
                <h3>Parent Details</h3>
                <p><strong>Mother's Name:</strong> <?php echo $student['mom_name']; ?></p>
                <p><strong>Father's Name:</strong> <?php echo $student['dad_name']; ?></p>
                <p><strong>Parent Contact:</strong> <?php echo $student['parent_contact']; ?></p>
                <p><strong>Parent Email:</strong> <?php echo $student['parent_email']; ?></p>
                <h3>Address</h3>
                <p><strong>Street:</strong> <?php echo $student['street']; ?></p>
                <p><strong>City:</strong> <?php echo $student['city']; ?></p>
                <p><strong>State:</strong> <?php echo $student['state']; ?></p>
                <p><strong>Postal Code:</strong> <?php echo $student['postal_code']; ?></p>
            </div>
        </div>
        <div class="actions">
            <a href="edit_course_student.php?username=<?php echo $student['username']; ?>">
                <button class="edit-btn">Edit</button>
            </a>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this student?');" style="display:inline;">
                <button type="submit" name="delete" class="delete-btn">Delete</button>
            </form>
        </div>
    <?php else: ?>
        <p>Student not found.</p>
    <?php endif; ?>
</body>
</html>
