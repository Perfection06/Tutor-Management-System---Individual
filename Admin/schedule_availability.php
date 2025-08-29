<?php
// Include database connection
include '../db_connect.php';

// Fetch existing free slots data
$existingSlots = [];
$sql = "SELECT day, start_time, end_time FROM admin_free_slots ORDER BY day, start_time";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $existingSlots[$row['day']][] = [
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time']
        ];
    }
}

// Handle form submission (reset, save/update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset'])) {
        // Reset all records
        $resetQuery = "DELETE FROM admin_free_slots";
        if ($conn->query($resetQuery)) {
            echo "<script>alert('All slots have been reset.'); window.location.href = 'schedule_availability.php';</script>";
        } else {
            echo "<script>alert('Error resetting slots: " . $conn->error . "');</script>";
        }
    } elseif (isset($_POST['days'])) {
        $selectedDays = $_POST['days'];
        $placeholders = implode(',', array_fill(0, count($selectedDays), '?'));

        // Delete records for unchecked days
        $uncheckedDays = array_diff(array_keys($existingSlots), $selectedDays);
        if (!empty($uncheckedDays)) {
            $uncheckedPlaceholders = implode(',', array_fill(0, count($uncheckedDays), '?'));
            $stmt = $conn->prepare("DELETE FROM admin_free_slots WHERE day IN ($uncheckedPlaceholders)");
            $stmt->bind_param(str_repeat('s', count($uncheckedDays)), ...$uncheckedDays);
            $stmt->execute();
            $stmt->close();
        }

        // Delete existing records for the selected days
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
        echo "<script>alert('Slots have been saved successfully.'); window.location.href = 'schedule_availability.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tutor Availability</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom animations for smooth transitions */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        /* Gradient hover effect */
        .btn-gradient {
            background: linear-gradient(to right, var(--primary-blue), var(--accent-teal));
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            background: linear-gradient(to right, var(--accent-teal), var(--primary-blue));
            transform: translateY(-2px);
        }
        /* Custom color palette for tutoring system */
        :root {
            --primary-blue: #1E3A8A; /* Deep blue for trust */
            --secondary-green: #10B981; /* Green for growth */
            --accent-teal: #2DD4BF; /* Teal for engagement */
            --neutral-gray: #F7FAFC; /* Softer gray for background */
            --text-dark: #1A202C; /* Richer dark text */
            --accent-shadow: rgba(45, 212, 191, 0.2); /* Teal shadow for depth */
        }
        /* Ensure form fits within viewport */
        .container {
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--accent-teal) var(--neutral-gray);
        }
        /* Custom scrollbar */
        .container::-webkit-scrollbar {
            width: 8px;
        }
        .container::-webkit-scrollbar-track {
            background: var(--neutral-gray);
        }
        .container::-webkit-scrollbar-thumb {
            background: var(--accent-teal);
            border-radius: 4px;
        }
        /* Card-like day checkboxes */
        .day-checkbox-label {
            transition: all 0.2s ease;
        }
        .day-checkbox:checked + .day-checkbox-label {
            background: var(--accent-teal);
            color: white;
            box-shadow: 0 4px 6px var(--accent-shadow);
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const timeSlotsContainer = document.getElementById('timeSlotsContainer');
            const addTimeSlotButton = document.getElementById('addTimeSlot');
            const form = document.getElementById('adminSlotsForm');

            // Validate time slots on form submission
            function validateTimeSlot(startTime, endTime) {
                if (!startTime || !endTime) return false;
                const start = new Date(`1970-01-01T${startTime}:00`);
                const end = new Date(`1970-01-01T${endTime}:00`);
                return start < end;
            }

            form.addEventListener('submit', function(e) {
                const startTimes = document.querySelectorAll('input[name^="start_time"]');
                const endTimes = document.querySelectorAll('input[name^="end_time"]');
                const selectedDays = document.querySelectorAll('.day-checkbox:checked');
                const timeSlots = document.querySelectorAll('.time-slot-group');

                if (selectedDays.length > 0 && timeSlots.length === 0) {
                    e.preventDefault();
                    showMessage('Please add at least one time slot for the selected days.', 'error');
                    return;
                }

                for (let i = 0; i < startTimes.length; i++) {
                    if (!validateTimeSlot(startTimes[i].value, endTimes[i].value)) {
                        e.preventDefault();
                        showMessage('Start time must be before end time.', 'error');
                        return;
                    }
                }

                // Add loading state
                const submitButton = document.querySelector('button[type="submit"]:not([name="reset"])');
                submitButton.disabled = true;
                submitButton.textContent = 'Saving...';
            });

            // Confirm reset action
            document.querySelector('button[name="reset"]').addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to reset all availability? This cannot be undone.')) {
                    e.preventDefault();
                }
            });

            // Add a new time slot
            addTimeSlotButton.addEventListener('click', () => {
                const selectedDays = document.querySelectorAll('.day-checkbox:checked');
                if (selectedDays.length === 0) {
                    showMessage('Please select at least one day.', 'error');
                    return;
                }

                selectedDays.forEach(day => {
                    const dayName = day.value;
                    let dayContainer = document.querySelector(`.time-slot-group[data-day="${dayName}"]`);

                    if (!dayContainer) {
                        dayContainer = document.createElement('div');
                        dayContainer.classList.add('time-slot-group', 'mb-3', 'fade-in', 'bg-gray-50', 'p-3', 'rounded-lg');
                        dayContainer.setAttribute('data-day', dayName);
                        dayContainer.innerHTML = `<h5 class="text-sm font-semibold text-[var(--text-dark)] mb-2">${dayName}</h5>`;
                        timeSlotsContainer.appendChild(dayContainer);
                    }

                    const slotRow = document.createElement('div');
                    slotRow.classList.add('flex', 'items-center', 'gap-2', 'mb-2', 'fade-in');
                    slotRow.innerHTML = `
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600">Start Time</label>
                            <input type="time" name="start_time[${dayName}][]" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[var(--accent-teal)] text-sm bg-white" required>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600">End Time</label>
                            <input type="time" name="end_time[${dayName}][]" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[var(--accent-teal)] text-sm bg-white" required>
                        </div>
                        <button type="button" class="text-red-500 hover:text-red-700 text-xs remove-time-slot" aria-label="Remove time slot">Remove</button>
                    `;
                    dayContainer.appendChild(slotRow);
                });
            });

            // Remove a specific time slot
            timeSlotsContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-time-slot')) {
                    const row = e.target.closest('.flex');
                    const dayContainer = row.closest('.time-slot-group');
                    row.remove();
                    if (!dayContainer.querySelector('.flex')) {
                        dayContainer.remove();
                    }
                }
            });

            // Show success/error message
            function showMessage(message, type) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `p-3 rounded-lg text-center text-sm fade-in shadow-md ${
                    type === 'error' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                }`;
                messageDiv.innerText = message;
                document.querySelector('.container').prepend(messageDiv);
                setTimeout(() => messageDiv.remove(), 3000);
            }

            // Show message from PHP session if exists
            <?php if (isset($_SESSION['message'])): ?>
                showMessage("<?php echo htmlspecialchars($_SESSION['message']); ?>", 
                    "<?php echo strpos($_SESSION['message'], 'Error') !== false ? 'error' : 'success'; ?>");
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
        });
    </script>
