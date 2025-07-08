<?php
// Start the session to manage user data
session_start();

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve username and password from the POST data
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Check if any fields are empty
    if (empty($username) || empty($password)) {
        header('Location: login.php?error=emptyfields'); // Redirect with an error
        exit(); // Stop script execution
    }

    // Define the path to the user data file
    $usersFile = __DIR__ . '/users_local.json';
    // Attempt to read and decode the user data from the JSON file. Suppress errors with '@'.
    $usersData = @json_decode(file_get_contents($usersFile), true);

    // Check if there was an error decoding the JSON data
    if ($usersData === null && json_last_error() !== JSON_ERROR_NONE) {
        header('Location: login.php?error=loginfailed'); // Redirect with a generic login failed error
        exit(); // Stop script execution
    }

    $foundUser = false; // Flag to track if the user is found
    // Iterate through each user in the $usersData array
    foreach ($usersData as $userId => $userData) {
        // Check if the username exists and matches the provided username
        if (isset($userData['username']) && $userData['username'] === $username) {
            $foundUser = true; // Set flag to true as user is found
            // Verify the provided password against the stored hashed password
            if (isset($userData['password']) && password_verify($password, $userData['password'])) {
                // Login successful: Set session variables
                $_SESSION['user_id'] = $userId;
                $_SESSION['is_logged_in'] = true;
                $_SESSION['username'] = $userData['username'];
                $_SESSION['email'] = $userData['email'] ?? ''; // Set email, or empty string if not present
                $_SESSION['level'] = $userData['level'] ?? 1; // Set level, default to 1 if not present

                // Regenerate session ID for security
                session_regenerate_id(true);

                // Set session cookie parameters for extended duration (30 days)
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    session_id(),
                    time() + (86400 * 30), // Expires in 30 days
                    $params["path"],
                    $params["domain"],
                    isset($params["secure"]) ? $params["secure"] : false,
                    isset($params["httponly"]) ? $params["httponly"] : true
                );

                header('Location: index.php'); // Redirect to the dashboard
                exit(); // Stop script execution
            } else {
                // Incorrect password: Redirect with an error
                header('Location: login.php?error=wrongpassword');
                exit(); // Stop script execution
            }
        }
    }

    // If the user was not found after iterating through all users
    if (!$foundUser) {
        header('Location: login.php?error=usernotfound'); // Redirect with an error
        exit(); // Stop script execution
    }

    // Fallback: If for some reason login fails without specific error
    header('Location: login.php?error=loginfailed');
    exit(); // Stop script execution

} else {
    // If the request method is not POST, redirect to the login page
    header('Location: login.php');
    exit(); // Stop script execution
}
?>