<?php
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();

try {
    // Get all completed tasks for this user
    $whereConditions = [
        ['user_id', '==', $userId],
        ['is_completed', '==', true]
    ];
    
    $completedTasks = getDocuments('tasks', $whereConditions);
    $deletedCount = 0;
    
    // Delete each task individually
    foreach ($completedTasks as $task) {
        if (deleteDocument('tasks', $task['id'])) {
            $deletedCount++;
        }
    }
    
    if ($deletedCount > 0) {
        $_SESSION['success'] = 'All completed tasks have been permanently deleted.';
    } else {
        $_SESSION['info'] = 'No completed tasks found to delete.';
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error clearing completed tasks: ' . $e->getMessage();
}

// Redirect back to completed page
header("Location: ../views/completed.php");
exit;
?>