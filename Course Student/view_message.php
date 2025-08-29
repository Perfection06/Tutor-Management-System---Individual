<?php
include('../db_connect.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'course') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$messages = [];

// Fetch student's enrolled courses
$course_query = "SELECT course_id FROM student_courses WHERE student_id = $student_id";
$course_result = mysqli_query($conn, $course_query);
$enrolled_courses = [];

while ($row = mysqli_fetch_assoc($course_result)) {
    $enrolled_courses[] = $row['course_id'];
}

// Ensure $enrolled_courses is not empty
if (!empty($enrolled_courses)) {
    // Fetch individual messages
    $individual_query = "
        SELECT 
            'Individual' AS message_type, 
            message, 
            attachment, 
            sent_at 
        FROM individual_messages 
        WHERE student_id = $student_id AND student_type = 'course'
    ";
    $individual_result = mysqli_query($conn, $individual_query);

    // Fetch broadcast messages for enrolled courses
    $broadcast_query = "
        SELECT 
            'Broadcast' AS message_type, 
            bm.message, 
            bm.attachment, 
            bm.created_at AS sent_at 
        FROM broadcast_message bm
        WHERE bm.message_type = 'course' AND bm.target_id IN (" . implode(',', $enrolled_courses) . ")
    ";
    $broadcast_result = mysqli_query($conn, $broadcast_query);

    // Combine messages into a single array
    if ($individual_result) {
        while ($row = mysqli_fetch_assoc($individual_result)) {
            $messages[] = $row;
        }
    }

    if ($broadcast_result) {
        while ($row = mysqli_fetch_assoc($broadcast_result)) {
            $messages[] = $row;
        }
    }
} else {
    // Handle the case where the student is not enrolled in any courses
    $messages[] = [
        'message_type' => 'System',
        'message' => 'You are not enrolled in any courses.',
        'attachment' => null,
        'sent_at' => date('Y-m-d H:i:s'),
    ];
}

// Sort messages by sent_at in descending order
usort($messages, function ($a, $b) {
    return strtotime($b['sent_at']) - strtotime($a['sent_at']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Student Messages</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>My Messages</h1>
    <table>
        <thead>
        <tr>
            <th>Type</th>
            <th>Message</th>
            <th>Attachment</th>
            <th>Sent At</th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($messages) > 0) { ?>
            <?php foreach ($messages as $message) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($message['message_type']); ?></td>
                    <td><?php echo htmlspecialchars($message['message']); ?></td>
                    <td>
                        <?php if ($message['attachment']) { ?>
                            <a href="<?php echo htmlspecialchars($message['attachment']); ?>" target="_blank">View</a>
                        <?php } else { ?>
                            N/A
                        <?php } ?>
                    </td>
                    <td><?php echo htmlspecialchars($message['sent_at']); ?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="4" style="text-align: center;">No messages found.</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
