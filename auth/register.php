<?php
require_once '../includes/config.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header("Location: ../index.php");
    exit;
}

$error = '';
$success = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = getPostData('username');
    $email = getPostData('email');
    $password = getPostData('password');
    $confirmPassword = getPostData('confirm_password');

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        try {
            // Check if username already exists in Firestore
            $usernameCheck = $firestore->collection('users')
                ->where('username', '==', $username)
                ->limit(1)
                ->documents();
            
            $usernameExists = false;
            foreach ($usernameCheck as $doc) {
                $usernameExists = true;
                break;
            }
            
            if ($usernameExists) {
                $error = 'Username already exists';
            } else {
                // Create user in Firebase Authentication
                $userProperties = [
                    'email' => $email,
                    'password' => $password,
                    'displayName' => $username,
                ];
                
                $createdUser = $auth->createUser($userProperties);
                $userId = $createdUser->uid;
                
                // Store additional user data in Firestore
                $userData = [
                    'username' => $username,
                    'email' => $email,
                    'created_at' => firestoreServerTimestamp()
                ];
                
                $firestore->collection('users')->document($userId)->set($userData);
                
                // Create default Inbox project for user
                $inboxProject = [
                    'user_id' => $userId,
                    'name' => 'Inbox',
                    'color' => '#808080'
                ];
                
                $projectId = addDocument('projects', $inboxProject);

                $success = 'Registration successful! You can now login.';
            }
        } catch (Exception $e) {
            $error = 'Error creating account: ' . $e->getMessage();
        }
    }
}

// Helper function to create a Firestore server timestamp
function firestoreServerTimestamp() {
    return ['@type' => 'firestore.googleapis.com/Timestamp', 'value' => ['seconds' => time(), 'nanos' => 0]];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TamaEagle Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/Login/Register.css" />
</head>

<body>
    <div class="register-container">
            <form class="register-form">
                <h2>Sign Up</h2>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" required placeholder="Enter your email" />
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" required placeholder="Enter your password" />
                    <span class="toggle-password" data-target="password">Show</span>
                </div>

                <div class="input-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" required placeholder="Confirm your password" />
                    <span class="toggle-password" data-target="confirm-password">Show</span>
                </div>

                <button type="button" class="register-btn" id="submit">Sign up</button>

                <div class="divider">
                    <span>or</span>
                </div>

                <button type="button" class="google-btn">
                    <img src="../assets/icons/google-logo.png" alt="Google" width="15" height="15"
                        style="vertical-align: middle; margin-right: 2px;">
                    Continue with Google
                </button>

                <p class="Login-text">
                    Already have an account? <a href="login.php">Sign in</a>
                </p>
            </form>
        </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../assets/js/Register.js"></script>
</body>

</html>