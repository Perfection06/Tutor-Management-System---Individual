<?php
include('../db_connect.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'course') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$error = $success = "";

// Fetch student profile details
$query = "
    SELECT 
        s.id, 
        s.name, 
        s.username, 
        sd.age, 
        sd.dob, 
        sd.nic, 
        sd.gender, 
        sd.contact, 
        sd.email, 
        sd.mom_name, 
        sd.dad_name, 
        sd.parent_contact, 
        sd.parent_email, 
        sd.street, 
        sd.city, 
        sd.state, 
        sd.postal_code, 
        sd.profile_image 
    FROM Student s 
    JOIN Student_details sd ON s.id = sd.student_id 
    WHERE s.id = $student_id AND s.program = 'course'
";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    die("Student details not found.");
}

// Fetch enrolled courses
$course_query = "
    SELECT 
        c.course_name, 
        c.course_details, 
        c.duration, 
        c.start_date, 
        c.end_date 
    FROM student_courses sc
    JOIN courses c ON sc.course_id = c.id
    WHERE sc.student_id = $student_id
";
$courses_result = mysqli_query($conn, $course_query);
$courses = mysqli_fetch_all($courses_result, MYSQLI_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'];
    $upload_dir = "../uploads/profile_images/";
    $profile_image = $student['profile_image'];

    if (!empty($_FILES['profile_image']['name'])) {
        $filename = basename($_FILES['profile_image']['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
            $profile_image = $target_path;
        } else {
            $error = "Error uploading profile image.";
        }
    }

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update_query = "
            UPDATE Student 
            SET password = '$hashed_password' 
            WHERE id = $student_id
        ";
        mysqli_query($conn, $update_query);
    }

    $update_profile_query = "
        UPDATE Student_details 
        SET profile_image = '$profile_image' 
        WHERE student_id = $student_id
    ";
    if (mysqli_query($conn, $update_profile_query)) {
        $success = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } else {
        $error = "Error updating profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table th, table td {
            text-align: left;
            padding: 10px;
            border: 1px solid #ddd;
        }

        table th {
            background: #007bff;
            color: white;
        }

        table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .profile-image {
            display: block;
            margin: 0 auto 20px;
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #007bff;
        }

        .edit-button {
            display: block;
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        .edit-button:hover {
            background: #0056b3;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 400px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            font-size: 1.2em;
            margin-bottom: 20px;
            text-align: center;
            color: #007bff;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .modal-footer {
            text-align: right;
        }

        .btn-close, .btn-save {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-close {
            background: #ddd;
            color: #333;
        }

        .btn-close:hover {
            background: #bbb;
        }

        .btn-save {
            background: #007bff;
            color: white;
        }

        .btn-save:hover {
            background: #0056b3;
        }
    </style>
    <script>
        function openModal() {
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</head>
<body>
<div class="container">
    <h1>My Profile</h1>
    <img src="<?php echo htmlspecialchars($student['profile_image'] ?? '../uploads/profile_images/default.png'); ?>" alt="Profile Image" class="profile-image">

    <h2>Personal Details</h2>
    <table>
        <tr><th>Name</th><td><?php echo htmlspecialchars($student['name']); ?></td></tr>
        <tr><th>Username</th><td><?php echo htmlspecialchars($student['username']); ?></td></tr>
        <tr><th>Age</th><td><?php echo htmlspecialchars($student['age']); ?></td></tr>
        <tr><th>Date of Birth</th><td><?php echo htmlspecialchars($student['dob']); ?></td></tr>
        <tr><th>NIC</th><td><?php echo htmlspecialchars($student['nic']); ?></td></tr>
        <tr><th>Gender</th><td><?php echo htmlspecialchars($student['gender']); ?></td></tr>
        <tr><th>Contact</th><td><?php echo htmlspecialchars($student['contact']); ?></td></tr>
        <tr><th>Email</th><td><?php echo htmlspecialchars($student['email']); ?></td></tr>
        <tr><th>Address</th><td><?php echo htmlspecialchars($student['street'] . ', ' . $student['city'] . ', ' . $student['state'] . ', ' . $student['postal_code']); ?></td></tr>
    </table>

    <h2>Parent Details</h2>
    <table>
        <tr><th>Mother's Name</th><td><?php echo htmlspecialchars($student['mom_name']); ?></td></tr>
        <tr><th>Father's Name</th><td><?php echo htmlspecialchars($student['dad_name']); ?></td></tr>
        <tr><th>Parent Contact</th><td><?php echo htmlspecialchars($student['parent_contact']); ?></td></tr>
        <tr><th>Parent Email</th><td><?php echo htmlspecialchars($student['parent_email']); ?></td></tr>
    </table>

    <h2>Enrolled Courses</h2>
    <?php if (!empty($courses)) { ?>
        <table>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Details</th>
                    <th>Duration</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($course['course_details']); ?></td>
                        <td><?php echo htmlspecialchars($course['duration']); ?></td>
                        <td><?php echo htmlspecialchars($course['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($course['end_date']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No enrolled courses found.</p>
    <?php } ?>

    <button class="edit-button" onclick="openModal()">Edit Profile</button>
</div>

<!-- Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Edit Profile</div>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" name="password" id="password" placeholder="Enter new password">
            </div>
            <div class="form-group">
                <label for="profile_image">Profile Image</label>
                <input type="file" name="profile_image" id="profile_image" accept="image/*">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-close" onclick="closeModal()">Close</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
