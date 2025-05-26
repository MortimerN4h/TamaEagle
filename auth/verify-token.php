<?php
require_once '../includes/config-firebase.php';

header('Content-Type: application/json');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['idToken'])) {
        throw new Exception('Missing ID token');
    }
    
    $idToken = $input['idToken'];
    
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
        throw new Exception($responseData['error']['message']);
    }
    
    if (!isset($responseData['users']) || empty($responseData['users'])) {
        throw new Exception('User not found');
    }
    
    $user = $responseData['users'][0];
    $uid = $user['localId'];
    
    // Get user data from Realtime Database
    $userRef = $firebase->getReference('users/' . $uid);
    $userData = $userRef->get();
    
    // Create session
    $_SESSION['user_id'] = $uid;
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $userData['name'] ?? '';
    $_SESSION['firebase_token'] = $idToken;
    
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $uid,
            'email' => $user['email'],
            'name' => $userData['name'] ?? '',
            'emailVerified' => $user['emailVerified'] ?? false
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Token verification error: " . $e->getMessage());
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
