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

// Get POST data
$title = getPostData('title');
$description = getPostData('description', '');
$dueDate = getPostData('due_date');
$priority = getPostData('priority', 'medium');
$projectId = getPostData('project_id');

// Validate required fields
if (empty($title)) {
    http_response_code(400);
    echo json_encode(['error' => 'Task title is required']);
    exit;
}

// Validate priority
$validPriorities = ['low', 'medium', 'high'];
if (!in_array($priority, $validPriorities)) {
    $priority = 'medium';
}

// Format due date if provided
if (!empty($dueDate)) {
    $dueDate = date('Y-m-d H:i:s', strtotime($dueDate));
}

try {
    $taskId = addTask($userId, $title, $description, $dueDate, $priority, $projectId);
    
    if ($taskId) {
        echo json_encode([
            'success' => true,
            'message' => 'Task added successfully',
            'task_id' => $taskId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add task']);
    }
} catch (Exception $e) {
    error_log("Error adding task: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while adding the task']);
}
?>