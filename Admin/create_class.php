<?php
session_start();
// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection
include('../db_connect.php');

$msg = "";

// Handle class creation, updates, deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_class'])) {
        $id = $_POST['id'] ?? "";
        $grade = $_POST['grade'];
        $subject = $_POST['subject'];
        $title = $_POST['title'];
        $fee = $_POST['fee'];

        if ($id) {
            // --- Update class ---
            $stmt = $conn->prepare("UPDATE classes SET grade=?, subject=?, title=?, fee=? WHERE id=?");
            $stmt->bind_param("ssssi", $grade, $subject, $title, $fee, $id);
            $stmt->execute();

            // delete old schedule
            $delStmt = $conn->prepare("DELETE FROM class_days_times WHERE class_id=?");
            $delStmt->bind_param("i", $id);
            $delStmt->execute();

            // insert new schedule
            if (isset($_POST['days'])) {
                foreach ($_POST['days'] as $key => $day) {
                    $start = $_POST['start_time'][$key];
                    $end = $_POST['end_time'][$key];
                    $stmtDay = $conn->prepare("INSERT INTO class_days_times (class_id, day, start_time, end_time) VALUES (?,?,?,?)");
                    $stmtDay->bind_param("isss", $id, $day, $start, $end);
                    $stmtDay->execute();
                }
            }

            $msg = "Class updated successfully!";
            header("Location: create_class.php");
            exit();
        } else {
            // --- Create class ---
            $stmt = $conn->prepare("INSERT INTO classes (grade, subject, title, fee) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $grade, $subject, $title, $fee);
            $stmt->execute();
            $classId = $stmt->insert_id;

            if (isset($_POST['days'])) {
                foreach ($_POST['days'] as $key => $day) {
                    $start = $_POST['start_time'][$key];
                    $end = $_POST['end_time'][$key];
                    $stmtDay = $conn->prepare("INSERT INTO class_days_times (class_id, day, start_time, end_time) VALUES (?,?,?,?)");
                    $stmtDay->bind_param("isss", $classId, $day, $start, $end);
                    $stmtDay->execute();
                }
            }
            $msg = "Class created successfully!";
            header("Location: create_class.php");
            exit();
        }
    } elseif (isset($_POST['delete_class'])) {
        $id = $_POST['id'];

        $conn->query("DELETE FROM class_days_times WHERE class_id=$id");
        $conn->query("DELETE FROM classes WHERE id=$id");

        $msg = "Class deleted successfully!";
    }
}

// Fetch grades and subjects
$gradesResult = $conn->query("SELECT * FROM grades");
$subjectsResult = $conn->query("SELECT * FROM subjects");

