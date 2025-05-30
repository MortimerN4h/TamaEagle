<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize Firebase Services
try {
    require_once __DIR__ . '/../vendor/autoload.php';

    // Initialize Firebase Services
    $factory = new \Kreait\Firebase\Factory();
    $factory = $factory->withServiceAccount(__DIR__ . '/../firebase-credentials.json');
    $factory = $factory->withProjectId('tamaeagle-36639');

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
            header("Location: ../auth/login.html");
        } else {
            header("Location: ../auth/login.html");
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
function firestoreServerTimestamp()
{
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

    try {
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
    } catch (Google\Cloud\Core\Exception\FailedPreconditionException $e) {
        // Handle missing index error
        error_log('Firestore Index Error: ' . $e->getMessage());

        // Extract index creation URL from error message
        $message = $e->getMessage();
        if (preg_match('/https:\/\/console\.firebase\.google\.com[^"]+/', $message, $matches)) {
            $indexUrl = $matches[0];
            error_log('Create index at: ' . $indexUrl);
        }

        // Return empty array for now, but log the error
        return [];
    } catch (Exception $e) {
        error_log('Firestore Query Error: ' . $e->getMessage());
        return [];
    }
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
    if (!isset($GLOBALS['firestore'])) {
        error_log("Firestore not initialized in updateDocument for $collection/$documentId");
        return false;
    }

    try {        // Add timestamp using server timestamp
        $data['updated_at'] = firestoreServerTimestamp();
        
        // Log the operation
        error_log("Updating document $collection/$documentId with data: " . json_encode($data));

        $docRef = $GLOBALS['firestore']->collection($collection)->document($documentId);
        
        // First check if document exists
        $snapshot = $docRef->snapshot();
        if (!$snapshot->exists()) {
            error_log("Error: Document $collection/$documentId does not exist");
            return false;
        }
          // Format data for Firestore update with explicit 'path' and 'value' keys
        $formattedData = [];
        foreach ($data as $key => $value) {
            $formattedData[] = ['path' => $key, 'value' => $value];
        }
          // Perform update with correctly formatted data and get update result
        $updateResult = $docRef->update($formattedData);
        
        // Log success without trying to access updateTime() which may not be available
        error_log("Document updated successfully: $collection/$documentId");
        return true;
    } catch (Exception $e) {
        error_log("Error updating document $collection/$documentId: " . $e->getMessage());
        return false;
    }
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