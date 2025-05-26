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
        // Check if username or email already exists
        $query = "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            // Create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                $userId = $stmt->insert_id;

                // Create default Inbox project for user
                $projectQuery = "INSERT INTO projects (user_id, name, color) VALUES (?, 'Inbox', '#808080')";
                $projectStmt = $conn->prepare($projectQuery);
                $projectStmt->bind_param("i", $userId);
                $projectStmt->execute();

                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Error creating account. Please try again.';
            }
        }
    }
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