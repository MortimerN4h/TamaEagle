<?php
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$sectionId = getGetData('id');

// Validate required fields
if (empty($sectionId)) {
    $_SESSION['error'] = 'Section ID is required.';
    header("Location: ../index.php");
    exit;
}

$projectId = null;
$otherSectionId = null;
$canDelete = false;

if ($GLOBALS['useFirebase']) {
    try {
        // Get section from Firestore
        $section = getDocument('sections', $sectionId);
        
        if (!$section) {
            $_SESSION['error'] = 'Section not found.';
            header("Location: ../index.php");
            exit;
        }
        
        $projectId = $section['project_id'];
        
        // Get project to verify ownership
        $project = getDocument('projects', $projectId);
        
        if (!$project || $project['user_id'] !== $userId) {
            $_SESSION['error'] = 'You do not have permission to delete this section.';
            header("Location: ../index.php");
            exit;
        }
        
        // Find another section to move tasks to
        $whereConditions = [
            ['project_id', '==', $projectId],
            ['id', '!=', $sectionId]
        ];
        $otherSections = getDocuments('sections', $whereConditions, 'position', 'asc', 1);
        
        if (count($otherSections) === 0) {
            $_SESSION['error'] = 'Cannot delete the only section in a project.';
            header("Location: ../projects/project.php?id=$projectId");
            exit;
        }
        
        $otherSectionId = $otherSections[0]['id'];
        $canDelete = true;
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error processing section: ' . $e->getMessage();
        header("Location: ../index.php");
        exit;
    }
} else {
    global $conn;
    
    // Get section details including project ID (MySQL)
    $sectionQuery = "SELECT s.id, s.project_id, p.user_id
                   FROM sections s
                   JOIN projects p ON s.project_id = p.id
                   WHERE s.id = ?";
    $sectionStmt = $conn->prepare($sectionQuery);
    $sectionStmt->bind_param("i", $sectionId);
    $sectionStmt->execute();
    $sectionResult = $sectionStmt->get_result();
    
    if ($sectionResult->num_rows === 0) {
        $_SESSION['error'] = 'Section not found.';
        header("Location: ../index.php");
        exit;
    }
    
    $section = $sectionResult->fetch_assoc();
    $projectId = $section['project_id'];
    
    // Verify project belongs to user
    if ($section['user_id'] != $userId) {
        $_SESSION['error'] = 'You do not have permission to delete this section.';
        header("Location: ../index.php");
        exit;
    }
    
    // Find another section to move tasks to
    $otherSectionQuery = "SELECT id FROM sections WHERE project_id = ? AND id != ? ORDER BY position ASC LIMIT 1";
    $otherSectionStmt = $conn->prepare($otherSectionQuery);
    $otherSectionStmt->bind_param("ii", $projectId, $sectionId);
    $otherSectionStmt->execute();
    $otherSectionResult = $otherSectionStmt->get_result();
    
    if ($otherSectionResult->num_rows === 0) {
        $_SESSION['error'] = 'Cannot delete the only section in a project.';
        header("Location: ../projects/project.php?id=$projectId");
        exit;
    }
    
    $otherSection = $otherSectionResult->fetch_assoc();
    $otherSectionId = $otherSection['id'];
    $canDelete = true;
}

// Now handle the section deletion
if ($canDelete) {
    if ($GLOBALS['useFirebase']) {
        try {
            // In Firestore, we need to update each task individually that belongs to this section
            $taskWhereConditions = [
                ['section_id', '==', $sectionId],
                ['project_id', '==', $projectId]
            ];
            
            $tasks = getDocuments('tasks', $taskWhereConditions);
            
            // Move tasks to another section
            foreach ($tasks as $task) {
                updateDocument('tasks', $task['id'], [
                    'section_id' => $otherSectionId
                ]);
            }
            
            // Delete the section
            deleteDocument('sections', $sectionId);
            
            $_SESSION['success'] = 'Section deleted successfully. Tasks have been moved to another section.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error deleting section: ' . $e->getMessage();
        }
    } else {
        global $conn;
        
        // Begin transaction for MySQL
        $conn->begin_transaction();
        
        try {
            // Move tasks from this section to another section
            $moveTasksQuery = "UPDATE tasks SET section_id = ? WHERE section_id = ? AND project_id = ?";
            $moveTasksStmt = $conn->prepare($moveTasksQuery);
            $moveTasksStmt->bind_param("iii", $otherSectionId, $sectionId, $projectId);
            $moveTasksStmt->execute();
            
            // Delete the section
            $deleteSectionQuery = "DELETE FROM sections WHERE id = ?";
            $deleteSectionStmt = $conn->prepare($deleteSectionQuery);
            $deleteSectionStmt->bind_param("i", $sectionId);
            $deleteSectionStmt->execute();
            
            // Commit transaction
            $conn->commit();
            $_SESSION['success'] = 'Section deleted successfully. Tasks have been moved to another section.';
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['error'] = 'Error deleting section: ' . $e->getMessage();
        }
    }
}

// Redirect back to project page
header("Location: ../projects/project.php?id=$projectId");
exit;
?>