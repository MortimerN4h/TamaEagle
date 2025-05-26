<?php
require_once '../includes/config-firebase.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header("Location: ../index.php");
    exit;
}

$error = '';
$success = '';

// Process forgot password form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = getPostData('email');

    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            // In Firebase, you would typically use Firebase Auth's password reset
            // For now, we'll just show a success message
            $success = 'If an account with that email exists, you will receive a password reset link shortly.';
        } catch (Exception $e) {
            $success = 'If an account with that email exists, you will receive a password reset link shortly.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TamaEagle Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/Login/ForgotPW.css" />
    <script type="module" src="../assets/js/Login/simple-auth.js" defer></script>
</head>

<body>
    <div class="forgotpw-container">
        <form class="forgotpw-form" method="post" action="forgot-password.php">
            <h2>Reset your password</h2>

            <p class="instruction-text">
                Enter your email address and we'll send you a link to reset your password.
            </p>            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
            </div>

            <button type="submit" class="reset-password-btn">Send reset link</button>

            <p class="back-to-login-text">
                <a href="login.php">Back to sign in</a>
            </p>
        </form>
    </div>
</body>

</html>
