<?php
require_once '../includes/config-firebase.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TamaEagle Register</title>
    <link rel="stylesheet" href="../assets/css/Login/Register.css" />
    <script type="module" src="../assets/js/Login/firebase-client.js" defer></script>
</head>

<body>    <div class="register-container">
        <form class="register-form" id="registerForm">
            <h2>Sign Up</h2>
            
            <div id="error-message" class="error-message" style="display: none;"></div>
            <div id="success-message" class="success-message" style="display: none;"></div>

            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter your username" />
            </div>

            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email" />
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password" />
                <span class="toggle-password" data-target="password">Show</span>
            </div>

            <div class="input-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm_password" required placeholder="Confirm your password" />
                <span class="toggle-password" data-target="confirm-password">Show</span>
            </div>

            <button type="submit" class="register-btn">Sign up</button>

            <div class="divider">
                <span>or</span>
            </div>

            <button type="button" class="google-btn" id="googleSignUpBtn">
                <img src="../assets/icons/google-logo.png" alt="Google" width="15" height="15"
                    style="vertical-align: middle; margin-right: 2px;">
                Continue with Google
            </button>

            <p class="Login-text">
                Already have an account? <a href="login.php">Sign in</a>
            </p>
        </form>
    </div>
</body>

</html>