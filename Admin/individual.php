<?php
session_start();
include('../db_connect.php');

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch courses
$course_query = "SELECT id, course_name FROM courses";
$course_result = mysqli_query($conn, $course_query);

// Fetch grades
$grades_query = "SELECT id, grade_name FROM grades";
$grades_result = mysqli_query($conn, $grades_query);

// Variables to store dynamically generated data
$classes = [];
$students = [];

// Fetch classes if a grade is selected
if (isset($_POST['grade'])) {
    $grade_id = intval($_POST['grade']);
    $class_query = "SELECT id, title FROM classes WHERE grade = $grade_id";
    $class_result = mysqli_query($conn, $class_query);

    while ($row = mysqli_fetch_assoc($class_result)) {
        $classes[] = $row;
    }
}

// Fetch students if a course or class is selected
if (isset($_POST['messageType'])) {
    $type = $_POST['messageType'];

    if ($type === 'course' && isset($_POST['course'])) {
        $course_id = intval($_POST['course']);
        $student_query = "SELECT s.id, s.name FROM student_courses sc
                          INNER JOIN Student s ON sc.student_id = s.id
                          WHERE sc.course_id = $course_id";
    } elseif ($type === 'class' && isset($_POST['class'])) {
        $class_id = intval($_POST['class']);
        $student_query = "SELECT s.id, s.name FROM student_classes sc
                          INNER JOIN Student s ON sc.student_id = s.id
                          WHERE sc.class_id = $class_id";
    }

    if (isset($student_query)) {
        $student_result = mysqli_query($conn, $student_query);

        while ($row = mysqli_fetch_assoc($student_result)) {
            $students[] = $row;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'], $_POST['students'], $_POST['messageType'])) {
    $admin_id = $_SESSION['admin_id']; // Logged-in admin ID
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $messageType = mysqli_real_escape_string($conn, $_POST['messageType']);
    $attachment = null;

    // Handle file upload if provided
    if (!empty($_FILES['attachment']['name'])) {
        $target_dir = "../uploads/messages/";
        $attachment = $target_dir . basename($_FILES['attachment']['name']);
        if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $attachment)) {
            $attachment = null;
        }
    }

    // Insert messages for each selected student
    foreach ($_POST['students'] as $student_id) {
        $student_id = intval($student_id);

        $insert_query = "INSERT INTO individual_messages (student_id, admin_id, student_type, message, attachment)
                         VALUES ($student_id, $admin_id, '$messageType', '$message', '$attachment')";

        mysqli_query($conn, $insert_query);
    }

    // Redirect or show success message
    echo "<script>alert('Messages sent successfully!'); window.location.href = 'individual.php';</script>";
    exit();
}

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message_id'])) {
    $message_id = intval($_POST['delete_message_id']);
    $admin_id = $_SESSION['admin_id'];

    // Ensure only messages sent by the logged-in admin can be deleted
    $delete_query = "DELETE FROM individual_messages WHERE id = $message_id AND admin_id = $admin_id";
    mysqli_query($conn, $delete_query);

    // Redirect or reload the page after deletion
    echo "<script>alert('Message deleted successfully!'); window.location.href = 'individual.php';</script>";
    exit();
}

// Fetch sent messages by the logged-in admin
$admin_id = $_SESSION['admin_id'];
$messages_query = "
    SELECT m.id, m.message, m.attachment, m.sent_at, 
           CASE m.student_type 
               WHEN 'course' THEN c.course_name 
               WHEN 'class' THEN cl.title 
           END AS student_type_name,
           s.name AS student_name
    FROM individual_messages m
    LEFT JOIN Student s ON m.student_id = s.id
    LEFT JOIN courses c ON m.student_type = 'course' AND c.id = (SELECT course_id FROM student_courses WHERE student_id = m.student_id LIMIT 1)
    LEFT JOIN classes cl ON m.student_type = 'class' AND cl.id = (SELECT class_id FROM student_classes WHERE student_id = m.student_id LIMIT 1)
    WHERE m.admin_id = $admin_id
    ORDER BY m.sent_at DESC";

