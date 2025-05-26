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
    $email = getPostData('username'); // This can be email or username
    $password = getPostData('password');

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            // Check if input is a valid email
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // If it's an email, sign in directly
                $signInResult = $auth->signInWithEmailAndPassword($email, $password);
            } else {
                // If it's a username, find the user by username in Firestore
                $usersQuery = $firestore->collection('users')
                    ->where('username', '==', $email)
                    ->limit(1)
                    ->documents();
                
                $user = null;
                foreach ($usersQuery as $userDoc) {
                    $user = $userDoc->data();
                    $user['id'] = $userDoc->id();
                    break;
                }
                
                if ($user && isset($user['email'])) {
                    // Now sign in with the retrieved email
                    $signInResult = $auth->signInWithEmailAndPassword($user['email'], $password);
                } else {
                    throw new Exception('User not found');
                }
            }
            
            // Get the user ID from Firebase
            $firebaseUserId = $signInResult->firebaseUserId();
            
            // Get additional user data from Firestore
            $userDoc = $firestore->collection('users')->document($firebaseUserId)->snapshot();
            $userData = $userDoc->exists() ? $userDoc->data() : [];
            
            // Create session
            $_SESSION['user_id'] = $firebaseUserId;
            $_SESSION['username'] = $userData['username'] ?? '';
            $_SESSION['email'] = $userData['email'] ?? '';
            
            // Redirect to dashboard
            header("Location: ../index.php");
            exit;
            
        } catch (Exception $e) {
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

