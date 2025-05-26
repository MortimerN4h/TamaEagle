<?php
require_once 'includes/config-firebase.php';

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect to Inbox page if logged in
    header("Location: views/inbox.php");
    exit;
} else {
    // Redirect to welcome page if not logged in
    header("Location: welcome.php");
    exit;
}
?>