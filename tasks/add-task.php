<?php
require_once '../includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php");
    exit;
}

$userId = getCurrentUserId();
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
if (!empty($sectionId)) {
    $section = getDocument('sections', $sectionId);
    
    if (!$section || $section['project_id'] !== $projectId) {
        $_SESSION['error'] = 'Invalid section selected.';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

$position = 0;

// Get max position for ordering
$whereConditions = [];

// If project ID and section ID are provided, get position within that section
if (!empty($projectId) && !empty($sectionId)) {
    $whereConditions = [
        ['project_id', '==', $projectId],
        ['section_id', '==', $sectionId]
    ];
}
// If only project ID is provided, get position within that project
elseif (!empty($projectId)) {
    $whereConditions = [
        ['project_id', '==', $projectId]
    ];
}
// Otherwise get position for all user tasks
else {
    $whereConditions = [
        ['user_id', '==', $userId]
    ];
}

// Get tasks to determine max position
$positionTasks = getDocuments('tasks', $whereConditions, 'position', 'desc');
if (count($positionTasks) > 0) {
    $position = intval($positionTasks[0]['position']) + 1;
}

// Create task data for Firestore
$taskData = [
    'user_id' => $userId,
    'name' => $name,
    'description' => $description,
    'start_date' => empty($startDate) ? null : $startDate,
    'due_date' => empty($dueDate) ? null : $dueDate,
    'priority' => intval($priority),
    'position' => $position,
    'is_completed' => false
];

// Add project and section if provided
if (!empty($projectId)) {
    $taskData['project_id'] = $projectId;
}

if (!empty($sectionId)) {
    $taskData['section_id'] = $sectionId;
}

try {
    // Add task to Firestore
    $taskId = addDocument('tasks', $taskData);
    $_SESSION['success'] = 'Task added successfully.';
} catch (Exception $e) {
    $_SESSION['error'] = 'Error adding task: ' . $e->getMessage();
}

// Redirect back to previous page or inbox if not available
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/inbox.php';
header("Location: $redirect");
exit;
?>