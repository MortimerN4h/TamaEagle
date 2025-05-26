<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'tamaeagle';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === TRUE) {
    // echo "Database created successfully or already exists";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($db_name);

// Create tables if they don't exist
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(20) DEFAULT '#ff0000',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    position INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT,
    section_id INT DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE,
    due_date DATE,
    priority INT DEFAULT 0,
    position INT DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL
);
";

if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
        // If there's more result-sets, prepare next one
    } while ($conn->more_results() && $conn->next_result());
} else {
    echo "Error creating tables: " . $conn->error;
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper function to check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Helper function to redirect if not logged in
function requireLogin()
{
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
function getCurrentUserId()
{
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Function to safely get POST data
function getPostData($key, $default = '')
{
    return isset($_POST[$key]) ? htmlspecialchars(trim($_POST[$key])) : $default;
}

// Function to safely get GET data
function getGetData($key, $default = '')
{
    return isset($_GET[$key]) ? htmlspecialchars(trim($_GET[$key])) : $default;
}

// Current date helper
function getCurrentDate()
{
    return date('Y-m-d');
}

// Format date for display
function formatDate($date)
{
    if (!$date) return '';
    $timestamp = strtotime($date);
    return date('M d', $timestamp);
}

// Check if a date is today
function isToday($date)
{
    return $date == date('Y-m-d');
}

// Check if a date is tomorrow
function isTomorrow($date)
{
    return $date == date('Y-m-d', strtotime('+1 day'));
}

// Check if a date is in the past
function isPast($date)
{
    return $date < date('Y-m-d');
}

// Get day of the week
function getDayOfWeek($date)
{
    return date('l', strtotime($date));
}
