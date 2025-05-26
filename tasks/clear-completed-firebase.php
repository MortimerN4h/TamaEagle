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

try {
    // Get all completed tasks for the user
    $completedTasks = getUserTasks($userId, true);
    $success = true;
    
    // Delete each completed task
    foreach ($completedTasks as $task) {
        $result = deleteTask($task['id'], $userId);
        if (!$result) {
            $success = false;
        }
    }
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'All completed tasks have been cleared',
            'count' => count($completedTasks)
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to clear all completed tasks']);
    }
} catch (Exception $e) {
    error_log("Error clearing completed tasks: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while clearing completed tasks']);
}
?>