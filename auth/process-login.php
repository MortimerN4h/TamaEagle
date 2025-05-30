<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Lấy ID token từ request
    $idToken = getPostData('idToken');
    $isGoogle = getPostData('google') === 'true';

    if (empty($idToken)) {
        throw new Exception('ID token is required');
    }

    // Debug: Log that we're attempting verification
    error_log('Attempting to verify ID token');

    // Xác thực ID token với Firebase
    $verifiedIdToken = $auth->verifyIdToken($idToken);

    // Lấy thông tin từ token đã xác thực
    $firebaseUserId = $verifiedIdToken->claims()->get('sub');
    $email = $verifiedIdToken->claims()->get('email');

    // Lấy thông tin user từ Firestore
    $userDoc = $firestore->collection('users')->document($firebaseUserId)->snapshot();

    if (!$userDoc->exists()) {
        // Nếu user không tồn tại trong Firestore, tạo document mới
        $userData = [
            'email' => $email,
            'created_at' => firestoreServerTimestamp()
        ];
        $firestore->collection('users')->document($firebaseUserId)->set($userData);

        // Lấy lại data sau khi tạo
        $userData = $userData;
    } else {
        $userData = $userDoc->data();
    }

    // Tạo session PHP
    $_SESSION['user_id'] = $firebaseUserId;
    $_SESSION['email'] = $email;
    $_SESSION['username'] = isset($userData['username']) ? $userData['username'] : '';

    // Trả về kết quả thành công
    echo json_encode([
        'success' => true,
        'redirect' => '../views/today.php',
        'user' => [
            'id' => $firebaseUserId,
            'email' => $email,
            'username' => isset($userData['username']) ? $userData['username'] : ''
        ]
    ]);
} catch (Exception $e) {
    // Ghi log lỗi với chi tiết hơn
    error_log('Login Error: ' . $e->getMessage());
    error_log('Login Error Code: ' . $e->getCode());
    error_log('Login Error File: ' . $e->getFile() . ':' . $e->getLine());

    // Kiểm tra nếu là lỗi xác thực Firebase
    $errorMessage = $e->getMessage();
    $isAuthError = (
        strpos($errorMessage, 'authentication') !== false ||
        strpos($errorMessage, 'credential') !== false ||
        strpos($errorMessage, 'UNAUTHENTICATED') !== false
    );

    if ($isAuthError) {
        error_log('Firebase authentication error detected. Check firebase-credentials.json file.');
        $responseMessage = 'Firebase authentication error. Please check server configuration.';
    } else {
        $responseMessage = $errorMessage;
    }

    // Trả về lỗi
    http_response_code(401);
    echo json_encode([
        'error' => $responseMessage,
        'errorCode' => $e->getCode(),
        'isAuthError' => $isAuthError
    ]);
}
?>