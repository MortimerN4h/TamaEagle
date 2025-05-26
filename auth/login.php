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
    <title>TamaEagle Login</title>
    <link rel="stylesheet" href="../assets/css/Login/Login.css" />
    <script type="module" src="../assets/js/Login/firebase-client.js" defer></script>
</head>

<body>
    <div class="Login-container">        <form class="Login-form" id="loginForm">
            <h2>Sign In</h2>            
            <div id="error-message" class="error-message" style="display: none;"></div>
            <div id="success-message" class="success-message" style="display: none;"></div>

            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email" />
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password" />
                <span class="toggle-password" data-target="password">Show</span>
            </div>

            <button type="submit" class="Login-btn">Log In</button>

            <div class="login-options">
                <div class="remember-me">
                    <input type="checkbox" id="rememberMe">
                    <label for="rememberMe">Remember me</label>
                </div>
                <a href="forgot-password.php">Forgot your password?</a>
            </div>

            <div class="divider">
                <span>or</span>
            </div>

            <button type="button" class="google-btn">
                <img src="../assets/icons/google-logo.png" alt="Google" width="15" height="15"
                    style="vertical-align: middle; margin-right: 2px;">
                Continue with Google
            </button>

            <p class="signup-text">
                Don't have an account? <a href="register.php">Sign up</a>
            </p>
        </form>
    </div>
</body>

</html>