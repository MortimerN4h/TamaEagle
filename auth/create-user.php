<?php
session_start();
require_once '../includes/config-firebase.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';
$name = $input['name'] ?? '';

// Validate input
if (empty($email) || empty($password) || empty($name)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email, password, and name are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 6 characters long']);
    exit;
}

try {
    // Check if user already exists
    if (checkIfUserExists($email)) {
        http_response_code(409);
        echo json_encode(['error' => 'User with this email already exists']);
        exit;
    }

    // Create user
    $userData = createUser($email, $password, $name);
    
    if ($userData) {
        // Set session data
        $_SESSION['user_id'] = $userData['localId'];
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $name;
        $_SESSION['firebase_token'] = $userData['idToken'];
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'User created successfully',
            'user' => [
                'id' => $userData['localId'],
                'email' => $email,
                'name' => $name
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create user']);
    }
} catch (Exception $e) {
    error_log("User creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while creating the user']);
}
?>
