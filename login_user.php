<?php


session_start();

$localUsersFile = __DIR__ . '/users_local.json';
$defaultLoginUserId = 'user1';
$defaultLoginPassword = 'user1';


if (file_exists($localUsersFile)) {
    $usersData = json_decode(file_get_contents($localUsersFile), true);
} else {
    $usersData = [];
    echo "Fehler: Lokale Benutzerdatei '$localUsersFile' nicht gefunden.\n";
}


if (isset($usersData[$defaultLoginUserId]) && isset($usersData[$defaultLoginUserId]['password']) && $usersData[$defaultLoginUserId]['password'] === $defaultLoginPassword) {

    $_SESSION['user_id'] = $defaultLoginUserId;
    $_SESSION['is_logged_in'] = true;


    if (isset($usersData[$defaultLoginUserId]['username'])) {
        $_SESSION['username'] = $usersData[$defaultLoginUserId]['username'];
    } else {

        $_SESSION['username'] = $defaultLoginUserId;
    }


    if (isset($usersData[$defaultLoginUserId]['level'])) {
        $_SESSION['level'] = $usersData[$defaultLoginUserId]['level'];
    }

} else {

    unset($_SESSION['user_id']);
    unset($_SESSION['is_logged_in']);
    unset($_SESSION['username']);
    unset($_SESSION['level']); 
}
