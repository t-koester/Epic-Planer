<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header('Location: login.php?error=emptyfields');
        exit();
    }

    $usersFile = __DIR__ . '/users_local.json';
    $usersData = @json_decode(file_get_contents($usersFile), true);

    if ($usersData === null && json_last_error() !== JSON_ERROR_NONE) {
 
        header('Location: login.php?error=loginfailed');
        exit();
    }

    $foundUser = false;
    foreach ($usersData as $userId => $userData) {
        if (isset($userData['username']) && $userData['username'] === $username) {
            $foundUser = true;
            if (isset($userData['password']) && password_verify($password, $userData['password'])) {
                // Login erfolgreich
                $_SESSION['user_id'] = $userId;
                $_SESSION['is_logged_in'] = true;
                $_SESSION['username'] = $userData['username'];
                $_SESSION['email'] = $userData['email'] ?? '';
                $_SESSION['level'] = $userData['level'] ?? 1;


                session_regenerate_id(true);

   
                $params = session_get_cookie_params();
                setcookie(session_name(), session_id(), time() + (86400 * 30), $params["path"], $params["domain"], isset($params["secure"]) ? $params["secure"] : false, isset($params["httponly"]) ? $params["httponly"] : true);

                header('Location: index.php');
                exit();
            } else {
                // Falsches Passwort
                header('Location: login.php?error=wrongpassword');
                exit();
            }
        }
    }


    if (!$foundUser) {
        header('Location: login.php?error=usernotfound');
        exit();
    }


    header('Location: login.php?error=loginfailed');
    exit();

} else {

    header('Location: login.php');
    exit();
}
?>