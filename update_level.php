<?php

// update_level.php

if (!isset($_SESSION['user_id'])) {
    // Wenn direkt aufgerufen, sende Fehler
    if (!headers_sent()) {
        http_response_code(403);
        echo json_encode(['error' => 'User not logged in.']);
    }
    exit();
}

$userId = $_SESSION['user_id'];
$usersFile = __DIR__ . '/users_local.json';
$newLevel = null; // Initialisiere $newLevel

require_once 'functions.php'; // Include die calculateLevel-Funktion

$usersData = [];
if (file_exists($usersFile)) {
    $usersData = json_decode(file_get_contents($usersFile), true);
    if ($usersData === null && json_last_error() !== JSON_ERROR_NONE) {
        if (!headers_sent()) {
            http_response_code(500);
            echo json_encode(['error' => 'Error decoding users database: ' . json_last_error_msg()]);
        }
        exit();
    }
}

if (isset($usersData[$userId]['xp'])) {
    $newLevel = calculateLevel($usersData[$userId]['xp']);
    $usersData[$userId]['level'] = $newLevel;

    // Speichere das aktualisierte Level
    if (file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT)) === false) {
        if (!headers_sent()) {
            http_response_code(500);
            echo json_encode(['error' => 'Error writing to users database.']);
        }
        exit();
    }

    // Wenn direkt aufgerufen, sende Erfolg
    if (!headers_sent() && !isset($included)) {
        echo json_encode(['level_updated' => true, 'new_level' => $newLevel]);
    }

} else {
    if (!headers_sent()) {
        http_response_code(404);
        echo json_encode(['error' => 'User data not found.']);
    }
    exit();
}

// Setze eine Flagge, um zu erkennen, dass die Datei eingebunden wurde
$included = true;

?>