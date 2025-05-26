<?php
require_once '../includes/config.php';
requireLogin();

// Check if it's an AJAX request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    // Return JSON error if not AJAX
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$userId = getCurrentUserId();
$taskId = $_POST['task_id'];
$sectionId = isset($_POST['section_id']) && !empty($_POST['section_id']) ? $_POST['section_id'] : null;
$position = intval($_POST['position']);

try {
    // Verify task belongs to user
    $task = getDocument('tasks', $taskId);
    
    if (!$task || $task['user_id'] !== $userId) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Task not found or permission denied']);
        exit;
    }
    
    $projectId = $task['project_id'] ?? null;
    
    // If section is provided, verify it exists
    if ($sectionId !== null) {
        $section = getDocument('sections', $sectionId);
        
        if (!$section) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Section not found']);
            exit;
        }
        
        // If task has a project ID, ensure section belongs to same project
        if ($projectId !== null && $section['project_id'] != $projectId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Section does not belong to task\'s project']);
            exit;
        }
        
        // Update task's project ID if moving to a section
        $projectId = $section['project_id'];
    }
    
    // Update the task in Firestore
    updateDocument('tasks', $taskId, [
        'section_id' => $sectionId,
        'project_id' => $projectId,
        'position' => $position
    ]);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Task position updated successfully in Firestore',
        'data' => [
            'task_id' => $taskId,
            'section_id' => $sectionId,
            'project_id' => $projectId,
            'position' => $position
        ]
    ]);
    exit;
} catch (Exception $e) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error updating task position: ' . $e->getMessage()]);
    exit;
}

?>
?>