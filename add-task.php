<?php
require_once 'includes/config.php';
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
if (!empty($sectionId)) {
    $sectionQuery = "SELECT id FROM sections WHERE id = ? AND project_id = ?";
    $stmt = $conn->prepare($sectionQuery);
    $stmt->bind_param("ii", $sectionId, $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Invalid section selected.';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

// Get max position for ordering
$positionQuery = "SELECT COALESCE(MAX(position), 0) + 1 as next_position FROM tasks WHERE user_id = ?";
$positionParams = [$userId];
$positionTypes = "i";

// If project ID and section ID are provided, get position within that section
if (!empty($projectId) && !empty($sectionId)) {
    $positionQuery = "SELECT COALESCE(MAX(position), 0) + 1 as next_position FROM tasks WHERE project_id = ? AND section_id = ?";
    $positionParams = [$projectId, $sectionId];
    $positionTypes = "ii";
} 
// If only project ID is provided, get position within that project
elseif (!empty($projectId)) {
    $positionQuery = "SELECT COALESCE(MAX(position), 0) + 1 as next_position FROM tasks WHERE project_id = ?";
    $positionParams = [$projectId];
    $positionTypes = "i";
}

$positionStmt = $conn->prepare($positionQuery);
$positionStmt->bind_param($positionTypes, ...$positionParams);
$positionStmt->execute();
$positionResult = $positionStmt->get_result();
$positionRow = $positionResult->fetch_assoc();
$position = $positionRow['next_position'];

// Insert the task
$query = "INSERT INTO tasks (user_id, project_id, section_id, name, description, start_date, due_date, priority, position) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiisssiii", $userId, $projectId, $sectionId, $name, $description, $startDate, $dueDate, $priority, $position);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Task added successfully.';
    
    // Redirect back to previous page or inbox if not available
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'inbox.php';
    header("Location: $redirect");
    exit;
} else {
    $_SESSION['error'] = 'Error adding task: ' . $conn->error;
    
    // Redirect back to previous page or inbox if not available
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'inbox.php';
    header("Location: $redirect");
    exit;
}
?>
