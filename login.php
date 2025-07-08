<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="auth-container login-container">
        <h1 class="auth-title">Login</h1>
        <?php
        // Check if an error message is present in the URL query string
        if (isset($_GET['error'])) {
            $error = htmlspecialchars($_GET['error']); // Sanitize the error message
            // Display specific error messages based on the 'error' parameter
            if ($error === 'emptyfields') {
                echo '<p class="error-message">Bitte fülle alle Felder aus.</p>';
            } elseif ($error === 'wrongpassword') {
                echo '<p class="error-message">Falsches Passwort.</p>';
            } elseif ($error === 'usernotfound') {
                echo '<p class="error-message">Benutzer nicht gefunden.</p>';
            } elseif ($error === 'loginfailed') {
                echo '<p class="error-message">Login fehlgeschlagen.</p>';
            }
        }
        ?>
        <form class="auth-form" action="process_login.php" method="post">
            <div class="form-group">
                <label for="username">Benutzername:</label>
                <input type="text" id="username" name="username" class="auth-input" required>
            </div>
            <div class="form-group">
                <label for="password">Passwort:</label>
                <input type="password" id="password" name="password" class="auth-input" required>
            </div>
            <button type="submit" class="auth-button">Einloggen</button>
        </form>
        <div class="auth-link">
            Noch kein Konto? <a href="register.php">Registrieren</a>
        </div>
    </div>
</body>
</html> 