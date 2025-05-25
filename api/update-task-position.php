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
$taskId = intval($_POST['task_id']);
$sectionId = isset($_POST['section_id']) && !empty($_POST['section_id']) ? intval($_POST['section_id']) : null;
$position = intval($_POST['position']);

// Verify task belongs to user
$taskQuery = "SELECT t.id, t.project_id FROM tasks t WHERE t.id = ? AND t.user_id = ?";
$taskStmt = $conn->prepare($taskQuery);
$taskStmt->bind_param("ii", $taskId, $userId);
$taskStmt->execute();
$taskResult = $taskStmt->get_result();

if ($taskResult->num_rows === 0) {
    // Return JSON error if task not found
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Task not found or permission denied']);
    exit;
}

$task = $taskResult->fetch_assoc();
$projectId = $task['project_id'];

// If section is provided, verify it belongs to the same project as the task
if ($sectionId !== null) {
    $sectionQuery = "SELECT s.id, s.project_id FROM sections s WHERE s.id = ?";
    $sectionStmt = $conn->prepare($sectionQuery);
    $sectionStmt->bind_param("i", $sectionId);
    $sectionStmt->execute();
    $sectionResult = $sectionStmt->get_result();

    if ($sectionResult->num_rows === 0) {
        // Return JSON error if section not found
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Section not found']);
        exit;
    }

    $section = $sectionResult->fetch_assoc();

    // If task has a project ID, ensure section belongs to same project
    if ($projectId !== null && $section['project_id'] != $projectId) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Section does not belong to task\'s project']);
        exit;
    }

    // Update task's project ID if moving to a section
    $projectId = $section['project_id'];
}

// Begin transaction to update positions
$conn->begin_transaction();

try {
    // First update the task's section and project
    $updateTaskQuery = "UPDATE tasks SET section_id = ?, project_id = ? WHERE id = ? AND user_id = ?";
    $updateTaskStmt = $conn->prepare($updateTaskQuery);
    $updateTaskStmt->bind_param("iiii", $sectionId, $projectId, $taskId, $userId);
    $updateTaskStmt->execute();

    // Update positions of other tasks in the same section/project
    if ($sectionId !== null) {
        // Move all tasks down to make room at the specified position
        $shiftQuery = "UPDATE tasks 
                      SET position = position + 1 
                      WHERE section_id = ? 
                        AND id != ? 
                        AND position >= ?
                      ORDER BY position DESC";
        $shiftStmt = $conn->prepare($shiftQuery);
        $shiftStmt->bind_param("iii", $sectionId, $taskId, $position);
        $shiftStmt->execute();

        // Set the position of our task
        $positionQuery = "UPDATE tasks SET position = ? WHERE id = ?";
        $positionStmt = $conn->prepare($positionQuery);
        $positionStmt->bind_param("ii", $position, $taskId);
        $positionStmt->execute();
    } elseif ($projectId !== null) {
        // Move all tasks in the project without section down
        $shiftQuery = "UPDATE tasks 
                      SET position = position + 1 
                      WHERE project_id = ? 
                        AND section_id IS NULL
                        AND id != ? 
                        AND position >= ?
                      ORDER BY position DESC";
        $shiftStmt = $conn->prepare($shiftQuery);
        $shiftStmt->bind_param("iii", $projectId, $taskId, $position);
        $shiftStmt->execute();

        // Set the position of our task
        $positionQuery = "UPDATE tasks SET position = ? WHERE id = ?";
        $positionStmt = $conn->prepare($positionQuery);
        $positionStmt->bind_param("ii", $position, $taskId);
        $positionStmt->execute();
    } else {
        // For tasks not in any project (inbox)
        $shiftQuery = "UPDATE tasks 
                      SET position = position + 1 
                      WHERE project_id IS NULL 
                        AND id != ? 
                        AND user_id = ?
                        AND position >= ?
                      ORDER BY position DESC";
        $shiftStmt = $conn->prepare($shiftQuery);
        $shiftStmt->bind_param("iii", $taskId, $userId, $position);
        $shiftStmt->execute();

        // Set the position of our task
        $positionQuery = "UPDATE tasks SET position = ? WHERE id = ?";
        $positionStmt = $conn->prepare($positionQuery);
        $positionStmt->bind_param("ii", $position, $taskId);
        $positionStmt->execute();
    }

    // Commit transaction
    $conn->commit();

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Task position updated successfully',
        'data' => [
            'task_id' => $taskId,
            'section_id' => $sectionId,
            'project_id' => $projectId,
            'position' => $position
        ]
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error updating task position: ' . $e->getMessage()]);
}
?>