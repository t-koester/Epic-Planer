<?php

// update_level.php

// This script is responsible for calculating and updating a user's level based on their XP.
// It's designed to be called via AJAX or included by another PHP script.

// Check if the user is logged in by verifying the 'user_id' in the session.
// If not logged in and headers haven't been sent yet, send a 403 Forbidden status
// and an error message in JSON format, then exit.
if (!isset($_SESSION['user_id'])) {
    if (!headers_sent()) {
        http_response_code(403);
        echo json_encode(['error' => 'User not logged in.']);
    }
    exit();
}

// Get the current user's ID from the session.
$userId = $_SESSION['user_id'];
// Define the path to the user data file.
$usersFile = __DIR__ . '/users_local.json';
// Initialize $newLevel to null; it will store the calculated level.
$newLevel = null;

// Include the 'functions.php' file, which is expected to contain the 'calculateLevel' function.
require_once 'functions.php';

// Initialize an empty array to hold user data.
$usersData = [];
// Check if the user data file exists.
if (file_exists($usersFile)) {
    // Read the content of the file and decode it from JSON into a PHP array.
    $usersData = json_decode(file_get_contents($usersFile), true);
    // If decoding fails (returns null and there's a JSON error), send a 500 Internal Server Error
    // and an error message, then exit.
    if ($usersData === null && json_last_error() !== JSON_ERROR_NONE) {
        if (!headers_sent()) {
            http_response_code(500);
            echo json_encode(['error' => 'Error decoding users database: ' . json_last_error_msg()]);
        }
        exit();
    }
}

// Check if the user's XP data exists.
if (isset($usersData[$userId]['xp'])) {
    // Calculate the new level using the 'calculateLevel' function and the user's current XP.
    $newLevel = calculateLevel($usersData[$userId]['xp']);
    // Update the user's level in the $usersData array.
    $usersData[$userId]['level'] = $newLevel;

    // Attempt to save the updated user data back to the JSON file.
    // If writing to the file fails, send a 500 Internal Server Error and an error message, then exit.
    if (file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT)) === false) {
        if (!headers_sent()) {
            http_response_code(500);
            echo json_encode(['error' => 'Error writing to users database.']);
        }
        exit();
    }

    // If the script was called directly (not included by another script) and headers haven't been sent,
    // send a success JSON response with the update status and the new level.
    // The `!isset($included)` check ensures this response is only sent when this script is the primary handler.
    if (!headers_sent() && !isset($included)) {
        echo json_encode(['level_updated' => true, 'new_level' => $newLevel]);
    }

} else {
    // If the user's XP data is not found, send a 404 Not Found status and an error message, then exit.
    if (!headers_sent()) {
        http_response_code(404);
        echo json_encode(['error' => 'User data not found.']);
    }
    exit();
}

// Set a flag to indicate that this file has been included. This helps prevent sending
// duplicate JSON responses if this script is included by another file that also sends output.
$included = true;

?>