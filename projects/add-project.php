<?php
require_once '../includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../index.php");
    exit;
}

$userId = getCurrentUserId();
$name = getPostData('name');
$color = getPostData('color');

// Validate required fields
if (empty($name)) {
    $_SESSION['error'] = 'Project name is required.';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Validate color
if (empty($color)) {
    $color = '#ff0000'; // Default to red
}

try {
    // Create project in Firestore
    $projectData = [
        'user_id' => $userId,
        'name' => $name,
        'color' => $color,
        'created_at' => firestoreServerTimestamp()
    ];

    $projectId = addDocument('projects', $projectData);

    // Create default section for this project
    $defaultSectionData = [
        'project_id' => $projectId,
        'name' => 'To Do',
        'position' => 0
    ];

    addDocument('sections', $defaultSectionData);

    $_SESSION['success'] = 'Project created successfully.';

    // Redirect to the new project page
    header("Location: project.php?id=$projectId");
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = 'Error creating project: ' . $e->getMessage();    // Redirect back to previous page or today if not available
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/today.php';
    header("Location: $redirect");
    exit;
}
?>