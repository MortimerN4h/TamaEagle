<?php
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$taskId = getGetData('id');

// Validate task exists and belongs to user
if (empty($taskId)) {
    $_SESSION['error'] = 'No task specified.';
    header("Location: ../views/completed.php");
    exit;
}

// Get task from Firestore
$task = getDocument('tasks', $taskId);

if (!$task || $task['user_id'] !== $userId) {
    $_SESSION['error'] = 'Task not found or you do not have permission to uncomplete it.';
    header("Location: ../views/completed.php");
    exit;
}

try {
    // Mark task as not completed in Firestore
    updateDocument('tasks', $taskId, [
        'is_completed' => false,
        'completed_at' => null
    ]);
    $_SESSION['success'] = 'Task marked as not completed.';
} catch (Exception $e) {
    $_SESSION['error'] = 'Error uncompleting task: ' . $e->getMessage();
}

// Redirect back to completed page
header("Location: ../views/completed.php");
exit;
?>