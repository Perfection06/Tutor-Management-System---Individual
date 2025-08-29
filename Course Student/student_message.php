<?php

include('../db_connect.php');

session_start();
// Ensure only course students can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'course') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    $username = $_SESSION['username'];
    $program = 'course'; // Hardcoded to course student
    $message = trim($_POST['message']);

    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO Message (username, program, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $program, $message);

        if ($stmt->execute()) {
            $success = "Message sent successfully!";
        } else {
            $error = "Failed to send the message. Please try again.";
        }

        $stmt->close();
    } else {
        $error = "Message cannot be empty.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Student - Send Message</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <!-- Back Button -->
        <a href="message.html" class="btn btn-secondary mb-4">&larr; Back</a>
        
        <!-- Message Form -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="card-title text-center mb-4">Send Message to Admin</h1>
                <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <textarea 
                            name="message" 
                            class="form-control" 
                            rows="5" 
                            placeholder="Write your message here..."
                            required></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
