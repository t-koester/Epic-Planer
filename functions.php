<?php

function calculateLevel(int $xp): int
{
    if ($xp < 50) {
        return 1;
    }

    $level = 2;
    $requiredXp = 50;

    while ($xp >= $requiredXp) {
        $requiredXp += (10 * ($level - 1)); // +10 XP pro Level über 1
        if ($xp >= $requiredXp) {
            $level++;
        } else {
            break;
        }
    }
    return $level;
}

?>