// If editing (via GET id)
$editData = null;
if (isset($_GET['id'])) {
    $classId = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM classes WHERE id=$classId");
    $editData = $res->fetch_assoc();

    // fetch schedule
    $daysRes = $conn->query("SELECT * FROM class_days_times WHERE class_id=$classId");
    $editData['schedule'] = $daysRes->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Class</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
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
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px var(--shadow-teal);
        }

        .form-container {
            background: var(--neutral-gray);
            border-radius: 16px;
            box-shadow: 0 6px 12px var(--shadow-teal);
            padding: 2.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .form-container:hover {
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

        .btn-danger {
            background: #DC3545;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background: #B91C1C;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(220, 53, 69, 0.3);
        }

        .btn-success {
            background: var(--accent-green);
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(16, 185, 129, 0.3);
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

        .card h5 {
            font-weight: 600;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        .card:hover h5 {
            color: var(--secondary-teal);
        }

        .card p {
            color: #4B5563;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .card:hover p {
            color: var(--text-dark);
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

        /* Lazy loading for cards */
        .card {
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .card.visible {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-4">
    <h1 class="mb-4 text-center fw-bold fade-in"><?= $editData ? "Edit Class" : "Create Class" ?></h1>
    <a href="admin_dashboard.php" class="btn btn-secondary back-btn mb-3"><i class="fas fa-arrow-left"></i> Back</a>

    <?php if ($msg): ?>
        <div class="alert alert-info fade-in"><?= $msg ?></div>
    <?php endif; ?>

    <!-- unified form -->
    <div class="card p-4 shadow form-container">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">

            <div class="row mb-3">
                <div class="col-md-6 form-group">
                    <label class="form-label">Grade</label>
                    <select name="grade" class="form-select" required>
                        <option value="">Select Grade</option>
                        <?php while ($row = $gradesResult->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>" <?= ($editData && $editData['grade']==$row['id'])?"selected":"" ?>>
                                <?= htmlspecialchars($row['grade_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label class="form-label">Subject</label>
                    <select name="subject" class="form-select" required>
                        <option value="">Select Subject</option>
                        <?php while ($row = $subjectsResult->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>" <?= ($editData && $editData['subject']==$row['id'])?"selected":"" ?>>
                                <?= htmlspecialchars($row['subject_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3 form-group">
                <label class="form-label">Class Title</label>
                <input type="text" name="title" class="form-control" value="<?= $editData['title'] ?? '' ?>" required>
            </div>

            <div class="mb-3 form-group">
                <label class="form-label">Fee (Rs.)</label>
                <input type="number" name="fee" class="form-control" value="<?= $editData['fee'] ?? '' ?>" required>
            </div>

            <div class="mb-3 form-group">
                <label class="form-label">Schedule</label>
                <div id="scheduleContainer">
                    <?php if ($editData && !empty($editData['schedule'])): ?>
                        <?php foreach ($editData['schedule'] as $sch): ?>
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <select name="days[]" class="form-select" required>
                                    <?php foreach (["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"] as $day): ?>
                                        <option value="<?= $day ?>" <?= ($sch['day']==$day)?"selected":"" ?>><?= $day ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="time" name="start_time[]" class="form-control" value="<?= $sch['start_time'] ?>" required>
                            </div>
                            <div class="col-md-3">
                                <input type="time" name="end_time[]" class="form-control" value="<?= $sch['end_time'] ?>" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger" onclick="this.closest('.row').remove()">X</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-sm btn-success mt-2" onclick="addScheduleRow()">+ Add Day</button>
            </div>

            <button type="submit" name="save_class" class="btn btn-primary w-100"><?= $editData ? "Update Class" : "Create Class" ?></button>
        </form>
    </div>

    <!-- list of existing classes -->
    <h2 class="mt-5 fade-in">Existing Classes</h2>
    <div class="row">
        <?php
        $result = $conn->query("SELECT c.*, g.grade_name, s.subject_name FROM classes c 
                                JOIN grades g ON c.grade=g.id 
                                JOIN subjects s ON c.subject=s.id");
        while ($row = $result->fetch_assoc()):
            $days = $conn->query("SELECT * FROM class_days_times WHERE class_id=".$row['id']);
            $scheduleText = [];
            while ($d = $days->fetch_assoc()) {
                $scheduleText[] = $d['day']." (".date("g:i A", strtotime($d['start_time']))." - ".date("g:i A", strtotime($d['end_time'])).")";
            }
        ?>
        <div class="col-md-4 mb-3">
            <div class="card p-3">
                <h5><?= htmlspecialchars($row['title']) ?> <small>(<?= $row['grade_name'] ?> - <?= $row['subject_name'] ?>)</small></h5>
                <p><b>Fee:</b> Rs. <?= $row['fee'] ?></p>
                <p><b>Schedule:</b> <?= implode(", ", $scheduleText) ?: "No schedule" ?></p>
                <a href="create_class.php?id=<?= $row['id'] ?>" class="btn btn-secondary w-100 mb-2"><i class="fas fa-edit"></i> Edit</a>
                <form method="POST" onsubmit="return confirm('Delete this class?')">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button type="submit" name="delete_class" class="btn btn-danger w-100"><i class="fas fa-trash"></i> Delete</button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
function addScheduleRow() {
    const container = document.getElementById("scheduleContainer");
    container.insertAdjacentHTML("beforeend", `
        <div class="row mb-2 fade-in">
            <div class="col-md-4">
                <select name="days[]" class="form-select" required>
                    <option value="">Select Day</option>
                    <option>Monday</option>
                    <option>Tuesday</option>
                    <option>Wednesday</option>
                    <option>Thursday</option>
                    <option>Friday</option>
                    <option>Saturday</option>
                    <option>Sunday</option>
                </select>
            </div>
            <div class="col-md-3"><input type="time" name="start_time[]" class="form-control" required></div>
            <div class="col-md-3"><input type="time" name="end_time[]" class="form-control" required></div>
            <div class="col-md-2"><button type="button" class="btn btn-danger" onclick="this.closest('.row').remove()">X</button></div>
        </div>
    `);
}

// Lazy load cards for performance
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