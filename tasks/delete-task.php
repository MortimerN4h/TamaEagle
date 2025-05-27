<?php
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$taskId = getGetData('id');

// Validate task exists and belongs to user
if (empty($taskId)) {
    $_SESSION['error'] = 'No task specified.';
    header("Location: ../views/inbox.php");
    exit;
}

// Get task from Firestore
$task = getDocument('tasks', $taskId);

if (!$task || $task['user_id'] !== $userId) {
    $_SESSION['error'] = 'Task not found or you do not have permission to delete it.';
    header("Location: ../views/inbox.php");
    exit;
}

try {
    // Delete the task from Firestore
    deleteDocument('tasks', $taskId);
    $_SESSION['success'] = 'Task deleted successfully.';
} catch (Exception $e) {
    $_SESSION['error'] = 'Error deleting task: ' . $e->getMessage();
}

// Redirect back to previous page or inbox if not available
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/inbox.php';
header("Location: $redirect");
exit;
?>