<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="auth-container register-container">
        <h1 class="auth-title">Registrierung</h1>
        <?php
        if (isset($_GET['error'])) {
            $error = htmlspecialchars($_GET['error']);
            if ($error === 'emptyfields') {
                echo '<p class="error-message">Bitte fülle alle Felder aus.</p>';
            } elseif ($error === 'invalidusername') {
                echo '<p class="error-message">Ungültiger Benutzername (nur Buchstaben und Zahlen erlaubt).</p>';
            } elseif ($error === 'invalidemail') {
                echo '<p class="error-message">Ungültige E-Mail-Adresse.</p>';
            } elseif ($error === 'passwordsdonotmatch') {
                echo '<p class="error-message">Passwörter stimmen nicht überein.</p>';
            } elseif ($error === 'usertaken') {
                echo '<p class="error-message">Benutzername bereits vergeben.</p>';
            } elseif ($error === 'emailtaken') {
                echo '<p class="error-message">E-Mail-Adresse bereits vergeben.</p>';
            } elseif ($error === 'registrationfailed') {
                echo '<p class="error-message">Registrierung fehlgeschlagen. Bitte versuche es später erneut.</p>';
            }
        } elseif (isset($_GET['success'])) {
            echo '<p class="success-message">Registrierung erfolgreich! Du kannst dich nun <a href="login.php">einloggen</a>.</p>';
        }
        ?>
        <form class="auth-form" action="process_register.php" method="post">
            <div class="form-group">
                <label for="username">Benutzername:</label>
                <input type="text" id="username" name="username" class="auth-input" required>
            </div>
            <div class="form-group">
                <label for="email">E-Mail:</label>
                <input type="email" id="email" name="email" class="auth-input" required>
            </div>
            <div class="form-group">
                <label for="password">Passwort:</label>
                <input type="password" id="password" name="password" class="auth-input" required>
            </div>
            <div class="form-group">
                <label for="password_repeat">Passwort wiederholen:</label>
                <input type="password" id="password_repeat" name="password_repeat" class="auth-input" required>
            </div>
            <button type="submit" class="auth-button">Registrieren</button>
        </form>
        <div class="auth-link">
            Hast du bereits ein Konto? <a href="login.php">Einloggen</a>
        </div>
    </div>
</body>
</html>