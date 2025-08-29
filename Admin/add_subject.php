<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

include('../db_connect.php');

$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subject'])) {
    $subject_name = $_POST['subject_name'];
    $stmt = $conn->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
    $stmt->bind_param("s", $subject_name);
    if ($stmt->execute()) {
        echo "<script>alert('Subject Added!'); window.location.href = './add_subject.php';</script>";
    } else {
        $errorMessage = "Error: " . $stmt->error;
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_subject'])) {
    $subject_name = $_POST['subject_name'];
    $stmt = $conn->prepare("DELETE FROM subjects WHERE subject_name = ?");
    $stmt->bind_param("s", $subject_name);
    if ($stmt->execute()) {
        echo "<script>alert('Subject Deleted!'); window.location.href = './add_subject.php';</script>";
    } else {
        $errorMessage = "Error: " . $stmt->error;
    }
    $stmt->close();
}

$result = $conn->query("SELECT subject_name FROM subjects");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <style>
        :root {
            --primary-blue: #1E3A8A;
            --secondary-teal: #2DD4BF;
            --accent-green: #10B981;
            --neutral-gray: #F7FAFC;
            --text-dark: #1A202C;
            --shadow-teal: rgba(45, 212, 191, 0.25);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        body {
            background: #F3F4F6;
        }
        .container {
            max-width: 600px;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .form-container {
            background: var(--neutral-gray);
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 8px var(--shadow-teal);
            padding: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px var(--shadow-teal);
        }
        .subject-sidebar {
            position: fixed;
            right: 0;
            top: 0;
            width: 200px;
            height: 100%;
            background: var(--neutral-gray);
            border-left: 1px solid #D1D5DB;
            padding: 1.5rem;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--secondary-teal) var(--neutral-gray);
        }
        .subject-sidebar::-webkit-scrollbar {
            width: 8px;
        }
        .subject-sidebar::-webkit-scrollbar-track {
            background: var(--neutral-gray);
        }
        .subject-sidebar::-webkit-scrollbar-thumb {
            background: var(--secondary-teal);
            border-radius: 4px;
        }
        .subject-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #D1D5DB;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s ease;
        }
        .subject-list li:hover {
            background: #E5E7EB;
        }
        .subject-list li .delete-btn {
            background: none;
            border: none;
            color: #DC3545;
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.2s ease;
        }
        .subject-list li .delete-btn:hover {
            color: #B91C1C;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #D1D5DB;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            border-color: var(--secondary-teal);
            box-shadow: 0 0 0 0.2rem var(--shadow-teal);
        }
        .btn-primary {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-teal));
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(to right, var(--secondary-teal), var(--primary-blue));
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--shadow-teal);
        }
        .back-btn {
            background: linear-gradient(to right, #6B7280, #4B5563);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background: linear-gradient(to right, #4B5563, #6B7280);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(107, 114, 128, 0.25);
        }
        h1, h2 {
            color: var(--text-dark);
        }
        .form-label {
            color: var(--text-dark);
            font-weight: 500;
        }
        .error-message {
            color: #DC3545;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="btn back-btn text-white mb-4">
            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="subject-sidebar">
        <h2 class="fw-semibold mb-3">Subjects</h2>
        <ul class="subject-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="fade-in">
                        <?php echo htmlspecialchars($row['subject_name']); ?>
                        <form action="add_subject.php" method="post" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                            <input type="hidden" name="subject_name" value="<?php echo htmlspecialchars($row['subject_name']); ?>">
                            <button type="submit" name="delete_subject" class="delete-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li>No subjects available</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="container">
        <div class="form-container fade-in">
            <h2 class="fw-semibold mb-3">Add New Subject</h2>
            <form action="add_subject.php" method="post">
                <input type="hidden" name="add_subject" value="1">
                <div class="mb-3">
                    <label for="subject_name" class="form-label">Enter the subject:</label>
                    <input type="text" name="subject_name" id="subject_name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-plus me-2"></i> Add Subject
                </button>
            </form>
            <?php if ($errorMessage): ?>
                <p class="error-message"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>