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

$idToken = $input['idToken'] ?? '';

if (empty($idToken)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID token is required']);
    exit;
}

try {
    // Verify the ID token with Firebase
    $verifyUrl = "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=" . FIREBASE_API_KEY;
    
    $verifyData = [
        'idToken' => $idToken
    ];
    
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($verifyData)
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($verifyUrl, false, $context);
    
    if ($response === FALSE) {
        throw new Exception('Failed to verify token with Firebase');
    }
    
    $responseData = json_decode($response, true);
    
    if (isset($responseData['error'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired token']);
        exit;
    }
    
    if (!isset($responseData['users']) || empty($responseData['users'])) {
        http_response_code(401);
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    $user = $responseData['users'][0];
    
    // Get user data from Realtime Database
    $userDataRef = $firebase->getReference('users/' . $user['localId']);
    $userData = $userDataRef->get();
    
    // Set session data
    $_SESSION['user_id'] = $user['localId'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $userData['name'] ?? '';
    $_SESSION['firebase_token'] = $idToken;
    
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['localId'],
            'email' => $user['email'],
            'name' => $userData['name'] ?? '',
            'emailVerified' => $user['emailVerified'] ?? false
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Google auth error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred during authentication']);
}
?>
