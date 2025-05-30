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
$taskId = $_POST['task_id'] ?? null;
$sectionId = isset($_POST['section_id']) && !empty($_POST['section_id']) ? $_POST['section_id'] : null;
$position = isset($_POST['position']) ? intval($_POST['position']) : 0;

// Validate required parameters
if (!$taskId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Task ID is required']);
    exit;
}

try {
    // Verify task belongs to user
    $task = getDocument('tasks', $taskId);
    
    if (!$task || $task['user_id'] !== $userId) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Task not found or permission denied']);
        exit;
    }
    
    $projectId = $task['project_id'] ?? null;
    $oldSectionId = $task['section_id'] ?? null;
    
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
    
    // If position is negative, set it to 0
    if ($position < 0) {
        $position = 0;
    }
    
    // Update the task in Firestore
    $updateData = [
        'section_id' => $sectionId,
        'position' => $position
    ];
    
    // Only update project ID if it has changed or if it's null
    if ($projectId !== null) {
        $updateData['project_id'] = $projectId;
    }
      // Update the task
    $updateResult = updateDocument('tasks', $taskId, $updateData);
    
    if (!$updateResult) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update task in database',
            'data' => [
                'task_id' => $taskId,
                'update_data' => $updateData
            ]
        ]);
        exit;
    }
      // Reorder other tasks in the same section to maintain consistency
    if ($sectionId !== null) {
        // Get all tasks in the current section
        $tasksWhereConditions = [
            ['section_id', '==', $sectionId],
            ['user_id', '==', $userId],
            ['is_completed', '==', false]
        ];
        
        $sectionTasks = getDocuments('tasks', $tasksWhereConditions, 'position', 'asc');
        $positionCounter = 0;
        
        // Update positions for tasks in the section
        foreach ($sectionTasks as $sectionTask) {
            // Skip the task that was just moved
            if ($sectionTask['id'] === $taskId) {
                $positionCounter++;
                continue;
            }
            
            // Only update if position needs to change
            if ((int)$sectionTask['position'] !== $positionCounter) {
                $taskUpdateData = [
                    'position' => $positionCounter
                ];
                updateDocument('tasks', $sectionTask['id'], $taskUpdateData);
                error_log('Reordering task: taskID=' . $sectionTask['id'] . ', new position=' . $positionCounter);
            }
            
            $positionCounter++;
        }
    }
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Task position updated successfully',
        'data' => [
            'task_id' => $taskId,
            'section_id' => $sectionId,
            'project_id' => $projectId,
            'position' => $position,
            'update_time' => date('Y-m-d H:i:s')
        ]
    ]);

    error_log('Updating task position: taskID=' . $taskId . ', sectionID=' . $sectionId . ', position=' . $position);

    exit;
} catch (Exception $e) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error updating task position: ' . $e->getMessage()]);
    exit;
}
?>
