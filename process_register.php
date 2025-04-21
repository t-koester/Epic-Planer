<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        die("Bitte fülle alle Felder aus.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Ungültige E-Mail-Adresse.");
    }

    $usersFile = __DIR__ . '/users_local.json';
    $usersData = json_decode(file_get_contents($usersFile), true) ?? [];

    // Überprüfen, ob der Benutzername oder die E-Mail bereits existieren
    foreach ($usersData as $user) {
        if (isset($user['username']) && $user['username'] === $username) {
            die("Benutzername bereits vergeben.");
        }
        if (isset($user['email']) && $user['email'] === $email) {
            die("E-Mail-Adresse bereits registriert.");
        }
    }

    // Neuen Benutzer hinzufügen
    $userId = uniqid(); // Eindeutige Benutzer-ID generieren
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $usersData[$userId] = [
        'id' => $userId,
        'username' => $username,
        'email' => $email, // E-Mail-Adresse speichern
        'password' => $hashedPassword,
        'level' => 1 // Standardlevel
    ];

    file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT));
    header('Location: login.php?registration=success');
    exit();

} else {
    header('Location: register.php');
    exit();
}
?>