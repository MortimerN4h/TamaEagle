<?php
require_once '../includes/config-firebase.php';
require_once '../includes/firebase-tasks.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$userId = getCurrentUserId();
$taskId = getPostData('task_id');

if (empty($taskId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Task ID is required']);
    exit;
}

try {
    $success = uncompleteTask($taskId, $userId);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Task uncompleted successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to uncomplete task']);
    }
} catch (Exception $e) {
    error_log("Error uncompleting task: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while uncompleting the task']);
}
?>