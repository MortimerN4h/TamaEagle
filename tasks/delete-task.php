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

if ($GLOBALS['useFirebase']) {
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
} else {
    // MySQL validation
    global $conn;
    $checkQuery = "SELECT id FROM tasks WHERE id = ? AND user_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $taskId, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        $_SESSION['error'] = 'Task not found or you do not have permission to delete it.';
        header("Location: ../views/inbox.php");
        exit;
    }
    
    // Delete the task from MySQL
    $query = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $taskId, $userId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Task deleted successfully.';
    } else {
        $_SESSION['error'] = 'Error deleting task: ' . $conn->error;
    }
}

// Redirect back to previous page or inbox if not available
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/inbox.php';
header("Location: $redirect");
exit;
?>