</head>
<body class="bg-[var(--neutral-gray)] font-sans text-[var(--text-dark)]">
    <?php include 'sidebar.php'; ?>
    <div class="min-h-screen flex items-center justify-center p-4 ">
        <div class="container bg-white rounded-xl shadow-2xl max-w-4xl w-full p-8">
            <h1 class="text-3xl font-bold text-center text-[var(--primary-blue)] mb-6 tracking-tight">Manage Tutor Availability</h1>

            <form id="adminSlotsForm" method="POST" action="save_admin_slots.php" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Select Available Days</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <?php
                        $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
                        foreach ($days as $day): ?>
                            <label class="flex items-center">
                                <input class="day-checkbox hidden" type="checkbox" name="days[]" value="<?= $day ?>" id="<?= $day ?>" 
                                    <?= isset($existingSlots[$day]) ? 'checked' : '' ?>>
                                <span class="day-checkbox-label w-full text-center py-2 px-3 rounded-md bg-gray-100 text-sm font-medium text-gray-700 hover:bg-gray-200 cursor-pointer <?= isset($existingSlots[$day]) ? 'bg-[var(--accent-teal)] text-white' : '' ?>">
                                    <?= $day ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="timeSlotsContainer" class="space-y-4">
                    <?php if (!empty($existingSlots)): ?>
                        <?php foreach ($existingSlots as $day => $slots): ?>
                            <div class="time-slot-group bg-gray-50 p-3 rounded-lg mb-3 fade-in" data-day="<?= $day ?>">
                                <h5 class="text-sm font-semibold text-[var(--text-dark)] mb-2"><?= $day ?></h5>
                                <?php foreach ($slots as $slot): ?>
                                    <div class="flex items-center gap-3 mb-2 fade-in">
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-600">Start Time</label>
                                            <input type="time" name="start_time[<?= $day ?>][]" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[var(--accent-teal)] text-sm bg-white" value="<?= htmlspecialchars($slot['start_time']) ?>" required>
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-600">End Time</label>
                                            <input type="time" name="end_time[<?= $day ?>][]" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[var(--accent-teal)] text-sm bg-white" value="<?= htmlspecialchars($slot['end_time']) ?>" required>
                                        </div>
                                        <button type="button" class="text-red-500 hover:text-red-700 text-xs font-medium remove-time-slot" aria-label="Remove time slot">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="flex justify-center gap-3">
                    <button type="button" id="addTimeSlot" class="btn-gradient text-white px-5 py-2 rounded-md text-sm font-medium shadow-md">Add Time Slot</button>
                    <button type="submit" class="bg-[var(--primary-blue)] text-white px-5 py-2 rounded-md hover:bg-blue-800 text-sm font-medium shadow-md transition-colors">Save Changes</button>
                    <button type="submit" name="reset" class="bg-red-500 text-white px-5 py-2 rounded-md hover:bg-red-600 text-sm font-medium shadow-md transition-colors">Reset All</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>