<?php
require_once '../includes/config.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header("Location: ../index.php");
    exit;
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = getPostData('username');
    $password = getPostData('password');

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all required fields';
    } else {
        // Query to find user
        $query = "SELECT id, username, password FROM users WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $username); // Username can be email too
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Redirect to dashboard                header("Location: ../index.php");
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TamaEagle Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/Login/Login.css" />
</head>

<body>
    <div class="Login-container">
        <form class="Login-form">
            <h2>Sign In</h2>

            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" required placeholder="Enter your email" />
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" required placeholder="Enter your password" />
                <span class="toggle-password" data-target="password">Show</span>
            </div>

            <button type="button" class="Login-btn" id="submit">Log In</button>

            <div class="login-options">
                <div class="remember-me">
                    <input type="checkbox" id="rememberMe">
                    <label for="rememberMe">Remember me</label>
                </div>
                <a href="forgotpw.php">Forgot your password?</a>
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

    <scripts src="../assets/js/Login.js"></scripts>
</body>

</html>

