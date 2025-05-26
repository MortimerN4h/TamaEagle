<?php
/**
 * Firebase Configuration for TamaEagle
 * This replaces the MySQL config and provides Firebase integration
 */

require_once 'firebase-config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize Firebase services
$firebaseAuth = new FirebaseAuth($firebaseConfig['apiKey']);
$firebaseDb = new FirebaseDatabase($firebaseConfig['databaseURL']);

// Auto-login with test account to get auth token
try {
    if (!isset($_SESSION['firebase_token'])) {
        $authResult = $firebaseAuth->signInWithEmailAndPassword($firebaseEmail, $firebasePassword);
        $_SESSION['firebase_token'] = $authResult['idToken'];
        $_SESSION['firebase_uid'] = $authResult['localId'];
    }
    
    // Set auth token for database operations
    $firebaseDb->setAuthToken($_SESSION['firebase_token']);
} catch (Exception $e) {
    error_log("Firebase auto-login failed: " . $e->getMessage());
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Helper function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        // Get the current directory depth to create proper path
        $scriptPath = $_SERVER['SCRIPT_FILENAME'];
        if (
            strpos($scriptPath, 'views/') !== false ||
            strpos($scriptPath, 'tasks/') !== false ||
            strpos($scriptPath, 'projects/') !== false ||
            strpos($scriptPath, 'sections/') !== false ||
            strpos($scriptPath, 'api/') !== false
        ) {
            header("Location: ../auth/login.php");
        } else {
            header("Location: auth/login.php");
        }
        exit;
    }
}

// Helper function to get current user ID
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Helper function to get current username
function getCurrentUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

// Helper function to get POST data safely
function getPostData($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

// Helper function to get GET data safely
function getGetData($key, $default = '') {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

// Firebase User Management Functions
function createUser($email, $username, $password) {
    global $firebaseAuth, $firebaseDb;
    
    try {
        // Create user in Firebase Auth
        $authResult = $firebaseAuth->createUserWithEmailAndPassword($email, $password);
        $uid = $authResult['localId'];
        
        // Store user data in Firebase Database
        $userRef = $firebaseDb->getReference('users/' . $uid);
        $userData = [
            'username' => $username,
            'email' => $email,
            'createdAt' => date('c')
        ];
        
        $userRef->set($userData);
        
        // Create default Inbox project
        $projectRef = $firebaseDb->getReference('projects/' . $uid . '/inbox');
        $projectData = [
            'name' => 'Inbox',
            'color' => '#808080',
            'createdAt' => date('c')
        ];
        $projectRef->set($projectData);
        
        return $uid;
    } catch (Exception $e) {
        throw new Exception("Failed to create user: " . $e->getMessage());
    }
}

function authenticateUser($email, $password) {
    global $firebaseAuth, $firebaseDb;
    
    try {
        // Sign in with Firebase Auth
        $authResult = $firebaseAuth->signInWithEmailAndPassword($email, $password);
        $uid = $authResult['localId'];
        
        // Get user data from database
        $userRef = $firebaseDb->getReference('users/' . $uid);
        $userData = $userRef->get();
        
        if ($userData) {
            return [
                'id' => $uid,
                'username' => $userData['username'],
                'email' => $userData['email']
            ];
        }
        
        return false;
    } catch (Exception $e) {
        return false;
    }
}

function checkIfUserExists($email, $username = null) {
    global $firebaseDb;
    
    try {
        $usersRef = $firebaseDb->getReference('users');
        $allUsers = $usersRef->get();
        
        if ($allUsers) {
            foreach ($allUsers as $uid => $userData) {
                if ($userData['email'] === $email) {
                    return true;
                }
                if ($username && $userData['username'] === $username) {
                    return true;
                }
            }
        }
        
        return false;
    } catch (Exception $e) {
        return false;
    }
}
?>
