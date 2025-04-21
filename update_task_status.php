<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in.']);
    exit();
}

$userId = $_SESSION['user_id'];
$taskDbFile = __DIR__ . '/task_db.json';
$usersFile = __DIR__ . '/users_local.json';
$taskIndex = isset($_POST['index']) ? intval($_POST['index']) : -1;
$completedStatus = isset($_POST['completed']) ? $_POST['completed'] : 'no';
$xpPerTask = 2.5;

if ($taskIndex < 0 || ($completedStatus !== 'yes' && $completedStatus !== 'no')) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request parameters.']);
    exit();
}

$tasksData = [];
if (file_exists($taskDbFile)) {
    $tasksData = json_decode(file_get_contents($taskDbFile), true);
    if ($tasksData === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(['error' => 'Error decoding task database: ' . json_last_error_msg()]);
        exit();
    }
}

$usersData = [];
if (file_exists($usersFile)) {
    $usersData = json_decode(file_get_contents($usersFile), true);
    if ($usersData === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(['error' => 'Error decoding users database: ' . json_last_error_msg()]);
        exit();
    }
}

if (isset($tasksData[$userId][$taskIndex])) {
    $task = &$tasksData[$userId][$taskIndex];

    $xpUpdated = false;
    $newXp = 0;

    if ($completedStatus === 'yes' && !$task['xp_awarded'] && isset($usersData[$userId])) {
        $earnedXp = round($xpPerTask * 2) / 2;
        $usersData[$userId]['xp'] = isset($usersData[$userId]['xp']) ? $usersData[$userId]['xp'] + $earnedXp : $earnedXp;
        $newXp = $usersData[$userId]['xp'];
        $task['xp_awarded'] = true;
        $xpUpdated = true;

        // Speichere die aktualisierten Benutzer-XP
        if (file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT)) === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error writing to users database (XP update).']);
            exit();
        }
    }

    $task['completed'] = ($completedStatus === 'yes');

    if (file_put_contents($taskDbFile, json_encode($tasksData, JSON_PRETTY_PRINT)) === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Error writing to task database.']);
        exit();
    }

    $response = ['success' => 'Task status updated.'];
    if ($xpUpdated) {
        $response['xp_updated'] = true;
        $response['new_xp'] = $newXp;
    }
    echo json_encode($response);
    exit();

} else {
    http_response_code(404);
    echo json_encode(['error' => 'Task not found.']);
    exit();
}

?>