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
$title = getPostData('title');
$description = getPostData('description', '');
$dueDate = getPostData('due_date');
$priority = getPostData('priority', 'medium');
$projectId = getPostData('project_id');

// Validate required fields
if (empty($taskId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Task ID is required']);
    exit;
}

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

// Prepare the data for update
$updateData = [
    'title' => $title,
    'description' => $description,
    'priority' => $priority
];

if (!empty($dueDate)) {
    $updateData['due_date'] = $dueDate;
}

if ($projectId !== null) {
    $updateData['project_id'] = $projectId;
}

try {
    $success = updateTask($taskId, $userId, $updateData);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Task updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update task']);
    }
} catch (Exception $e) {
    error_log("Error updating task: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while updating the task']);
}
?>