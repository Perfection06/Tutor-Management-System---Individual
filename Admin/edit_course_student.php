<?php
include '../db_connect.php';

$username = isset($_GET['username']) ? $conn->real_escape_string($_GET['username']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    $name = $_POST['name'];
    $age = $_POST['age'];
    $dob = $_POST['dob'];
    $nic = $_POST['nic'];
    $gender = $_POST['gender'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $postal_code = $_POST['postal_code'];
    $courses = isset($_POST['course_ids']) ? $_POST['course_ids'] : [];

    // Update student and address details
    $query = "
        UPDATE Student_details sd
        JOIN Student s ON sd.student_id = s.id
        SET sd.age = '$age', sd.dob = '$dob', sd.nic = '$nic', sd.gender = '$gender', 
            sd.contact = '$contact', sd.email = '$email', s.name = '$name',
            sd.street = '$street', sd.city = '$city', sd.state = '$state', sd.postal_code = '$postal_code'
        WHERE s.username = '$username'";
    $conn->query($query);

    // Update courses only if selected
    if (!empty($courses)) {
        $student_id = $conn->query("SELECT id FROM Student WHERE username = '$username'")->fetch_assoc()['id'];

        // Clear existing course enrollments
        $conn->query("DELETE FROM student_courses WHERE student_id = $student_id");

        // Enroll in selected courses
        $stmt = $conn->prepare("INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)");
        foreach ($courses as $course_id) {
            $stmt->bind_param("ii", $student_id, $course_id);
            $stmt->execute();
        }
    }

    header("Location: course_student_profile.php?username=$username");
    exit;
}

// Fetch student details
$query = "
    SELECT s.name, sd.age, sd.dob, sd.nic, sd.gender, sd.contact, sd.email, 
           sd.street, sd.city, sd.state, sd.postal_code
    FROM Student s
    JOIN Student_details sd ON s.id = sd.student_id
    WHERE s.username = '$username'";
$result = $conn->query($query);
$student = $result->fetch_assoc();

// Fetch all courses
$courses = $conn->query("SELECT id, course_name FROM courses")->fetch_all(MYSQLI_ASSOC);

// Fetch already enrolled courses
$student_id = $conn->query("SELECT id FROM Student WHERE username = '$username'")->fetch_assoc()['id'];
$enrolled_courses = $conn->query("SELECT course_id FROM student_courses WHERE student_id = $student_id")
    ->fetch_all(MYSQLI_ASSOC);
$enrolled_course_ids = array_column($enrolled_courses, 'course_id');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
</head>
<body>
    <h1>Edit Student Details</h1>
    <form method="POST">
        <label>Name: <input type="text" name="name" value="<?php echo $student['name']; ?>"></label><br>
        <label>Age: <input type="number" name="age" value="<?php echo $student['age']; ?>"></label><br>
        <label>Date of Birth: <input type="date" name="dob" value="<?php echo $student['dob']; ?>"></label><br>
        <label>NIC: <input type="text" name="nic" value="<?php echo $student['nic']; ?>"></label><br>
        <label>Gender: 
            <select name="gender">
                <option value="Male" <?php echo $student['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo $student['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo $student['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </label><br>
        <label>Contact: <input type="text" name="contact" value="<?php echo $student['contact']; ?>"></label><br>
        <label>Email: <input type="email" name="email" value="<?php echo $student['email']; ?>"></label><br>
        <h3>Address</h3>
        <label>Street: <input type="text" name="street" value="<?php echo $student['street']; ?>"></label><br>
        <label>City: <input type="text" name="city" value="<?php echo $student['city']; ?>"></label><br>
        <label>State: <input type="text" name="state" value="<?php echo $student['state']; ?>"></label><br>
        <label>Postal Code: <input type="text" name="postal_code" value="<?php echo $student['postal_code']; ?>"></label><br>
        <h3>Enroll in Courses</h3>
        <div id="course_section">
            <?php foreach ($courses as $course): ?>
                <label>
                    <input type="checkbox" name="course_ids[]" value="<?php echo $course['id']; ?>" 
                        <?php echo in_array($course['id'], $enrolled_course_ids) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8'); ?>
                </label><br>
            <?php endforeach; ?>
        </div>
        <button type="submit">Save</button>
    </form>
</body>
</html>
