<?php
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$sectionId = getGetData('id');
$name = getGetData('name');

// Validate required fields
if (empty($sectionId) || empty($name)) {
    $_SESSION['error'] = 'Section ID and name are required.';
    header("Location: ../index.php");
    exit;
}

$projectId = null;

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
        $_SESSION['error'] = 'You do not have permission to modify this section.';
        header("Location: ../index.php");
        exit;
    }

    // Update the section name in Firestore
    updateDocument('sections', $sectionId, [
        'name' => $name
    ]);

    $_SESSION['success'] = 'Section renamed successfully.';
} catch (Exception $e) {
    $_SESSION['error'] = 'Error renaming section: ' . $e->getMessage();
}

// Redirect back to project page
header("Location: ../projects/project.php?id=$projectId");
exit;
?>