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

// Insert the project
$query = "INSERT INTO projects (user_id, name, color) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $userId, $name, $color);

if ($stmt->execute()) {
    $projectId = $stmt->insert_id;

    // Create default section for this project
    $defaultSectionName = "To Do";
    $defaultSectionQuery = "INSERT INTO sections (project_id, name, position) VALUES (?, ?, 0)";
    $defaultSectionStmt = $conn->prepare($defaultSectionQuery);
    $defaultSectionStmt->bind_param("is", $projectId, $defaultSectionName);
    $defaultSectionStmt->execute();

    $_SESSION['success'] = 'Project created successfully.';

    // Redirect to the new project page
    header("Location: project.php?id=$projectId");
    exit;
} else {
    $_SESSION['error'] = 'Error creating project: ' . $conn->error;

    // Redirect back to previous page or inbox if not available
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
    header("Location: $redirect");
    exit;
}
?>