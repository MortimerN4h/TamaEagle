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
$sectionId = intval($_POST['section_id']);
$position = intval($_POST['position']);

// Verify section belongs to user's project
$sectionQuery = "SELECT s.id, s.project_id, p.user_id
                FROM sections s
                JOIN projects p ON s.project_id = p.id
                WHERE s.id = ?";
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
$projectId = $section['project_id'];

// Verify user owns the project
if ($section['user_id'] != $userId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// Begin transaction to update positions
$conn->begin_transaction();

try {
    // Move all sections down to make room at the specified position
    $shiftQuery = "UPDATE sections 
                  SET position = position + 1 
                  WHERE project_id = ? 
                    AND id != ? 
                    AND position >= ?
                  ORDER BY position DESC";
    $shiftStmt = $conn->prepare($shiftQuery);
    $shiftStmt->bind_param("iii", $projectId, $sectionId, $position);
    $shiftStmt->execute();

    // Set the position of our section
    $positionQuery = "UPDATE sections SET position = ? WHERE id = ?";
    $positionStmt = $conn->prepare($positionQuery);
    $positionStmt->bind_param("ii", $position, $sectionId);
    $positionStmt->execute();

    // Commit transaction
    $conn->commit();

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Section position updated successfully',
        'data' => [
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
    echo json_encode(['success' => false, 'message' => 'Error updating section position: ' . $e->getMessage()]);
}
?>