<?php

// Start the session to manage user data
session_start();

// Check if the user is logged in; if not, display an error and exit
if (!isset($_SESSION['user_id'])) {
    echo '<div class="error-message">';
    echo '<p class="error-title">Fehler</p>';
    echo '<p>User not logged in. Please <a href="login.php">log in</a>.</p>';
    echo '</div>';
    exit();
}

// Get the user ID and define the task database file path
$userId = $_SESSION['user_id'];
$taskDbFile = __DIR__ . '/task_db.json';
// Retrieve and sanitize the new task description from the POST request
$newTaskDescription = isset($_POST['task']) ? trim($_POST['task']) : '';

// If the task description is empty, return an error and exit
if (empty($newTaskDescription)) {
    http_response_code(400);
    echo json_encode(['error' => 'Task description cannot be empty.']);
    exit();
}

// Initialize task data and load it from the database file if it exists
$tasksData = [];
if (file_exists($taskDbFile)) {
    $tasksData = json_decode(file_get_contents($taskDbFile), true);
    // Handle JSON decoding errors
    if ($tasksData === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(['error' => 'Error decoding task database.']);
        exit();
    }
}

// If no tasks exist for the current user, initialize an empty array for them
if (!isset($tasksData[$userId])) {
    $tasksData[$userId] = [];
}

// Create a new task array with sanitized description, completion status, and creation timestamp
$newTask = [
    'description' => htmlspecialchars($newTaskDescription),
    'completed' => false,
    'created_at' => date('Y-m-d H:i:s')
];

// Add the new task to the user's task list
$tasksData[$userId][] = $newTask;

// Attempt to save the updated task data back to the JSON file
if (file_put_contents($taskDbFile, json_encode($tasksData, JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Error writing to task database.']);
    exit();
}

// Return a success message if the task was added successfully
echo json_encode(['success' => 'Task added successfully.']);

?>