<?php
include '../db_connect.php';

// Reset all records if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $resetQuery = "DELETE FROM admin_free_slots";
    if ($conn->query($resetQuery)) {
        echo "All slots have been reset.";
    } else {
        echo "Error resetting slots: " . $conn->error;
    }
    $conn->close();
    exit;
}

// Save or update records
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['days'])) {
    // Clear existing records for the selected days
    $selectedDays = $_POST['days'];
    $placeholders = implode(',', array_fill(0, count($selectedDays), '?'));
    $stmt = $conn->prepare("DELETE FROM admin_free_slots WHERE day IN ($placeholders)");
    $stmt->bind_param(str_repeat('s', count($selectedDays)), ...$selectedDays);
    $stmt->execute();
    $stmt->close();

    // Insert new time slots for each selected day
    $insertQuery = "INSERT INTO admin_free_slots (day, start_time, end_time) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);

    foreach ($selectedDays as $day) {
        if (isset($_POST['start_time'][$day]) && isset($_POST['end_time'][$day])) {
            $startTimes = $_POST['start_time'][$day];
            $endTimes = $_POST['end_time'][$day];

            for ($i = 0; $i < count($startTimes); $i++) {
                $startTime = $startTimes[$i];
                $endTime = $endTimes[$i];
                $stmt->bind_param("sss", $day, $startTime, $endTime);
                $stmt->execute();
            }
        }
    }

    $stmt->close();
    echo "Slots have been saved successfully.";
}

// Redirect back to the form
$conn->close();
header("Location: ./schedule_availability.php");
exit;
?>
