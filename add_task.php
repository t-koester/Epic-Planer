<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in.']);
    exit();
}

$userId = $_SESSION['user_id'];
$taskDbFile = __DIR__ . '/task_db.json';
$newTaskDescription = isset($_POST['task']) ? trim($_POST['task']) : '';

if (empty($newTaskDescription)) {
    http_response_code(400);
    echo json_encode(['error' => 'Task description cannot be empty.']);
    exit();
}

$tasksData = [];
if (file_exists($taskDbFile)) {
    $tasksData = json_decode(file_get_contents($taskDbFile), true);
    if ($tasksData === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(['error' => 'Error decoding task database.']);
        exit();
    }
}

if (!isset($tasksData[$userId])) {
    $tasksData[$userId] = [];
}

$newTask = [
    'description' => htmlspecialchars($newTaskDescription),
    'completed' => false,
    'created_at' => date('Y-m-d H:i:s')
];

$tasksData[$userId][] = $newTask;

if (file_put_contents($taskDbFile, json_encode($tasksData, JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Error writing to task database.']);
    exit();
}

echo json_encode(['success' => 'Task added successfully.']);

?>