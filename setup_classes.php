<?php
// setup_classes.php

session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<p>User not logged in. Please <a href='login.php'>log in</a>.</p>";
    exit();
}

$userId = $_SESSION['user_id'];

$timeSlots = ['08:00 - 09:00', '09:00 - 10:00', '10:30 - 11:30', '11:30 - 12:30', '12:30 - 14:00'];
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$timetableFile = __DIR__ . '/timetable.json';
$existingTimetableData = [];

if (file_exists($timetableFile)) {
    $content = file_get_contents($timetableFile);
    $existingTimetableData = json_decode($content, true) ?? [];
}

$userTimetable = $existingTimetableData[$userId] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $timetableData = [];
    foreach ($timeSlots as $timeSlot) {
        foreach ($daysOfWeek as $day) {
            $class = $_POST[$day . '_' . str_replace([':', ' ', '-'], '_', $timeSlot) . '_class'] ?? '';
            $room = $_POST[$day . '_' . str_replace([':', ' ', '-'], '_', $timeSlot) . '_room'] ?? '';
            if (!empty($class)) {
                $timetableData[$timeSlot][$day] = ['class' => $class, 'room' => $room];
            }
        }
    }

    $existingTimetableData[$userId] = $timetableData;

    if (file_put_contents($timetableFile, json_encode($existingTimetableData, JSON_PRETTY_PRINT))) {
        echo '<p style="color: green;">Your timetable has been saved!</p>';
    } else {
        echo '<p style="color: red;">Error saving timetable.</p>';
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