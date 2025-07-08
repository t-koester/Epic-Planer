<?php
// Start the session to manage user data across pages.
session_start();

// Check if the user is logged in. If not, display a message and stop execution.
if (!isset($_SESSION['user_id'])) {
    echo "<p>User not logged in. Please <a href='login.php'>log in</a>.</p>";
    exit();
}

// Get the unique user ID from the session.
$userId = $_SESSION['user_id'];
// Define the path to the JSON file where timetable data is stored.
$timetableFile = __DIR__ . '/timetable.json';
// Initialize an empty array to hold the current user's timetable data.
$timetableData = [];

// Check if the timetable data file exists.
if (file_exists($timetableFile)) {
    // Read the file content and decode the JSON data into a PHP array.
    $content = file_get_contents($timetableFile);
    $allTimetableData = json_decode($content, true) ?? []; // Use null coalescing to ensure it's an array if decoding fails.
    // If data exists for the current user ID, assign it to $timetableData.
    if (isset($allTimetableData[$userId])) {
        $timetableData = $allTimetableData[$userId];
    }
}

// Define the standard time slots for the timetable.
$timeSlots = ['08:00 - 09:00', '09:00 - 10:00', '10:30 - 11:30', '11:30 - 12:30', '12:30 - 14:00'];
// Define the days of the week for the timetable.
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

// Helper function to safely escape HTML special characters.
// This is crucial for preventing Cross-Site Scripting (XSS) attacks when displaying user-generated content.
function htmlspecialchars_utf8($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Timetable</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="timetable-container">
        <h2 class="timetable-title">Your Timetable</h2>
        <table class="timetable">
            <thead>
                <tr>
                    <th>Time</th>
                    <?php foreach ($daysOfWeek as $day): ?>
                        <th><?php echo htmlspecialchars($day); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($timeSlots as $timeSlot): ?>
                    <tr>
                        <td class="time-slot"><?php echo htmlspecialchars($timeSlot); ?></td>
                        <?php foreach ($daysOfWeek as $day): ?>
                            <td class="day">
                                <?php
                                // Check if there's class data for the current time slot and day.
                                if (isset($timetableData[$timeSlot][$day])): ?>
                                    <span class="subject"><?php echo htmlspecialchars_utf8($timetableData[$timeSlot][$day]['class']); ?></span><br>
                                    <span class="room"><?php echo htmlspecialchars_utf8($timetableData[$timeSlot][$day]['room']); ?></span>
                                <?php
                                // Special case: If it's the specific break time slot, display "Break".
                                elseif ($timeSlot === '10:00 - 10:30'): ?>
                                    <span class="break">Break</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><a href="setup_classes.php">Edit Your Classes</a></p>
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>