$messages_result = mysqli_query($conn, $messages_query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual Messaging</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 600px;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 10px;
        }

        select, textarea, input[type="file"] {
            padding: 8px;
            margin-top: 5px;
            width: 100%;
        }

        .checkbox-group {
            margin-top: 10px;
            max-height: 200px;
            overflow-y: auto;
        }

        .checkbox-group label {
            display: block;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            margin-top: 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .btn:disabled {
            background-color: #ddd;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Send Individual Message</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <!-- Select Type -->
        <label for="messageType">Select Type:</label>
        <select name="messageType" id="messageType" onchange="this.form.submit()">
            <option value="">--Select--</option>
            <option value="course" <?php if (isset($_POST['messageType']) && $_POST['messageType'] === 'course') echo 'selected'; ?>>Course</option>
            <option value="class" <?php if (isset($_POST['messageType']) && $_POST['messageType'] === 'class') echo 'selected'; ?>>Class</option>
        </select>

        <!-- Course Dropdown -->
        <?php if (isset($_POST['messageType']) && $_POST['messageType'] === 'course') { ?>
            <label for="course">Select Course:</label>
            <select name="course" id="course" onchange="this.form.submit()">
                <option value="">--Select Course--</option>
                <?php while ($row = mysqli_fetch_assoc($course_result)) { ?>
                    <option value="<?php echo $row['id']; ?>" <?php if (isset($_POST['course']) && $_POST['course'] == $row['id']) echo 'selected'; ?>>
                        <?php echo $row['course_name']; ?>
                    </option>
                <?php } ?>
            </select>
        <?php } ?>

        <!-- Grade and Class Dropdown -->
        <?php if (isset($_POST['messageType']) && $_POST['messageType'] === 'class') { ?>
            <label for="grade">Select Grade:</label>
            <select name="grade" id="grade" onchange="this.form.submit()">
                <option value="">--Select Grade--</option>
                <?php while ($row = mysqli_fetch_assoc($grades_result)) { ?>
                    <option value="<?php echo $row['id']; ?>" <?php if (isset($_POST['grade']) && $_POST['grade'] == $row['id']) echo 'selected'; ?>>
                        <?php echo $row['grade_name']; ?>
                    </option>
                <?php } ?>
            </select>

            <?php if (!empty($classes)) { ?>
                <label for="class" style="margin-top: 10px;">Select Class:</label>
                <select name="class" id="class" onchange="this.form.submit()">
                    <option value="">--Select Class--</option>
                    <?php foreach ($classes as $class) { ?>
                        <option value="<?php echo $class['id']; ?>" <?php if (isset($_POST['class']) && $_POST['class'] == $class['id']) echo 'selected'; ?>>
                            <?php echo $class['title']; ?>
                        </option>
                    <?php } ?>
                </select>
            <?php } ?>
        <?php } ?>

        <!-- Students Checkbox -->
        <?php if (!empty($students)) { ?>
            <div class="checkbox-group">
                <?php foreach ($students as $student) { ?>
                    <label>
                        <input type="checkbox" name="students[]" value="<?php echo $student['id']; ?>"> <?php echo $student['name']; ?>
                    </label>
                <?php } ?>
            </div>
        <?php } ?>

        <!-- Message -->
        <label for="message">Message:</label>
        <textarea name="message" id="message" rows="5" required></textarea>

        <!-- Attachment -->
        <label for="attachment">Attach File (optional):</label>
        <input type="file" name="attachment" id="attachment">

        <!-- Submit Button -->
        <button type="submit" class="btn">Send Message</button>
    </form>



<!-- Display Sent Messages -->
<div>
    <h2>Sent Messages</h2>
    <?php if (mysqli_num_rows($messages_result) > 0) { ?>
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Message</th>
                    <th>Attachment</th>
                    <th>Student Name</th>
                    <th>Type</th>
                    <th>Sent At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($message = mysqli_fetch_assoc($messages_result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($message['message']); ?></td>
                        <td>
                            <?php if (!empty($message['attachment'])) { ?>
                                <a href="<?php echo $message['attachment']; ?>" target="_blank">View File</a>
                            <?php } else { ?>
                                No Attachment
                            <?php } ?>
                        </td>
                        <td><?php echo htmlspecialchars($message['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($message['student_type_name']); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($message['sent_at'])); ?></td>
                        <td>
                            <!-- Delete Button -->
                            <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this message?');" style="display: inline;">
                                <input type="hidden" name="delete_message_id" value="<?php echo $message['id']; ?>">
                                <button type="submit" style="background-color: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No messages sent yet.</p>
    <?php } ?>
</div>

</div>
</body>
</html>
