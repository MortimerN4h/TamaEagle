<?php
require_once 'includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php");
    exit;
}

$userId = getCurrentUserId();
$taskId = getPostData('task_id');
$name = getPostData('name');
$description = getPostData('description');
$startDate = getPostData('start_date');
$dueDate = getPostData('due_date');
$priority = getPostData('priority');
$projectId = getPostData('project_id') ? getPostData('project_id') : null;
$sectionId = getPostData('section_id') ? getPostData('section_id') : null;

// Validate task exists and belongs to user
$checkQuery = "SELECT id FROM tasks WHERE id = ? AND user_id = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("ii", $taskId, $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    $_SESSION['error'] = 'Task not found or you do not have permission to edit it.';
    header("Location: index.php");
    exit;
}

// Validate required fields
if (empty($name)) {
    $_SESSION['error'] = 'Task name is required.';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Validate dates
if (!empty($startDate) && !empty($dueDate) && $dueDate < $startDate) {
    $_SESSION['error'] = 'Due date cannot be before start date.';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Validate project (if provided) belongs to user
if (!empty($projectId)) {
    $projectQuery = "SELECT id FROM projects WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($projectQuery);
    $stmt->bind_param("ii", $projectId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Invalid project selected.';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

// Validate section (if provided) belongs to project
if (!empty($sectionId) && !empty($projectId)) {
    $sectionQuery = "SELECT id FROM sections WHERE id = ? AND project_id = ?";
    $stmt = $conn->prepare($sectionQuery);
    $stmt->bind_param("ii", $sectionId, $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $sectionId = null; // Clear invalid section
    }
} elseif (empty($projectId)) {
    // If no project, clear section too
    $sectionId = null;
}

// Update the task
$query = "UPDATE tasks 
          SET name = ?, description = ?, start_date = ?, due_date = ?, 
              priority = ?, project_id = ?, section_id = ?
          WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssiiii", $name, $description, $startDate, $dueDate, $priority, $projectId, $sectionId, $taskId, $userId);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Task updated successfully.';
    
    // Redirect back to previous page or inbox if not available
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'inbox.php';
    header("Location: $redirect");
    exit;
} else {
    $_SESSION['error'] = 'Error updating task: ' . $conn->error;
    
    // Redirect back to previous page or inbox if not available
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'inbox.php';
    header("Location: $redirect");
    exit;
}
?>
