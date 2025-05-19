<?php
require_once 'includes/config.php';
requireLogin();

$userId = getCurrentUserId();

// Delete all completed tasks for this user
$query = "DELETE FROM tasks WHERE user_id = ? AND is_completed = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    $_SESSION['success'] = 'All completed tasks have been permanently deleted.';
} else {
    $_SESSION['error'] = 'Error clearing completed tasks: ' . $conn->error;
}

// Redirect back to completed page
header("Location: completed.php");
exit;
?>
