<?php
session_start();
// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection
include('../db_connect.php');

$errorMessage = "";

// Handle form submission for adding a grade
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_grade'])) {
    $grade_name = $_POST['grade_name'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO grades (grade_name) VALUES (?)");
    $stmt->bind_param("s", $grade_name);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<script>alert('Grade Added!'); window.location.href = './add_grade.php';</script>";
    } else {
        $errorMessage = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle grade deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_grade'])) {
    $grade_name = $_POST['grade_name'];

    // Prepare and bind
    $stmt = $conn->prepare("DELETE FROM grades WHERE grade_name = ?");
    $stmt->bind_param("s", $grade_name);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<script>alert('Grade Deleted!'); window.location.href = './add_grade.php';</script>";
    } else {
        $errorMessage = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all grades
$result = $conn->query("SELECT grade_name FROM grades");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Grade</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <style>
        /* Custom color palette */
        :root {
            --primary-blue: #1E3A8A; /* Deep blue for trust */
            --secondary-teal: #2DD4BF; /* Teal for engagement */
            --accent-green: #10B981; /* Green for growth */
            --neutral-gray: #F7FAFC; /* Soft gray for background */
            --text-dark: #1A202C; /* Dark text for contrast */
            --shadow-teal: rgba(45, 212, 191, 0.25); /* Teal shadow for depth */
        }
        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        /* Body and container */
        body {
            background: #F3F4F6;
        }
        .container {
            max-width: 600px;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        /* Form card */
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
        /* Sidebar */
        .grades-sidebar {
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
        .grades-sidebar::-webkit-scrollbar {
            width: 8px;
        }
        .grades-sidebar::-webkit-scrollbar-track {
            background: var(--neutral-gray);
        }
        .grades-sidebar::-webkit-scrollbar-thumb {
            background: var(--secondary-teal);
            border-radius: 4px;
        }
        /* Grades list */
        .grades-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #D1D5DB;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s ease;
        }
        .grades-list li:hover {
            background: #E5E7EB;
        }
        .grades-list li .delete-btn {
            background: none;
            border: none;
            color: #DC3545;
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.2s ease;
        }
        .grades-list li .delete-btn:hover {
            color: #B91C1C;
        }
        /* Form inputs */
        .form-control {
            border-radius: 8px;
            border: 1px solid #D1D5DB;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            border-color: var(--secondary-teal);
            box-shadow: 0 0 0 0.2rem var(--shadow-teal);
        }
        /* Buttons */
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
        /* Typography */
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
    <?php include 'sidebar.php'; ?>

    <!-- Floating grades list on the right -->
    <div class="grades-sidebar">
        <h2 class="fw-semibold mb-3">Grades</h2>
        <ul class="grades-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="fade-in">
                        <?php echo htmlspecialchars($row['grade_name']); ?>
                        <form action="add_grade.php" method="post" onsubmit="return confirm('Are you sure you want to delete this grade?');">
                            <input type="hidden" name="grade_name" value="<?php echo htmlspecialchars($row['grade_name']); ?>">
                            <button type="submit" name="delete_grade" class="delete-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li>No grades available</li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Form content in the center -->
    <div class="container">
        <div class="form-container fade-in">
            <h2 class="fw-semibold mb-3">Add New Grade</h2>
            <form action="add_grade.php" method="post">
                <input type="hidden" name="add_grade" value="1">
                <div class="mb-3">
                    <label for="grade_name" class="form-label">Enter the grade:</label>
                    <input type="text" name="grade_name" id="grade_name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-plus me-2"></i> Add Grade
                </button>
            </form>
            <?php if ($errorMessage): ?>
                <p class="error-message"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>