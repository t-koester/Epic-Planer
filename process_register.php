<?php
// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve username, email, and password from POST data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate that no fields are empty
    if (empty($username) || empty($email) || empty($password)) {
        die("Bitte fülle alle Felder aus."); // Stop execution and display error
    }

    // Validate the email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Ungültige E-Mail-Adresse."); // Stop execution and display error
    }

    // Define the path to the user data file
    $usersFile = __DIR__ . '/users_local.json';
    // Read and decode existing user data; if file doesn't exist or is empty/invalid, initialize as an empty array
    $usersData = json_decode(file_get_contents($usersFile), true) ?? [];

    // Check if the username or email already exist in the database
    foreach ($usersData as $user) {
        if (isset($user['username']) && $user['username'] === $username) {
            die("Benutzername bereits vergeben."); // Stop execution and display error
        }
        if (isset($user['email']) && $user['email'] === $email) {
            die("E-Mail-Adresse bereits registriert."); // Stop execution and display error
        }
    }

    // Add the new user to the data
    $userId = uniqid(); // Generate a unique ID for the new user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password for security
    $usersData[$userId] = [
        'id' => $userId,
        'username' => $username,
        'email' => $email, // Store the email address
        'password' => $hashedPassword,
        'level' => 1 // Set default level for new users
    ];

    // Save the updated user data back to the JSON file with pretty printing
    file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT));
    // Redirect to the login page with a success message
    header('Location: login.php?registration=success');
    exit(); // Stop script execution

} else {
    // If the request method is not POST, redirect to the registration page
    header('Location: register.php');
    exit(); // Stop script execution
}
?>