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
    header("Location: students.php"); // Redirect to the students list after deletion
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <?php if ($student): ?>
            <div class="card shadow-sm mx-auto" style="max-width: 600px;">
                <div class="card-body text-center">
                    <!-- Profile Image -->
                    <img 
                        src="<?php echo $student['profile_image'] ? $student['profile_image'] : '../uploads/default-profile.png'; ?>" 
                        alt="Profile Image" 
                        class="rounded-circle img-fluid mb-3" 
                        style="width: 150px; height: 150px; object-fit: cover;">
                    
                    <!-- Student Name -->
                    <h3 class="card-title"><?php echo $student['name']; ?></h3>
                    <p class="text-muted">@<?php echo $student['username']; ?></p>

                    <!-- Divider -->
                    <hr>

                    <!-- Student Details -->
                    <div class="text-start">
                        <h5>Student Details</h5>
                        <p><strong>Age:</strong> <?php echo $student['age']; ?></p>
                        <p><strong>Date of Birth:</strong> <?php echo $student['dob']; ?></p>
                        <p><strong>NIC:</strong> <?php echo $student['nic']; ?></p>
                        <p><strong>Gender:</strong> <?php echo $student['gender']; ?></p>
                        <p><strong>Contact:</strong> <?php echo $student['contact']; ?></p>
                        <p><strong>Email:</strong> <?php echo $student['email']; ?></p>
                        
                        <hr>
                        
                        <h5>Parent Details</h5>
                        <p><strong>Mother's Name:</strong> <?php echo $student['mom_name']; ?></p>
                        <p><strong>Father's Name:</strong> <?php echo $student['dad_name']; ?></p>
                        <p><strong>Parent Contact:</strong> <?php echo $student['parent_contact']; ?></p>
                        <p><strong>Parent Email:</strong> <?php echo $student['parent_email']; ?></p>

                        <hr>

                        <h5>Address</h5>
                        <p><strong>Street:</strong> <?php echo $student['street']; ?></p>
                        <p><strong>City:</strong> <?php echo $student['city']; ?></p>
                        <p><strong>State:</strong> <?php echo $student['state']; ?></p>
                        <p><strong>Postal Code:</strong> <?php echo $student['postal_code']; ?></p>
                    </div>
                </div>
                <!-- Action Buttons -->
                <div class="card-footer text-center">
                    <a href="edit_student.php?username=<?php echo $student['username']; ?>" class="btn btn-warning me-2">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this student?');" style="display: inline;">
                        <button type="submit" name="delete" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger text-center">
                <strong>Student not found.</strong>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
