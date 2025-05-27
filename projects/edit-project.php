<?php
require_once '../includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../index.php");
    exit;
}

$userId = getCurrentUserId();
$projectId = getPostData('project_id');
$name = getPostData('name');
$color = getPostData('color');

// Validate required fields
if (empty($projectId) || empty($name)) {
    $_SESSION['error'] = 'Project ID and name are required.';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Validate color
if (empty($color)) {
    $color = '#ff0000'; // Default to red
}

try {
    // Get project from Firestore to verify ownership
    $project = getDocument('projects', $projectId);

    if (!$project || $project['user_id'] !== $userId) {
        $_SESSION['error'] = 'Project not found or you do not have permission to edit it.';
        header("Location: ../index.php");
        exit;
    }

    // Update project in Firestore
    $projectData = [
        'name' => $name,
        'color' => $color,
        'updated_at' => firestoreServerTimestamp()
    ];

    updateDocument('projects', $projectId, $projectData);

    $_SESSION['success'] = 'Project updated successfully.';

    // Redirect to the project page
    header("Location: project.php?id=$projectId");
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = 'Error updating project: ' . $e->getMessage();

    // Redirect back to project page
    header("Location: project.php?id=$projectId");
    exit;
}
?>