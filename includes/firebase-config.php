<?php
/**
 * Firebase Configuration and Client Classes
 * 
 * Provides optimized Firebase Realtime Database operations with improved
 * error handling, connection management, and authentication.
 */

// Firebase configuration
$firebaseConfig = [
    'apiKey' => "AIzaSyAzd7Jgo5HgqSUPtqcLnt2PkZE1lkxaW5s",
    'authDomain' => "tamaeagle-36639.firebaseapp.com",
    'projectId' => "tamaeagle-36639",
    'storageBucket' => "tamaeagle-36639.firebasestorage.app",
    'messagingSenderId' => "1067380139684",
    'appId' => "1:1067380139684:web:635f2edcff500b0e032831",
    'databaseURL' => "https://tamaeagle-36639-default-rtdb.firebaseio.com"
];

// Firebase Authentication (automatically login with the specified account)
$firebaseEmail = 'testmaildontdelete@gmail.com';
$firebasePassword = '123456789';

// Check if CURL is installed
if (!function_exists('curl_version')) {
    die("CURL is required for Firebase operations. Please install the PHP CURL extension.");
}

/**
 * Firebase Authentication Class
 * 
 * Handles Firebase Authentication operations with improved error handling
 */
class FirebaseAuth {
    private $apiKey;
    private $timeout;
    
    public function __construct($apiKey, $timeout = 30) {
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
    }
    
    public function signInWithEmailAndPassword($email, $password) {
        $url = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=" . $this->apiKey;
        
        $data = [
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true
        ];
        
        $response = $this->makeHttpRequest($url, 'POST', $data);
        return $response;
    }
    
    public function refreshIdToken($refreshToken) {
        $url = "https://securetoken.googleapis.com/v1/token?key=" . $this->apiKey;
        
        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ];
        
        $response = $this->makeHttpRequest($url, 'POST', $data);
        return $response;
    }
    
    private function makeHttpRequest($url, $method = 'GET', $data = null) {
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        
        if ($method === 'POST' && $data) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ]);
        }
        
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($response === false || !empty($curlError)) {
            error_log("Firebase cURL error: " . $curlError);
            throw new Exception("Network error: " . $curlError);
        }
        
        if ($statusCode >= 400) {
            $error = json_decode($response, true);
            $errorMessage = $error['error']['message'] ?? 'Unknown error';
            error_log("Firebase API error: HTTP $statusCode - $errorMessage");
            throw new Exception("Firebase auth error: " . $errorMessage);
        }
        
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response from Firebase");
        }
          return $decodedResponse;
    }
    
    public function createUserWithEmailAndPassword($email, $password) {
        $url = "https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=" . $this->apiKey;
        
        $data = [
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true
        ];
        
        $jsonData = json_encode($data);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);
        
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($response === false || !empty($curlError)) {
            error_log("Firebase cURL error: " . $curlError);
            throw new Exception("Network error: " . $curlError);
        }
        
        if ($statusCode >= 400) {
            $error = json_decode($response, true);
            $errorMessage = $error['error']['message'] ?? 'Unknown error';
            error_log("Firebase API error: HTTP $statusCode - $errorMessage");
            throw new Exception("Firebase auth error: " . $errorMessage);
        }
        
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response from Firebase");
        }
        
        return $decodedResponse;
    }
    
    public function signOut() {
        return true;
    }
}

/**
 * Firebase Database Class
 */
class FirebaseDatabase {
    private $databaseURL;
    private $authToken;
    private $timeout;
    
    public function __construct($databaseURL, $authToken = null, $timeout = 30) {
        $this->databaseURL = rtrim($databaseURL, '/');
        $this->authToken = $authToken;
        $this->timeout = $timeout;
    }
    
    public function getReference($path) {
        return new FirebaseReference($this->databaseURL, $path, $this->authToken, $this->timeout);
    }
    
    public function setAuthToken($authToken) {
        $this->authToken = $authToken;
    }
}

/**
 * Firebase Reference Class
 */
class FirebaseReference {
    private $databaseURL;
    private $path;
    private $authToken;
    private $timeout;
    
    public function __construct($databaseURL, $path, $authToken = null, $timeout = 30) {
        $this->databaseURL = $databaseURL;
        $this->path = ltrim($path, '/');
        $this->authToken = $authToken;
        $this->timeout = $timeout;
    }
    
    public function getValue() {
        try {
            $url = $this->buildUrl();
            $response = $this->makeHttpRequest($url, 'GET');
            return $response;
        } catch (Exception $e) {
            error_log("Firebase getValue error: " . $e->getMessage());
            return null;
        }
    }
    
    public function push() {
        $id = $this->generateUniqueId();
        return new FirebaseReference($this->databaseURL, $this->path . '/' . $id, $this->authToken, $this->timeout);
    }
    
    public function set($data) {
        try {
            $url = $this->buildUrl();
            return $this->makeHttpRequest($url, 'PUT', $data);
        } catch (Exception $e) {
            error_log("Firebase set error: " . $e->getMessage());
            return null;
        }
    }
    
