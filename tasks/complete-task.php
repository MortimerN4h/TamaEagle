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
if ($GLOBALS['useFirebase']) {
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
} else {
    // MySQL version
    // Check if task exists and belongs to user
    $checkQuery = "SELECT id FROM tasks WHERE id = ? AND user_id = ?";
    $checkStmt = $GLOBALS['conn']->prepare($checkQuery);
    $checkStmt->bind_param("ss", $taskId, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        $_SESSION['error'] = 'Task not found or you do not have permission to complete it.';
        header("Location: ../index.php");
        exit;
    }

    // Mark task as completed
    $updateQuery = "UPDATE tasks SET is_completed = 1, completed_at = NOW() WHERE id = ? AND user_id = ?";
    $updateStmt = $GLOBALS['conn']->prepare($updateQuery);
    $updateStmt->bind_param("ss", $taskId, $userId);

    if ($updateStmt->execute()) {
        $_SESSION['success'] = 'Task marked as completed.';
    } else {
        $_SESSION['error'] = 'Error completing task: ' . $GLOBALS['conn']->error;
    }
}

// Redirect back to previous page or inbox if not available
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/inbox.php';
header("Location: $redirect");
exit;
?>