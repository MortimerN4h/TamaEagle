<?php
require_once 'includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$taskId = getGetData('id');

// Validate task exists and belongs to user
if (empty($taskId)) {
    $_SESSION['error'] = 'No task specified.';
    header("Location: index.php");
    exit;
}

$checkQuery = "SELECT id FROM tasks WHERE id = ? AND user_id = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("ii", $taskId, $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    $_SESSION['error'] = 'Task not found or you do not have permission to complete it.';
    header("Location: index.php");
    exit;
}

// Mark task as completed
$query = "UPDATE tasks SET is_completed = 1, completed_at = NOW() WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $taskId, $userId);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Task marked as completed.';
} else {
    $_SESSION['error'] = 'Error completing task: ' . $conn->error;
}

// Redirect back to previous page or inbox if not available
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'inbox.php';
header("Location: $redirect");
exit;
?>