    public function update($data) {
        try {
            $url = $this->buildUrl();
            return $this->makeHttpRequest($url, 'PATCH', $data);
        } catch (Exception $e) {
            error_log("Firebase update error: " . $e->getMessage());
            return null;
        }
    }
    
    public function remove() {
        try {
            $url = $this->buildUrl();
            return $this->makeHttpRequest($url, 'DELETE');
        } catch (Exception $e) {
            error_log("Firebase remove error: " . $e->getMessage());
            return null;
        }
    }
    
    public function getKey() {
        $parts = explode('/', $this->path);
        return end($parts);
    }
    
    public function orderByChild($child) {
        return $this;
    }
    
    public function equalTo($value) {
        return $this;
    }
    
    private function buildUrl() {
        $url = $this->databaseURL . '/' . $this->path . '.json';
        if ($this->authToken) {
            $url .= '?auth=' . $this->authToken;
        }
        return $url;
    }
    
    private function makeHttpRequest($url, $method = 'GET', $data = null) {
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_USERAGENT, 'TamaEagle-Firebase-Client/1.0');
        
        switch ($method) {
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                break;
        }
        
        if (in_array($method, ['PUT', 'PATCH', 'POST']) && $data !== null) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ]);
        }
        
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($response === false || !empty($curlError)) {
            throw new Exception("Network error: " . $curlError);
        }
        
        if ($statusCode >= 400) {
            $error = json_decode($response, true);
            $errorMessage = $error['error'] ?? 'HTTP ' . $statusCode;
            throw new Exception("Firebase error: " . $errorMessage);
        }
        
        if ($response === 'null' || $response === '') {
            return null;
        }
        
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response from Firebase");
        }
        
        return $decodedResponse;
    }
    
    private function generateUniqueId() {
        $chars = '-0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
        $id = '';
        for ($i = 0; $i < 20; $i++) {
            $id .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $id;
    }
}

class FirebaseFirestore {
    private $projectId;
    
    public function __construct($projectId) {
        $this->projectId = $projectId;
    }
    
    public function collection($name) {
        return new FirestoreCollection($this->projectId, $name);
    }
}

class FirestoreCollection {
    private $projectId;
    private $name;
    
    public function __construct($projectId, $name) {
        $this->projectId = $projectId;
        $this->name = $name;
    }
    
    public function getName() {
        return $this->name;
    }
}

// Initialize Firebase
$auth = new FirebaseAuth($firebaseConfig['apiKey']);
$database = new FirebaseDatabase($firebaseConfig['databaseURL']);
$firestore = new FirebaseFirestore($firebaseConfig['projectId']);

// Initialize Firebase connection and authentication
function initializeFirebase() {
    global $auth, $firebaseEmail, $firebasePassword, $database, $firebaseConfig;
    
    // Check if user is already logged in and token is still valid
    if (isset($_SESSION['firebase_user']) && isset($_SESSION['firebase_token'])) {
        $tokenExpiry = $_SESSION['firebase_token_expiry'] ?? 0;
        if (time() < $tokenExpiry) {
            $database->setAuthToken($_SESSION['firebase_token']);
            return true;
        }
        
        if (isset($_SESSION['firebase_refresh_token'])) {
            try {
                $refreshResult = $auth->refreshIdToken($_SESSION['firebase_refresh_token']);
                $_SESSION['firebase_token'] = $refreshResult['id_token'];
                $_SESSION['firebase_token_expiry'] = time() + (int)$refreshResult['expires_in'];
                $database->setAuthToken($refreshResult['id_token']);
                return true;
            } catch (Exception $e) {
                error_log("Token refresh failed: " . $e->getMessage());
            }
        }
    }
    
    try {
        $user = $auth->signInWithEmailAndPassword($firebaseEmail, $firebasePassword);
        
        $_SESSION['firebase_user'] = $user;
        $_SESSION['firebase_user_id'] = $user['localId'];
        $_SESSION['firebase_user_email'] = $firebaseEmail;
        $_SESSION['firebase_token'] = $user['idToken'];
        $_SESSION['firebase_refresh_token'] = $user['refreshToken'] ?? null;
        $_SESSION['firebase_token_expiry'] = time() + (int)($user['expiresIn'] ?? 3600);
        
        $database->setAuthToken($user['idToken']);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Firebase Authentication Error: " . $e->getMessage());
        throw new Exception("Firebase Authentication failed: " . $e->getMessage());
    }
}

function getFirebaseReference($path) {
    global $database;
    
    try {
        return $database->getReference($path);
    } catch (Exception $e) {
        error_log("Failed to get Firebase reference for path '$path': " . $e->getMessage());
        return null;
    }
}

function getFirestoreCollection($collection) {
    global $firestore;
    return $firestore->collection($collection);
}

function checkCurlInstalled() {
    if (!function_exists('curl_version')) {
        die("CURL is required for Firebase operations. Please install the PHP CURL extension.");
    }
}

function testFirebaseConnection() {
    try {
        initializeFirebase();
        $ref = getFirebaseReference('test');
        if ($ref) {
            return true;
        }
    } catch (Exception $e) {
        error_log("Firebase connection test failed: " . $e->getMessage());
    }
    return false;
}

?>
