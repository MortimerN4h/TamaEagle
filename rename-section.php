<?php
require_once 'includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$sectionId = getGetData('id');
$name = getGetData('name');

// Validate required fields
if (empty($sectionId) || empty($name)) {
    $_SESSION['error'] = 'Section ID and name are required.';
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
    $_SESSION['error'] = 'You do not have permission to modify this section.';
    header("Location: index.php");
    exit;
}

// Update the section name
$query = "UPDATE sections SET name = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $name, $sectionId);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Section renamed successfully.';
} else {
    $_SESSION['error'] = 'Error renaming section: ' . $conn->error;
}

// Redirect back to project page
header("Location: project.php?id=$projectId");
exit;
?>
