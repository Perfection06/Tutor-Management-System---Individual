<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize data
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_BCRYPT);
    $age = intval($_POST['age']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $nic = $conn->real_escape_string($_POST['nic']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $email = $conn->real_escape_string($_POST['email']);
    $mom_name = $conn->real_escape_string($_POST['mom_name']);
    $dad_name = $conn->real_escape_string($_POST['dad_name']);
    $parent_contact = $conn->real_escape_string($_POST['parent_contact']);
    $parent_email = $conn->real_escape_string($_POST['parent_email']);
    $street = $conn->real_escape_string($_POST['street']);
    $city = $conn->real_escape_string($_POST['city']);
    $state = $conn->real_escape_string($_POST['state']);
    $postal_code = $conn->real_escape_string($_POST['postal_code']);
    $type = $conn->real_escape_string($_POST['type']);
    $course_ids = isset($_POST['course_ids']) ? $_POST['course_ids'] : [];
    $class_ids = isset($_POST['class_ids']) ? $_POST['class_ids'] : [];

    // Check for duplicate entries
    $check_query = "
        SELECT * FROM Student s 
        LEFT JOIN Student_details sd ON s.id = sd.student_id 
        WHERE s.username = '$username' 
        OR sd.email = '$email' 
        OR sd.contact = '$contact' 
        OR sd.nic = '$nic'";
    $result = $conn->query($check_query);

    if ($result->num_rows > 0) {
        echo "<script>alert('Duplicate entry found! Ensure username, email, contact number, and NIC are unique.');</script>";
    } else {
        // Handle profile image upload
        $image_path = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../uploads/profile_images/";
            $file_name = uniqid() . "_" . basename($_FILES["profile_image"]["name"]);
            $target_file = $target_dir . $file_name;
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $valid_extensions = ["jpg", "jpeg", "png", "gif"];

            if (in_array($image_file_type, $valid_extensions)) {
                if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                    $image_path = $conn->real_escape_string($target_file);
                    echo "<script>console.log('Image uploaded to: $target_file');</script>";
                } else {
                    echo "<script>alert('Error uploading the profile image. Check folder permissions.');</script>";
                }
            } else {
                echo "<script>alert('Invalid image file format. Only JPG, JPEG, PNG, and GIF are allowed.');</script>";
            }
        } elseif (isset($_FILES['profile_image']['error'])) {
            echo "<script>alert('Error with file upload: " . $_FILES['profile_image']['error'] . "');</script>";
        }

        // Insert into Student table
        $sql = "INSERT INTO Student (name, username, password, program) VALUES ('$full_name', '$username', '$password', '$type')";
        if ($conn->query($sql) === TRUE) {
            $student_id = $conn->insert_id;

            // Insert into Student_details table
            $sql_details = "INSERT INTO Student_details (student_id, age, dob, nic, gender, contact, email, mom_name, dad_name, parent_contact, parent_email, street, city, state, postal_code, type, profile_image) 
                            VALUES ('$student_id', '$age', '$dob', '$nic', '$gender', '$contact', '$email', '$mom_name', '$dad_name', '$parent_contact', '$parent_email', '$street', '$city', '$state', '$postal_code', '$type', '$image_path')";
            $conn->query($sql_details);

            // Insert into student_courses table
            foreach ($course_ids as $course_id) {
                $course_id = intval($course_id);
                $conn->query("INSERT INTO student_courses (student_id, course_id) VALUES ('$student_id', '$course_id')");
            }

            // Insert into student_classes table
            foreach ($class_ids as $class_id) {
                $class_id = intval($class_id);
                $conn->query("INSERT INTO student_classes (student_id, class_id) VALUES ('$student_id', '$class_id')");
            }

            echo "<script>alert('Student and details added successfully with courses, classes, and optional profile image!');</script>";
            header("Location: add_student.php");
            exit();
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}

// Fetch courses
$courses = $conn->query("SELECT id, course_name FROM courses");

// Fetch grades
$grades = $conn->query("SELECT id, grade_name FROM grades");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js" defer></script>
    <style>
        :root {
            --primary-blue: #1E3A8A;
            --secondary-teal: #2DD4BF;
            --accent-green: #10B981;
            --neutral-gray: #F7FAFC;
            --text-dark: #1A202C;
            --shadow-teal: rgba(45, 212, 191, 0.3);
            --shadow-dark: rgba(0, 0, 0, 0.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        body {
            background: linear-gradient(to bottom, #E5E7EB, #F3F4F6);
            font-family: 'Inter', sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        h1, h2 {
            color: var(--text-dark);
            font-weight: 700;
            text-shadow: 0 2px 4px var(--shadow-dark);
        }

        .card {
            border: none;
            border-radius: 16px;
            background: var(--neutral-gray);
            box-shadow: 0 6px 12px var(--shadow-teal);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            opacity: 0;
        }

        .card.visible {
            opacity: 1;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px var(--shadow-teal);
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #D1D5DB;
            transition: all 0.3s ease;
            background: #FFFFFF;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-teal);
            box-shadow: 0 0 0 0.25rem var(--shadow-teal);
            outline: none;
        }

        .form-check-input {
            border: 2px solid #D1D5DB;
            transition: all 0.3s ease;
        }

        .form-check-input:checked {
            background-color: var(--secondary-teal);
            border-color: var(--secondary-teal);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem var(--shadow-teal);
        }

        .form-check-label {
            transition: color 0.3s ease;
        }

        .form-check:hover .form-check-label {
            color: var(--secondary-teal);
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary-blue), var(--secondary-teal));
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, var(--secondary-teal), var(--primary-blue));
            transform: translateY(-3px);
            box-shadow: 0 6px 12px var(--shadow-teal);
        }

        .btn-secondary {
            background: var(--primary-blue);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #1E40AF;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px var(--shadow-teal);
        }

        .back-btn {
            background: linear-gradient(90deg, #6B7280, #4B5563);
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: linear-gradient(90deg, #4B5563, #6B7280);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(107, 114, 128, 0.3);
        }

        .alert-info {
            background: linear-gradient(to right, #E6FFFA, #B2F5EA);
            border: none;
            border-radius: 10px;
            color: #1A202C;
            box-shadow: 0 4px 8px var(--shadow-dark);
            transition: opacity 0.3s ease;
        }

        .form-label {
            color: var(--text-dark);
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .form-group:hover .form-label {
            color: var(--secondary-teal);
        }

        /* Optimize for performance */
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .row {
            margin-right: 0;
            margin-left: 0;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4 fade-in">Student Profile Form</h1>
        <a href="admin_dashboard.php" class="btn btn-secondary back-btn mb-3"><i class="fas fa-arrow-left"></i> Back</a>
        <div class="card p-4 shadow">
            <form action="add_student.php" method="POST" enctype="multipart/form-data">
                <!-- Personal Information -->
                <div class="mb-4">
                    <h2 class="fade-in">Personal Information</h2>
                    <div class="mb-3 form-group">
                        <label for="full_name" class="form-label">Full Name:</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3 form-group">
                            <label for="age" class="form-label">Age:</label>
                            <input type="number" id="age" name="age" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3 form-group">
                            <label for="dob" class="form-label">Date of Birth:</label>
                            <input type="date" id="dob" name="dob" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="nic" class="form-label">NIC (if available):</label>
                        <input type="text" id="nic" name="nic" class="form-control">
                    </div>

                    <div class="mb-3 form-group">
                        <label class="form-label">Gender:</label><br>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="male" name="gender" value="Male" class="form-check-input" required>
                            <label for="male" class="form-check-label">Male</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="female" name="gender" value="Female" class="form-check-input">
                            <label for="female" class="form-check-label">Female</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="other" name="gender" value="Other" class="form-check-input">
                            <label for="other" class="form-check-label">Other</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3 form-group">
                            <label for="contact" class="form-label">Contact Number:</label>
                            <input type="text" id="contact" name="contact" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3 form-group">
                            <label for="email" class="form-label">Email Address:</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                    </div>
                </div>

                <!-- Parent Information -->
                <div class="mb-4">
                    <h2 class="fade-in">Parent Information</h2>
                    <div class="row">
                        <div class="col-md-6 mb-3 form-group">
                            <label for="mom_name" class="form-label">Mom's Name:</label>
                            <input type="text" id="mom_name" name="mom_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3 form-group">
                            <label for="dad_name" class="form-label">Dad's Name:</label>
                            <input type="text" id="dad_name" name="dad_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3 form-group">
                            <label for="parent_contact" class="form-label">Parent Contact Number:</label>
                            <input type="text" id="parent_contact" name="parent_contact" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3 form-group">
                            <label for="parent_email" class="form-label">Parent Email Address:</label>
                            <input type="email" id="parent_email" name="parent_email" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="mb-4">
                    <h2 class="fade-in">Address</h2>
                    <div class="mb-3 form-group">
                        <label for="street" class="form-label">Street:</label>
                        <input type="text" id="street" name="street" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3 form-group">
                            <label for="city" class="form-label">City:</label>
                            <input type="text" id="city" name="city" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3 form-group">
                            <label for="state" class="form-label">State:</label>
                            <input type="text" id="state" name="state" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3 form-group">
                            <label for="postal_code" class="form-label">Postal Code:</label>
                            <input type="text" id="postal_code" name="postal_code" class="form-control" required>
                        </div>
                    </div>
                </div>

                <!-- Profile Image -->
                <div class="mb-4">
                    <h2 class="fade-in">Profile Image</h2>
                    <div class="mb-3 form-group">
                        <label for="profile_image" class="form-label">Upload Profile Image (optional):</label>
                        <input type="file" id="profile_image" name="profile_image" class="form-control" accept="image/*">
                    </div>
                </div>

                <!-- Courses or Classes -->
                <div class="mb-4">
                    <h2 class="fade-in">Select Courses or Classes</h2>
                    <div class="mb-3 form-group">
                        <label for="selection" class="form-label">Select Type:</label>
                        <select id="selection" name="type" class="form-select" onchange="toggleCourseClassSelection()">
                            <option value="">-- Select --</option>
                            <option value="course">Course</option>
                            <option value="class">Class</option>
                        </select>
                    </div>

                    <div id="course_section" style="display: none;" class="form-group">
                        <label class="form-label">Select Courses:</label><br>
                        <?php while ($row = $courses->fetch_assoc()): ?>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input course-checkbox" id="course_<?php echo $row['id']; ?>" name="course_ids[]" value="<?php echo $row['id']; ?>">
                                <label for="course_<?php echo $row['id']; ?>" class="form-check-label"><?php echo $row['course_name']; ?></label>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div id="class_section" style="display: none;" class="form-group">
                        <label for="grades" class="form-label">Select Grade:</label>
                        <select id="grades" name="grade_id" class="form-select" onchange="fetchClasses()">
                            <option value="">-- Select Grade --</option>
                            <?php while ($row = $grades->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['grade_name']; ?></option>
                            <?php endwhile; ?>
                        </select>

                        <div id="classes_checkbox_list" class="mt-3">
                            <label class="form-label">Select Classes:</label><br>
                        </div>
                    </div>
                </div>

                <!-- Login Details -->
                <div class="mb-4">
                    <h2 class="fade-in">Login Details</h2>
                    <div class="mb-3 form-group">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3 form-group">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleCourseClassSelection() {
            const selection = document.getElementById('selection').value;

            // Hide and reset both sections
            document.getElementById('course_section').style.display = 'none';
            document.getElementById('class_section').style.display = 'none';
            resetCheckboxes('course-checkbox');
            resetCheckboxes('class-checkbox');

            if (selection === 'course') {
                document.getElementById('course_section').style.display = 'block';
            } else if (selection === 'class') {
                document.getElementById('class_section').style.display = 'block';
            }
        }

        function fetchClasses() {
            const gradeId = document.getElementById('grades').value;
            const classCheckboxList = document.getElementById('classes_checkbox_list');
            classCheckboxList.innerHTML = ''; // Clear previous checkboxes

            if (gradeId) {
                fetch('fetch_classes.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `grade_id=${gradeId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(classItem => {
                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.className = 'class-checkbox form-check-input';
                            checkbox.id = `class_${classItem.id}`;
                            checkbox.name = 'class_ids[]';
                            checkbox.value = classItem.id;

                            const label = document.createElement('label');
                            label.setAttribute('for', `class_${classItem.id}`);
                            label.textContent = `${classItem.title} (${classItem.subject_name})`;
                            label.className = 'form-check-label';

                            const div = document.createElement('div');
                            div.className = 'form-check fade-in';

                            div.appendChild(checkbox);
                            div.appendChild(label);
                            classCheckboxList.appendChild(div);
                        });
                    })
                    .catch(error => {
                        classCheckboxList.innerHTML = '<p class="text-danger">Error loading classes. Please try again later.</p>';
                        console.error('Error fetching classes:', error);
                    });
            }
        }

        function resetCheckboxes(className) {
            const checkboxes = document.getElementsByClassName(className);
            for (let checkbox of checkboxes) {
                checkbox.checked = false;
            }
        }

        // Lazy load card for performance
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => observer.observe(card));
        });
    </script>
</body>
</html>