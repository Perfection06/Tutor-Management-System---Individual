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

// Fetch classes based on grade
$classes = [];
if (isset($_POST['grade'])) {
    $grade_id = intval($_POST['grade']);
    $class_query = "SELECT id, title FROM classes WHERE grade = $grade_id";
    $class_result = mysqli_query($conn, $class_query);

    while ($row = mysqli_fetch_assoc($class_result)) {
        $classes[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'], $_POST['messageType'])) {
    $admin_id = $_SESSION['admin_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $messageType = mysqli_real_escape_string($conn, $_POST['messageType']);
    $target_id = 0;
    $attachment = null;

    // Handle file upload if provided
    if (!empty($_FILES['attachment']['name'])) {
        $target_dir = "../uploads/messages/";
        $attachment = $target_dir . basename($_FILES['attachment']['name']);
        if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $attachment)) {
            $attachment = null;
        }
    }

    // Determine target (course or class)
    if ($messageType === 'course' && isset($_POST['course'])) {
        $target_id = intval($_POST['course']);
    } elseif ($messageType === 'class' && isset($_POST['class'])) {
        $target_id = intval($_POST['class']);
    }

    // Insert into broadcast_message table
    if ($target_id > 0) {
        $insert_query = "INSERT INTO broadcast_message (admin_id, message_type, target_id, message, attachment)
                         VALUES ($admin_id, '$messageType', $target_id, '$message', '$attachment')";
        if (mysqli_query($conn, $insert_query)) {
            echo "<script>alert('Message broadcasted successfully!'); window.location.href = 'broadcast.php';</script>";
        } else {
            echo "<script>alert('Error broadcasting message. Please try again.');</script>";
        }
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $message_id = intval($_GET['delete']);
    $delete_query = "DELETE FROM broadcast_message WHERE id = $message_id AND admin_id = {$_SESSION['admin_id']}";
    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('Message deleted successfully!'); window.location.href = 'broadcast.php';</script>";
    } else {
        echo "<script>alert('Error deleting message. Please try again.');</script>";
    }
}

// Fetch all broadcast messages
$messages_query = "SELECT bm.id, bm.message_type, bm.target_id, bm.message, bm.attachment, bm.created_at,
                   CASE 
                       WHEN bm.message_type = 'course' THEN c.course_name
                       WHEN bm.message_type = 'class' THEN cl.title
                   END AS target_name
                   FROM broadcast_message bm
                   LEFT JOIN courses c ON bm.message_type = 'course' AND bm.target_id = c.id
                   LEFT JOIN classes cl ON bm.message_type = 'class' AND bm.target_id = cl.id
                   WHERE bm.admin_id = {$_SESSION['admin_id']}
                   ORDER BY bm.created_at DESC";
$messages_result = mysqli_query($conn, $messages_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broadcast Message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 600px;
            margin-bottom: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            margin-top: 10px;
            display: block;
        }

        select, textarea, input[type="file"], button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        table {
            width: 80%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f7fc;
        }

        a.delete-btn {
            color: red;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Broadcast Message</h1>
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
            <select name="course" id="course">
                <option value="">--Select Course--</option>
                <?php while ($row = mysqli_fetch_assoc($course_result)) { ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['course_name']; ?></option>
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
                <label for="class">Select Class:</label>
                <select name="class" id="class">
                    <option value="">--Select Class--</option>
                    <?php foreach ($classes as $class) { ?>
                        <option value="<?php echo $class['id']; ?>"><?php echo $class['title']; ?></option>
                    <?php } ?>
                </select>
            <?php } ?>
        <?php } ?>

        <!-- Message -->
        <label for="message">Message:</label>
        <textarea name="message" id="message" rows="4" placeholder="Type your message here..." required></textarea>

        <!-- Attachment -->
        <label for="attachment">Attach File (Optional):</label>
        <input type="file" name="attachment" id="attachment">

        <!-- Submit Button -->
        <button type="submit">Send Message</button>
    </form>
</div>

<div class="container">
    <h1>Sent Messages</h1>
    <table>
        <thead>
        <tr>
            <th>Type</th>
            <th>Target</th>
            <th>Message</th>
            <th>Attachment</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($messages_result)) { ?>
            <tr>
                <td><?php echo ucfirst($row['message_type']); ?></td>
                <td><?php echo $row['target_name']; ?></td>
                <td><?php echo $row['message']; ?></td>
                <td>
                    <?php if ($row['attachment']) { ?>
                        <a href="<?php echo $row['attachment']; ?>" target="_blank">View</a>
                    <?php } else { ?>
                        N/A
                    <?php } ?>
                </td>
                <td><?php echo $row['created_at']; ?></td>
                <td><a href="?delete=<?php echo $row['id']; ?>" class="delete-btn">Delete</a></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
