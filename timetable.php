<?php
// timetable.php

session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<p>User not logged in. Please <a href='login.php'>log in</a>.</p>";
    exit();
}

$userId = $_SESSION['user_id'];
$timetableFile = __DIR__ . '/timetable.json';
$timetableData = [];

if (file_exists($timetableFile)) {
    $content = file_get_contents($timetableFile);
    $allTimetableData = json_decode($content, true) ?? [];
    if (isset($allTimetableData[$userId])) {
        $timetableData = $allTimetableData[$userId];
    }
}

$timeSlots = ['08:00 - 09:00', '09:00 - 10:00', '10:30 - 11:30', '11:30 - 12:30', '12:30 - 14:00'];
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

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
                                <?php if (isset($timetableData[$timeSlot][$day])): ?>
                                    <span class="subject"><?php echo htmlspecialchars_utf8($timetableData[$timeSlot][$day]['class']); ?></span><br>
                                    <span class="room"><?php echo htmlspecialchars_utf8($timetableData[$timeSlot][$day]['room']); ?></span>
                                <?php elseif ($timeSlot === '10:00 - 10:30'): ?>
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