<?php
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$taskId = getGetData('id');

// Validate task exists and belongs to user
if (empty($taskId)) {
    $_SESSION['error'] = 'No task specified.';
    header("Location: ../index.php");
    exit;
}

// Validate task exists and belongs to user
// Get task from Firestore
$task = getDocument('tasks', $taskId);

if (!$task || $task['user_id'] !== $userId) {
    $_SESSION['error'] = 'Task not found or you do not have permission to complete it.';
    header("Location: ../index.php");
    exit;
}

try {
    // Mark task as completed
    $updateData = [
        'is_completed' => true,
        'completed_at' => firestoreServerTimestamp()
    ];

    updateDocument('tasks', $taskId, $updateData);
    $_SESSION['success'] = 'Task marked as completed.';
} catch (Exception $e) {
    $_SESSION['error'] = 'Error completing task: ' . $e->getMessage();
}

// Redirect back to previous page or inbox if not available
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/today.php';
header("Location: $redirect");
exit;
?>