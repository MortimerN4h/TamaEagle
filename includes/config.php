<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize Firebase Services
try {
    require_once __DIR__ . '/../vendor/autoload.php';

    // Initialize Firebase Services
    $factory = (new \Kreait\Firebase\Factory)
        ->withServiceAccount(__DIR__ . '/../firebase-credentials.json')
        ->withProjectId('tamaeagle-36639');

    $GLOBALS['auth'] = $factory->createAuth();
    $GLOBALS['firestore'] = $factory->createFirestore()->database();
} catch (Exception $e) {
    die("Firebase initialization error: " . $e->getMessage());
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

// Helper function to create a Firestore server timestamp field value
function firestoreServerTimestamp() {
    return ['@type' => 'firestore.googleapis.com/Timestamp', 'value' => ['seconds' => time(), 'nanos' => 0]];
}

// Firestore helper functions
// Get a document by ID from a collection
function getDocument($collection, $documentId)
{
    if (!isset($GLOBALS['firestore'])) return null;
    
    $docRef = $GLOBALS['firestore']->collection($collection)->document($documentId);
    $snapshot = $docRef->snapshot();
    
    if ($snapshot->exists()) {
        $data = $snapshot->data();
        $data['id'] = $documentId; // Add document ID to the data
        return $data;
    }
    
    return null;
}

// Get documents from a collection with optional where clauses
function getDocuments($collection, $whereConditions = [], $orderBy = null, $orderDirection = 'asc')
{
    if (!isset($GLOBALS['firestore'])) return [];
    
    $query = $GLOBALS['firestore']->collection($collection);
    
    // Apply where conditions
    foreach ($whereConditions as $condition) {
        if (count($condition) === 3) {
            list($field, $operator, $value) = $condition;
            $query = $query->where($field, $operator, $value);
        }
    }
    
    // Apply ordering
    if ($orderBy !== null) {
        $query = $query->orderBy($orderBy, $orderDirection);
    }
    
    // Execute and return results
    $documents = [];
    $snapshot = $query->documents();
    
    foreach ($snapshot as $document) {
        $data = $document->data();
        $data['id'] = $document->id(); // Add document ID to the data
        $documents[] = $data;
    }
    
    return $documents;
}

// Add a new document to a collection
function addDocument($collection, $data)
{
    if (!isset($GLOBALS['firestore'])) return null;
    
    // Add timestamp using server timestamp
    $data['created_at'] = firestoreServerTimestamp();
    
    $docRef = $GLOBALS['firestore']->collection($collection)->add($data);
    return $docRef->id();
}

// Update an existing document
function updateDocument($collection, $documentId, $data)
{
    if (!isset($GLOBALS['firestore'])) return false;
    
    // Add timestamp using server timestamp
    $data['updated_at'] = firestoreServerTimestamp();
    
    $docRef = $GLOBALS['firestore']->collection($collection)->document($documentId);
    $docRef->update($data);
    
    return true;
}

// Delete a document
function deleteDocument($collection, $documentId)
{
    if (!isset($GLOBALS['firestore'])) return false;
    
    $docRef = $GLOBALS['firestore']->collection($collection)->document($documentId);
    $docRef->delete();
    
    return true;
}

// Transaction management
function runTransaction($callback)
{
    if (!isset($GLOBALS['firestore'])) return null;
    
    return $GLOBALS['firestore']->runTransaction($callback);
}
?>
