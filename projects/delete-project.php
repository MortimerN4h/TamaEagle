<?php
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$projectId = getGetData('id');

// Validate required fields
if (empty($projectId)) {
    $_SESSION['error'] = 'Project ID is required.';
    header("Location: ../index.php");
    exit;
}

try {
    // Get project from Firestore to verify ownership
    $project = getDocument('projects', $projectId);

    if (!$project || $project['user_id'] !== $userId) {
        $_SESSION['error'] = 'Project not found or you do not have permission to delete it.';
        header("Location: ../index.php");
        exit;
    }

    // Get all sections for this project
    $whereConditions = [
        ['project_id', '==', $projectId]
    ];
    $sections = getDocuments('sections', $whereConditions);

    // Get all tasks for this project
    $taskWhereConditions = [
        ['project_id', '==', $projectId],
        ['user_id', '==', $userId]
    ];
    $tasks = getDocuments('tasks', $taskWhereConditions);

    // Delete all tasks in this project
    foreach ($tasks as $task) {
        deleteDocument('tasks', $task['id']);
    }

    // Delete all sections in this project
    foreach ($sections as $section) {
        deleteDocument('sections', $section['id']);
    }

    // Delete the project itself
    deleteDocument('projects', $projectId);

    $_SESSION['success'] = 'Project and all its tasks deleted successfully.';

    // Redirect to home page
    header("Location: ../index.php");
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = 'Error deleting project: ' . $e->getMessage();

    // Redirect back to project page
    header("Location: project.php?id=$projectId");
    exit;
}
?>