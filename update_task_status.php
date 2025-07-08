<?php

// Start the session to access user data.
session_start();

// Check if the user is logged in. If not, return a 403 Forbidden status and an error message.
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Set HTTP status code to 403 (Forbidden).
    echo json_encode(['error' => 'User not logged in.']); // Send JSON error message.
    exit(); // Stop script execution.
}

// Get the user ID from the session.
$userId = $_SESSION['user_id'];
// Define file paths for the task database and user database.
$taskDbFile = __DIR__ . '/task_db.json';
$usersFile = __DIR__ . '/users_local.json';

// Get and validate task index and completion status from the POST request.
// `intval()` ensures the index is an integer; default to -1 if not set.
$taskIndex = isset($_POST['index']) ? intval($_POST['index']) : -1;
// Get completed status; default to 'no' if not set.
$completedStatus = isset($_POST['completed']) ? $_POST['completed'] : 'no';

// Define the amount of experience points (XP) awarded per task.
$xpPerTask = 2.5;

// Validate input parameters. If invalid, return a 400 Bad Request status and an error message.
if ($taskIndex < 0 || ($completedStatus !== 'yes' && $completedStatus !== 'no')) {
    http_response_code(400); // Set HTTP status code to 400 (Bad Request).
    echo json_encode(['error' => 'Invalid request parameters.']); // Send JSON error message.
    exit(); // Stop script execution.
}

// Initialize task data array.
$tasksData = [];
// Check if the task database file exists.
if (file_exists($taskDbFile)) {
    // Read and decode the task data from the JSON file.
    $tasksData = json_decode(file_get_contents($taskDbFile), true);
    // Handle JSON decoding errors. If an error occurs, return a 500 Internal Server Error.
    if ($tasksData === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500); // Set HTTP status code to 500.
        echo json_encode(['error' => 'Error decoding task database: ' . json_last_error_msg()]);
        exit();
    }
}

// Initialize user data array.
$usersData = [];
// Check if the user database file exists.
if (file_exists($usersFile)) {
    // Read and decode the user data from the JSON file.
    $usersData = json_decode(file_get_contents($usersFile), true);
    // Handle JSON decoding errors. If an error occurs, return a 500 Internal Server Error.
    if ($usersData === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500); // Set HTTP status code to 500.
        echo json_encode(['error' => 'Error decoding users database: ' . json_last_error_msg()]);
        exit();
    }
}

// Check if the task exists for the current user at the given index.
if (isset($tasksData[$userId][$taskIndex])) {
    // Get a reference to the specific task for easier modification.
    $task = &$tasksData[$userId][$taskIndex];

    // Initialize flags and variables for XP and level updates.
    $xpUpdated = false;
    $newXp = 0;
    $newLevel = null; // New level, if updated.

    // If the task is being marked as 'completed' and XP has not already been awarded:
    if ($completedStatus === 'yes' && !$task['xp_awarded'] && isset($usersData[$userId])) {
        // Calculate earned XP (rounded to nearest 0.5).
        $earnedXp = round($xpPerTask * 2) / 2;
        // Add earned XP to the user's total XP. Initialize if not set.
        $usersData[$userId]['xp'] = isset($usersData[$userId]['xp']) ? $usersData[$userId]['xp'] + $earnedXp : $earnedXp;
        $newXp = $usersData[$userId]['xp']; // Store the new total XP.
        $task['xp_awarded'] = true; // Mark XP as awarded for this task.
        $xpUpdated = true; // Set flag to indicate XP was updated.

        // Save the updated user data (with new XP) back to the users JSON file.
        // If saving fails, return a 500 Internal Server Error.
        if (file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT)) === false) {
            http_response_code(500); // Set HTTP status code to 500.
            echo json_encode(['error' => 'Error writing to users database (XP update).']);
            exit();
        }

        // Attempt to call the update_level.php script to re-calculate and update the user's level.
        // Using @ suppresses warnings/errors if the file_get_contents fails (e.g., file not found).
        $levelUpdateResponse = @file_get_contents('update_level.php');
        // If the level update script returned a response, decode it.
        if ($levelUpdateResponse !== FALSE) {
            $levelData = json_decode($levelUpdateResponse, true);
            // Get the new level from the response, if available.
            $newLevel = $levelData['new_level'] ?? null;
        }
    }

    // Update the task's completion status in the tasks data.
    $task['completed'] = ($completedStatus === 'yes');

    // Save the updated task data back to the tasks JSON file.
    // If saving fails, return a 500 Internal Server Error.
    if (file_put_contents($taskDbFile, json_encode($tasksData, JSON_PRETTY_PRINT)) === false) {
        http_response_code(500); // Set HTTP status code to 500.
        echo json_encode(['error' => 'Error writing to task database.']);
        exit();
    }

    // Prepare the success response.
    $response = ['success' => 'Task status updated.'];
    // Add XP and level update details if XP was awarded.
    if ($xpUpdated) {
        $response['xp_updated'] = true;
        $response['new_xp'] = $newXp;
        if ($newLevel !== null) {
            $response['new_level'] = $newLevel;
        }
    }
    // Send the JSON success response.
    echo json_encode($response);
    exit(); // Stop script execution.

} else {
    // If the task was not found for the user, return a 404 Not Found status and an error message.
    http_response_code(404); // Set HTTP status code to 404.
    echo json_encode(['error' => 'Task not found.']); // Send JSON error message.
    exit(); // Stop script execution.
}

?>