<?php
require_once '../includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../index.php");
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

// Get task from Firestore
$task = getDocument('tasks', $taskId);

if (!$task || $task['user_id'] !== $userId) {
    $_SESSION['error'] = 'Task not found or you do not have permission to edit it.';
    header("Location: ../views/today.php");
    exit;
}

// Validate project (if provided) belongs to user
if (!empty($projectId)) {
    $project = getDocument('projects', $projectId);
    if (!$project || $project['user_id'] !== $userId) {
        $_SESSION['error'] = 'Invalid project selected.';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

// Validate section (if provided) belongs to project
if (!empty($sectionId) && !empty($projectId)) {
    $section = getDocument('sections', $sectionId);
    if (!$section || $section['project_id'] !== $projectId) {
        $sectionId = null; // Clear invalid section
    }
} elseif (empty($projectId)) {
    // If no project, clear section too
    $sectionId = null;
}

try {
    // Prepare the task data with validated fields
    $taskData = [
        'name' => $name,
        'description' => $description,
        'priority' => (int)$priority,
        'project_id' => $projectId,
        'section_id' => $sectionId,
    ];

    // Add dates if they're present
    if (!empty($startDate)) {
        $taskData['start_date'] = $startDate;
    } else {
        // Set to null if empty
        $taskData['start_date'] = null;
    }

    if (!empty($dueDate)) {
        $taskData['due_date'] = $dueDate;
    } else {
        // Set to null if empty
        $taskData['due_date'] = null;
    }

    // Update the task in Firestore
    updateDocument('tasks', $taskId, $taskData);
    $_SESSION['success'] = 'Task updated successfully.';
} catch (Exception $e) {
    $_SESSION['error'] = 'Error updating task: ' . $e->getMessage();
}

// Redirect back to previous page or today if not available
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/today.php';
header("Location: $redirect");
exit;
?>