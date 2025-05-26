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
$sectionId = $_POST['section_id'];
$position = intval($_POST['position']);

try {
    // Verify section exists
    $section = getDocument('sections', $sectionId);
    
    if (!$section) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Section not found']);
        exit;
    }
    
    $projectId = $section['project_id'];
    
    // Verify user owns the project
    $project = getDocument('projects', $projectId);
    
    if (!$project || $project['user_id'] !== $userId) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        exit;
    }
    
    // Update section position in Firestore
    updateDocument('sections', $sectionId, [
        'position' => $position
    ]);
    
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
    exit;
} catch (Exception $e) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error updating section position: ' . $e->getMessage()]);
    exit;
}

?>
?>