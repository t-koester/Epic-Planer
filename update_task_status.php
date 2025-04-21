<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in.']);
    exit();
}

$userId = $_SESSION['user_id'];
$taskDbFile = __DIR__ . '/task_db.json';
$taskIndex = isset($_POST['index']) ? intval($_POST['index']) : -1;
$completedStatus = isset($_POST['completed']) ? $_POST['completed'] : 'no';

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

if (isset($tasksData[$userId][$taskIndex])) {
    $tasksData[$userId][$taskIndex]['completed'] = ($completedStatus === 'yes');

    if (file_put_contents($taskDbFile, json_encode($tasksData, JSON_PRETTY_PRINT)) === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Error writing to task database.']);
        exit();
    }

    echo json_encode(['success' => 'Task status updated.']);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Task not found.']);
    exit();
}

?>