<?php
/**
 * API endpoint to get task notifications
 * Returns overdue tasks and tasks due in the next 3 days, sorted by priority
 */
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$today = date('Y-m-d');
$threeDaysLater = date('Y-m-d', strtotime('+3 days'));

// Get overdue tasks
$overdueConditions = [
    ['user_id', '==', $userId],
    ['is_completed', '==', false],
    ['due_date', '<', $today]
];

// Get upcoming tasks (due within next 3 days)
$upcomingConditions = [
    ['user_id', '==', $userId],
    ['is_completed', '==', false],
    ['due_date', '>=', $today],
    ['due_date', '<=', $threeDaysLater]
];

try {
    // Get overdue tasks sorted by priority (highest first) and then by due date (oldest first)
    $overdueTasks = getDocuments('tasks', $overdueConditions, 'priority', 'desc');
    
    // Sort overdue tasks by due date (oldest first for same priority)
    usort($overdueTasks, function($a, $b) {
        if ($a['priority'] != $b['priority']) {
            return $b['priority'] - $a['priority']; // Higher priority first
        }
        return strcmp($a['due_date'], $b['due_date']); // Older due date first
    });
    
    // Get upcoming tasks sorted by priority (highest first) and due date
    $upcomingTasks = getDocuments('tasks', $upcomingConditions, 'priority', 'desc');
    
    // Sort upcoming tasks by due date (soonest first for same priority)
    usort($upcomingTasks, function($a, $b) {
        if ($a['priority'] != $b['priority']) {
            return $b['priority'] - $a['priority']; // Higher priority first
        }
        return strcmp($a['due_date'], $b['due_date']); // Sooner due date first
    });
    
    // Add project information
    $processedOverdueTasks = processTasksWithProjectInfo($overdueTasks);
    $processedUpcomingTasks = processTasksWithProjectInfo($upcomingTasks);
    
    // Return the result as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'overdue' => $processedOverdueTasks,
        'upcoming' => $processedUpcomingTasks
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching notifications: ' . $e->getMessage()]);
}

/**
 * Process tasks to include project information
 */
function processTasksWithProjectInfo($tasks) {
    $result = [];
    
    foreach ($tasks as $task) {
        // Add project info if the task has a project ID
        if (!empty($task['project_id'])) {
            $project = getDocument('projects', $task['project_id']);
            if ($project) {
                $task['project_name'] = $project['name'];
                $task['project_color'] = $project['color'];
            } else {
                $task['project_name'] = null;
                $task['project_color'] = null;
            }
        } else {
            $task['project_name'] = null;
            $task['project_color'] = null;
        }
        
        $result[] = $task;
    }
    
    return $result;
}
