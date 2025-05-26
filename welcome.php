<?php
require_once 'includes/config-firebase.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header("Location: views/inbox.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TamaEagle - Task Management Made Simple</title>
    <link rel="stylesheet" href="assets/css/welcome.css" />
</head>

<body>
    <header class="welcome-header">
        <nav class="navbar">
            <div class="nav-brand">
                <h1>TamaEagle</h1>
            </div>
            <div class="nav-links">
                <a href="auth/login.php" class="btn btn-outline">Sign In</a>
                <a href="auth/register.php" class="btn btn-primary">Sign Up</a>
            </div>
        </nav>
    </header>

    <main class="welcome-main">
        <section class="hero">
            <div class="hero-content">
                <h1>Organize your work and life, finally.</h1>
                <p>Become focused, organized, and calm with TamaEagle. The world's #1 task manager and to-do list app.</p>
                <div class="hero-buttons">
                    <a href="auth/register.php" class="btn btn-large btn-primary">Start for free</a>
                    <a href="auth/login.php" class="btn btn-large btn-outline">Sign In</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="task-preview">
                    <div class="task-item completed">
                        <span class="checkbox">✓</span>
                        <span class="task-text">Design new landing page</span>
                    </div>
                    <div class="task-item">
                        <span class="checkbox"></span>
                        <span class="task-text">Review project proposals</span>
                    </div>
                    <div class="task-item">
                        <span class="checkbox"></span>
                        <span class="task-text">Team meeting at 3 PM</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h2>Why choose TamaEagle?</h2>
                <div class="features-grid">
                    <div class="feature">
                        <div class="feature-icon">📋</div>
                        <h3>Stay organized</h3>
                        <p>Capture and organize tasks the moment they pop into your head.</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">🎯</div>
                        <h3>Stay focused</h3>
                        <p>Get a clear overview of everything on your plate and never lose track of important tasks.</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">⚡</div>
                        <h3>Stay productive</h3>
                        <p>Use TamaEagle to organize your work and life projects the way you want.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="welcome-footer">
        <div class="container">
            <p>&copy; 2025 TamaEagle. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>
