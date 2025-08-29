<?php
// Include database connection
include '../db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'class') {
    header("Location: login.php");
    exit();
}

// Fetch free slots from the database
$sql = "SELECT day, start_time, end_time FROM admin_free_slots ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), start_time";
$result = $conn->query($sql);

$freeSlots = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $freeSlots[$row['day']][] = [
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Admin Free Days & Time</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4">Admin Free Days & Time</h1>



    <!-- Display free slots -->
    <?php if (!empty($freeSlots)): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Day</th>
                        <th>Time Slots</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($freeSlots as $day => $slots): ?>
                        <tr>
                            <td><?= $day ?></td>
                            <td>
                                <?php foreach ($slots as $slot): ?>
                                    <span class="badge bg-primary me-1">
                                        <?= date('h:i A', strtotime($slot['start_time'])) ?> - <?= date('h:i A', strtotime($slot['end_time'])) ?>
                                    </span>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            No free slots available. Please schedule availability first.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
