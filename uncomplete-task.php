<?php
require_once 'includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$taskId = getGetData('id');

// Validate task exists and belongs to user
if (empty($taskId)) {
    $_SESSION['error'] = 'No task specified.';
    header("Location: completed.php");
    exit;
}

$checkQuery = "SELECT id FROM tasks WHERE id = ? AND user_id = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("ii", $taskId, $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    $_SESSION['error'] = 'Task not found or you do not have permission to uncomplete it.';
    header("Location: completed.php");
    exit;
}

// Mark task as not completed
$query = "UPDATE tasks SET is_completed = 0, completed_at = NULL WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $taskId, $userId);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Task marked as not completed.';
} else {
    $_SESSION['error'] = 'Error uncompleting task: ' . $conn->error;
}

// Redirect back to completed page
header("Location: completed.php");
exit;
?>
