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
        ['project_id', '==', $projectId]
    ];
    $allSections = getDocuments('sections', $whereConditions);

    // Filter out the section we want to delete and find another one
    $otherSections = array_filter($allSections, function ($sec) use ($sectionId) {
        return $sec['id'] !== $sectionId;
    });

    if (count($otherSections) === 0) {
        $_SESSION['error'] = 'Cannot delete the only section in a project.';
        header("Location: ../projects/project.php?id=$projectId");
        exit;
    }

    // Get the first available section to move tasks to
    $otherSection = reset($otherSections);
    $otherSectionId = $otherSection['id'];
    // In Firestore, we need to update each task individually that belongs to this section
    // Get all tasks for this project
    $taskWhereConditions = [
        ['project_id', '==', $projectId]
    ];

    $allTasks = getDocuments('tasks', $taskWhereConditions);

    // Filter to find tasks in this section
    $tasksInSection = array_filter($allTasks, function ($task) use ($sectionId) {
        return isset($task['section_id']) && $task['section_id'] === $sectionId;
    });

    // Move tasks to another section
    foreach ($tasksInSection as $task) {
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

// Redirect back to project page
header("Location: ../projects/project.php?id=$projectId");
exit;
?>