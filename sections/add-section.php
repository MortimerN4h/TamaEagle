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

if ($GLOBALS['useFirebase']) {
    try {
        // Verify project belongs to user
        $project = getDocument('projects', $projectId);

        if (!$project || $project['user_id'] !== $userId) {
            $_SESSION['error'] = 'Project not found or you do not have permission to add sections to it.';
            header("Location: ../index.php");
            exit;
        }

        // Get max position value for this project
        $whereConditions = [
            ['project_id', '==', $projectId]
        ];
        $sections = getDocuments('sections', $whereConditions, 'position', 'desc', 1);
        
        $position = 0;
        if (count($sections) > 0) {
            $position = $sections[0]['position'] + 1;
        }

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
} else {
    global $conn;
    
    // Verify project belongs to user (MySQL)
    $projectQuery = "SELECT id FROM projects WHERE id = ? AND user_id = ?";
    $projectStmt = $conn->prepare($projectQuery);
    $projectStmt->bind_param("ii", $projectId, $userId);
    $projectStmt->execute();
    $projectResult = $projectStmt->get_result();
    
    if ($projectResult->num_rows === 0) {
        $_SESSION['error'] = 'Project not found or you do not have permission to add sections to it.';
        header("Location: ../index.php");
        exit;
    }
    
    // Get max position value for this project
    $positionQuery = "SELECT COALESCE(MAX(position), 0) + 1 as next_position FROM sections WHERE project_id = ?";
    $positionStmt = $conn->prepare($positionQuery);
    $positionStmt->bind_param("i", $projectId);
    $positionStmt->execute();
    $positionResult = $positionStmt->get_result();
    $positionRow = $positionResult->fetch_assoc();
    $position = $positionRow['next_position'];
    
    // Insert the section in MySQL
    $query = "INSERT INTO sections (project_id, name, position) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isi", $projectId, $name, $position);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Section added successfully.';
    } else {
        $_SESSION['error'] = 'Error adding section: ' . $conn->error;
    }
}

// Redirect back to project page
header("Location: ../projects/project.php?id=$projectId");
exit;
?>