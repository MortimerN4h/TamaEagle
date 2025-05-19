<?php
require_once 'includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$sectionId = getGetData('id');

// Validate required fields
if (empty($sectionId)) {
    $_SESSION['error'] = 'Section ID is required.';
    header("Location: index.php");
    exit;
}

// Get section details including project ID
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
    header("Location: index.php");
    exit;
}

$section = $sectionResult->fetch_assoc();
$projectId = $section['project_id'];

// Verify project belongs to user
if ($section['user_id'] != $userId) {
    $_SESSION['error'] = 'You do not have permission to delete this section.';
    header("Location: index.php");
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
    header("Location: project.php?id=$projectId");
    exit;
}

$otherSection = $otherSectionResult->fetch_assoc();
$otherSectionId = $otherSection['id'];

// Begin transaction
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

// Redirect back to project page
header("Location: project.php?id=$projectId");
exit;
?>
