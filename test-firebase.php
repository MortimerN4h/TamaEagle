<?php
require_once 'includes/config-firebase.php';

echo "<h1>Firebase Connection Test</h1>";

try {
    echo "<p>✅ Firebase config loaded successfully</p>";
    
    // Test Firebase Authentication
    if (isset($firebaseAuth)) {
        echo "<p>✅ Firebase Auth initialized</p>";
    } else {
        echo "<p>❌ Firebase Auth not initialized</p>";
    }
    
    // Test Firebase Database
    if (isset($firebaseDb)) {
        echo "<p>✅ Firebase Database initialized</p>";
        
        // Test writing to database
        $testRef = $firebaseDb->getReference('test/connection');
        $testData = [
            'message' => 'Hello Firebase!',
            'timestamp' => date('c'),
            'status' => 'connected'
        ];
        
        $result = $testRef->set($testData);
        if ($result !== null) {
            echo "<p>✅ Successfully wrote test data to Firebase</p>";
            
            // Test reading from database
            $readData = $testRef->get();
            if ($readData) {
                echo "<p>✅ Successfully read test data from Firebase:</p>";
                echo "<pre>" . json_encode($readData, JSON_PRETTY_PRINT) . "</pre>";
            } else {
                echo "<p>❌ Failed to read test data from Firebase</p>";
            }
        } else {
            echo "<p>❌ Failed to write test data to Firebase</p>";
        }
    } else {
        echo "<p>❌ Firebase Database not initialized</p>";
    }
    
    // Test session
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "<p>✅ Session is active</p>";
        if (isset($_SESSION['firebase_token'])) {
            echo "<p>✅ Firebase token exists in session</p>";
        } else {
            echo "<p>⚠️ No Firebase token in session</p>";
        }
    } else {
        echo "<p>❌ Session not active</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='auth/register.php'>Test Registration</a> | <a href='auth/login.php'>Test Login</a> | <a href='welcome.php'>Back to Welcome</a></p>";
?>
