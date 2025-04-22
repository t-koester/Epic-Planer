<?php

session_start();


if (!isset($_SESSION['user_id'])) {
    echo '<div class="not-logged-in-container">';
    echo '<p class="not-logged-in-message">User not logged in.</p>';
    echo '<p class="not-logged-in-link"><a href="login.php">Please log in</a>.</p>';
    echo '</div>';
    exit();
}

$userId = $_SESSION['user_id'];
$usersFile = __DIR__ . '/users_local.json';
$userXp = 0;
$userLevel = 1;

require_once 'functions.php'; // Include die calculateLevel-Funktion

if (file_exists($usersFile)) {
    $usersData = json_decode(file_get_contents($usersFile), true);
    if (isset($usersData[$userId]['xp'])) {
        $userXp = $usersData[$userId]['xp'];
    }
    if (isset($usersData[$userId]['level'])) {
        $userLevel = $usersData[$userId]['level'];
        $_SESSION['level'] = $userLevel; // Speichere das Level in der Session für den Header
    }
}

function getRequiredXpForLevel(int $level): int
{
    if ($level <= 1) {
        return 0; // Level 1 benötigt keine XP
    }
    if ($level === 2) {
        return 50;
    }
    $requiredXp = 50;
    for ($i = 3; $i <= $level; $i++) {
        $requiredXp += (10 * ($i - 2));
    }
    return $requiredXp;
}

$currentLevelRequiredXp = getRequiredXpForLevel($userLevel);
$nextLevel = $userLevel + 1;
$nextLevelRequiredXp = getRequiredXpForLevel($nextLevel);
$xpDifference = $nextLevelRequiredXp - $currentLevelRequiredXp;
$xpProgress = ($xpDifference > 0) ? ($userXp - $currentLevelRequiredXp) / $xpDifference : 1;
$progressBarWidth = round(min(1, max(0, $xpProgress)) * 100) . '%';
$xpToNextLevel = max(0, $nextLevelRequiredXp - $userXp);

?>

<header class="header">
    <div class="logo-name">
        <div>
            <img src="images/book.png" alt="Logo" class="logo">
            <h1 class="name">   Study Dashboard</h1>
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