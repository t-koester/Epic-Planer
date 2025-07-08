<?php

// Start the session to manage user data
session_start();

// Check if the user is logged in; if not, display an error message and exit
if (!isset($_SESSION['user_id'])) {
    echo '<div class="not-logged-in-container">';
    echo '<p class="not-logged-in-message">User not logged in.</p>';
    echo '<p class="not-logged-in-link"><a href="login.php">Please log in</a>.</p>';
    echo '</div>';
    exit();
}

// Get the user ID from the session
$userId = $_SESSION['user_id'];
// Define the path to the user data file
$usersFile = __DIR__ . '/users_local.json';
// Initialize user XP and level
$userXp = 0;
$userLevel = 1;

// Include the 'calculateLevel' function from 'functions.php'
require_once 'functions.php';

// Check if the user data file exists
if (file_exists($usersFile)) {
    // Decode the JSON user data from the file
    $usersData = json_decode(file_get_contents($usersFile), true);
    // If user XP is set, update $userXp
    if (isset($usersData[$userId]['xp'])) {
        $userXp = $usersData[$userId]['xp'];
    }
    // If user level is set, update $userLevel and store it in the session
    if (isset($usersData[$userId]['level'])) {
        $userLevel = $usersData[$userId]['level'];
        $_SESSION['level'] = $userLevel; // Store the level in the session for the header
    }
}

/**
 * Calculates the total experience points required to reach a specific level.
 *
 * @param int $level The target level.
 * @return int The total XP required for the given level.
 */
function getRequiredXpForLevel(int $level): int
{
    // Level 1 requires 0 XP
    if ($level <= 1) {
        return 0;
    }
    // Level 2 requires 50 XP
    if ($level === 2) {
        return 50;
    }
    // Calculate required XP for levels beyond 2
    $requiredXp = 50;
    for ($i = 3; $i <= $level; $i++) {
        $requiredXp += (10 * ($i - 2)); // XP increases by 10 for each level after level 2
    }
    return $requiredXp;
}

// Calculate XP required for the current level
$currentLevelRequiredXp = getRequiredXpForLevel($userLevel);
// Determine the next level
$nextLevel = $userLevel + 1;
// Calculate XP required for the next level
$nextLevelRequiredXp = getRequiredXpForLevel($nextLevel);
// Calculate the XP range for the current level
$xpDifference = $nextLevelRequiredXp - $currentLevelRequiredXp;
// Calculate XP progress within the current level (as a ratio)
$xpProgress = ($xpDifference > 0) ? ($userXp - $currentLevelRequiredXp) / $xpDifference : 1;
// Calculate the width of the progress bar in percentage
$progressBarWidth = round(min(1, max(0, $xpProgress)) * 100) . '%';
// Calculate XP remaining until the next level
$xpToNextLevel = max(0, $nextLevelRequiredXp - $userXp);

?>

<header class="header">
    <div class="logo-name">
        <div>
            <img src="images/book.png" alt="Logo" class="logo">
            <h1 class="name">Study Dashboard</h1>
        </div>
    </div>

    <div class="top-navigation">
        <a href="http://localhost:8000/timetable.php ">Classes</a>
        <a href="http://localhost:8000/Loren.php">Tasks</a>
        <a href="http://localhost:8000/Sinum.php">Notes</a>
        <a href="http://localhost:8000/Impsum.php">Fokus</a>
        <a href="http://localhost:8000/Loren.php">Exams</a>
        <a href="http://localhost:8000/Loren.php">Life</a>
    </div>

    <div class="user-info">
        <img src="images/circle-user-round.png" alt="User Avatar">
        <div class="user-details">
            <span class="username"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Gast'; ?></span>
            <div class="level-info">
                <div class="level-bar-placeholder">
                    <div class="level-progress" style="width: <?php echo $progressBarWidth; ?>;"></div>
                </div>
                <span class="level-placeholder">Level <?php echo $userLevel; ?></span>
            </div>
        </div>
    </div>
</header>