<?php
require_once 'includes/config.php';

echo "<h1>Database Configuration Status</h1>";

if ($GLOBALS['useFirebase']) {
    echo "<div style='padding: 15px; background-color: #dff0d8; border: 1px solid #d6e9c6; border-radius: 4px;'>";
    echo "<h2 style='color: #3c763d;'>Using Firebase Configuration</h2>";
    echo "<p>The application is currently using Firebase Firestore for data storage.</p>";
    
    // Check if Firebase services initialized properly
    if (isset($GLOBALS['firestore']) && isset($GLOBALS['auth'])) {
        echo "<p style='color: #3c763d;'>✓ Firebase services initialized successfully.</p>";
    } else {
        echo "<p style='color: #a94442;'>✗ Firebase services initialization error.</p>";
    }
    
    echo "</div>";
    
    echo "<h3>Next Steps</h3>";
    echo "<ul>";
    echo "<li>Ensure your Firebase service account credentials are properly set up in firebase-credentials.json</li>";
    echo "<li>Create and initialize your Firestore database in the <a href='https://console.firebase.google.com/'>Firebase Console</a></li>";
    echo "<li>Run <code>composer require kreait/firebase-php google/cloud-firestore</code> to install required dependencies</li>";
    echo "</ul>";
} else {
    echo "<div style='padding: 15px; background-color: #fcf8e3; border: 1px solid #faebcc; border-radius: 4px;'>";
    echo "<h2 style='color: #8a6d3b;'>Using MySQL Configuration (Fallback)</h2>";
    echo "<p>The application is currently using MySQL for data storage.</p>";
    
    // Check if MySQL connection initialized properly
    if (isset($GLOBALS['conn'])) {
        echo "<p style='color: #3c763d;'>✓ MySQL connection established successfully.</p>";
    } else {
        echo "<p style='color: #a94442;'>✗ MySQL connection error.</p>";
    }
    
    echo "</div>";
    
    echo "<h3>To Enable Firebase:</h3>";
    echo "<ol>";
    echo "<li>Install Composer if not already installed (Get it from <a href='https://getcomposer.org/download/'>https://getcomposer.org/download/</a>)</li>";
    echo "<li>Run the following command in your project directory:";
    echo "<pre style='background-color: #f5f5f5; padding: 10px; border: 1px solid #ccc; border-radius: 4px;'>composer require kreait/firebase-php google/cloud-firestore</pre></li>";
    echo "<li>Create a service account in Firebase Console and download the credentials JSON</li>";
    echo "<li>Place the credentials file as firebase-credentials.json in the project root</li>";
    echo "<li>Refresh this page to verify Firebase integration</li>";
    echo "</ol>";
}

echo "<p><a href='index.php'>Return to application</a></p>";
?>
