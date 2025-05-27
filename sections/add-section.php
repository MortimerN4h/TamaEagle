<?php
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$projectId = getGetData('project_id');
$name = getGetData('name');

// Validate required fields
if (empty($projectId) || empty($name)) {
    $_SESSION['error'] = 'Project ID and section name are required.';
    header("Location: ../index.php");
    exit;
}

try {
    // Verify project belongs to user
    $project = getDocument('projects', $projectId);

    if (!$project || $project['user_id'] !== $userId) {
        $_SESSION['error'] = 'Project not found or you do not have permission to add sections to it.';
        header("Location: ../index.php");
        exit;
    }    // Get all sections for this project
    $whereConditions = [
        ['project_id', '==', $projectId]
    ];
    $sections = getDocuments('sections', $whereConditions);

    // Find the maximum position value manually
    $position = 0;
    foreach ($sections as $section) {
        if (isset($section['position']) && $section['position'] > $position) {
            $position = $section['position'];
        }
    }
    // Increment for new position
    $position++;

    // Create the section in Firestore
    $sectionData = [
        'project_id' => $projectId,
        'name' => $name,
        'position' => $position
    ];

    addDocument('sections', $sectionData);
    $_SESSION['success'] = 'Section added successfully.';
} catch (Exception $e) {
    $_SESSION['error'] = 'Error adding section: ' . $e->getMessage();
}

// Redirect back to project page
header("Location: ../projects/project.php?id=$projectId");
exit;
?>