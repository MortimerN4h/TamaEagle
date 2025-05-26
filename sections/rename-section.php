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
} else {
    global $conn;
    
    // Get section details including project ID from MySQL
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
        $_SESSION['error'] = 'You do not have permission to modify this section.';
        header("Location: ../index.php");
        exit;
    }
    
    // Update the section name in MySQL
    $query = "UPDATE sections SET name = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $name, $sectionId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Section renamed successfully.';
    } else {
        $_SESSION['error'] = 'Error renaming section: ' . $conn->error;
    }
}

// Redirect back to project page
header("Location: ../projects/project.php?id=$projectId");
exit;
?>