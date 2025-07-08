<?php

/**
 * Calculates a user's level based on their experience points (XP).
 *
 * @param int $xp The total experience points of the user.
 * @return int The calculated level.
 */
function calculateLevel(int $xp): int
{
    // If XP is less than 50, the user is level 1.
    if ($xp < 50) {
        return 1;
    }

    // Initialize level to 2 and required XP for level 2.
    $level = 2;
    $requiredXp = 50;

    // Loop to determine the level based on XP thresholds.
    while ($xp >= $requiredXp) {
        // Calculate the XP required for the next level. The requirement increases by 10 XP for each level above 1.
        $requiredXp += (10 * ($level - 1)); // +10 XP per level above 1
        // If current XP meets or exceeds the new requirement, increment the level.
        if ($xp >= $requiredXp) {
            $level++;
        } else {
            // If XP is not enough for the next level, break the loop.
            break;
        }
    }
    // Return the calculated level.
    return $level;
}

?>