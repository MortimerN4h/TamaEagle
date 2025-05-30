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
$sectionId = $_POST['section_id'] ?? null;
$position = isset($_POST['position']) ? intval($_POST['position']) : 0;

// Validate required parameters
if (!$sectionId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Section ID is required']);
    exit;
}

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
    }    // Update section position in Firestore
    $updateData = [
        'position' => $position
    ];
    
    $updateResult = updateDocument('sections', $sectionId, $updateData);
    
    if (!$updateResult) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update section in database',
            'data' => [
                'section_id' => $sectionId,
                'update_data' => $updateData
            ]
        ]);
        exit;
    }
    
    // Reorder other sections in the same project to maintain consistency
    $sectionsWhereConditions = [
        ['project_id', '==', $projectId]
    ];
    
    $projectSections = getDocuments('sections', $sectionsWhereConditions, 'position', 'asc');
    $positionCounter = 0;
    
    // Update positions for sections in the project
    foreach ($projectSections as $projectSection) {
        // Skip the section that was just moved
        if ($projectSection['id'] === $sectionId) {
            $positionCounter++;
            continue;
        }
        
        // Only update if position needs to change
        if ((int)$projectSection['position'] !== $positionCounter) {
            $sectionUpdateData = [
                'position' => $positionCounter
            ];
            updateDocument('sections', $projectSection['id'], $sectionUpdateData);
            error_log('Reordering section: sectionID=' . $projectSection['id'] . ', new position=' . $positionCounter);
        }
        
        $positionCounter++;
    }
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Section position updated successfully',
        'data' => [
            'section_id' => $sectionId,
            'project_id' => $projectId,
            'position' => $position,
            'update_time' => date('Y-m-d H:i:s')
        ]
    ]);
    
    error_log('Updating section position: sectionID=' . $sectionId . ', position=' . $position);
    exit;
} catch (Exception $e) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error updating section position: ' . $e->getMessage()]);
    exit;
}
?>
