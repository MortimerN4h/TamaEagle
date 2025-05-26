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

if ($GLOBALS['useFirebase']) {
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
        $_SESSION['error'] = 'Error creating project: ' . $e->getMessage();
        
        // Redirect back to previous page or inbox if not available
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/inbox.php';
        header("Location: $redirect");
        exit;
    }
} else {
    global $conn;
    
    // Insert the project in MySQL
    $query = "INSERT INTO projects (user_id, name, color, created_at) VALUES (?, ?, ?, NOW())";
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
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/inbox.php';
        header("Location: $redirect");
        exit;
    }
}
?>