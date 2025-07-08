<?php
// Start the session to manage user data
session_start();

// Check if the user is logged in. If not, display a message and exit.
if (!isset($_SESSION['user_id'])) {
    echo "<p>User not logged in. Please <a href='login.php'>log in</a>.</p>";
    exit();
}

// Get the user's ID from the session
$userId = $_SESSION['user_id'];

// Define available time slots and days of the week for the timetable
$timeSlots = ['08:00 - 09:00', '09:00 - 10:00', '10:30 - 11:30', '11:30 - 12:30', '12:30 - 14:00'];
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
// Define the path to the timetable data file
$timetableFile = __DIR__ . '/timetable.json';
// Initialize an empty array to hold existing timetable data
$existingTimetableData = [];

// If the timetable file exists, read its content and decode the JSON data
if (file_exists($timetableFile)) {
    $content = file_get_contents($timetableFile);
    $existingTimetableData = json_decode($content, true) ?? []; // Decode, or set to empty array if decoding fails
}

// Get the current user's timetable data, or an empty array if none exists
$userTimetable = $existingTimetableData[$userId] ?? [];

// Process form submission if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $timetableData = []; // Initialize an empty array for the new timetable data
    // Loop through each time slot and day to collect class and room information from POST data
    foreach ($timeSlots as $timeSlot) {
        foreach ($daysOfWeek as $day) {
            // Construct the input field names based on day and time slot
            $classInputName = $day . '_' . str_replace([':', ' ', '-'], '_', $timeSlot) . '_class';
            $roomInputName = $day . '_' . str_replace([':', ' ', '-'], '_', $timeSlot) . '_room';

            // Retrieve class and room values, defaulting to an empty string if not set
            $class = $_POST[$classInputName] ?? '';
            $room = $_POST[$roomInputName] ?? '';

            // If a class is provided, store it along with the room in the timetable data
            if (!empty($class)) {
                $timetableData[$timeSlot][$day] = ['class' => $class, 'room' => $room];
            }
        }
    }

    // Update the existing timetable data with the current user's new timetable
    $existingTimetableData[$userId] = $timetableData;

    // Attempt to save the updated timetable data back to the JSON file
    if (file_put_contents($timetableFile, json_encode($existingTimetableData, JSON_PRETTY_PRINT))) {
        echo '<p style="color: green;">Your timetable has been saved!</p>'; // Success message
    } else {
        echo '<p style="color: red;">Error saving timetable.</p>'; // Error message
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Your Classes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="setup-classes-container">
        <h2>Edit Your Weekly Classes</h2>
        <form method="post">
            <table class="setup-timetable">
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
                                <td>
                                    <?php
                                    // Get existing class and room values for the current time slot and day
                                    $classValue = $userTimetable[$timeSlot][$day]['class'] ?? '';
                                    $roomValue = $userTimetable[$timeSlot][$day]['room'] ?? '';
                                    ?>
                                    <input type="text" name="<?php echo htmlspecialchars($day . '_' . str_replace([':', ' ', '-'], '_', $timeSlot) . '_class'); ?>" placeholder="Class" value="<?php echo htmlspecialchars($classValue); ?>">
                                    <input type="text" name="<?php echo htmlspecialchars($day . '_' . str_replace([':', ' ', '-'], '_', $timeSlot) . '_room'); ?>" placeholder="Room" value="<?php echo htmlspecialchars($roomValue); ?>">
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">
                            <button type="submit" class="save-classes-button">Save Classes</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <p><a href="timetable.php">View Your Timetable</a></p>
        <p><a href="index.php">Back to Dashboard</a></p>
    </div>
</body>
</html>