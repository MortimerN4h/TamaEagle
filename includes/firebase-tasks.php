<?php
// Firebase Task Management Functions for TamaEagle
require_once 'config-firebase.php';

/**
 * Add a new task to Firebase
 */
function addTask($userId, $title, $description = '', $dueDate = null, $priority = 'medium', $projectId = null) {
    global $firebase;
    
    try {
        $taskData = [
            'title' => $title,
            'description' => $description,
            'due_date' => $dueDate,
            'priority' => $priority,
            'project_id' => $projectId,
            'completed' => false,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id' => $userId
        ];
        
        $taskRef = $firebase->getReference('tasks')->push($taskData);
        return $taskRef->getKey();
    } catch (Exception $e) {
        error_log("Error adding task: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all tasks for a user
 */
function getUserTasks($userId, $completed = null) {
    global $firebase;
    
    try {
        $tasksRef = $firebase->getReference('tasks');
        $tasksData = $tasksRef->orderByChild('user_id')->equalTo($userId)->getSnapshot()->getValue();
        
        if (!$tasksData) {
            return [];
        }
        
        $tasks = [];
        foreach ($tasksData as $taskId => $task) {
            $task['id'] = $taskId;
            
            // Filter by completion status if specified
            if ($completed !== null && $task['completed'] != $completed) {
                continue;
            }
            
            $tasks[] = $task;
        }
        
        return $tasks;
    } catch (Exception $e) {
        error_log("Error getting user tasks: " . $e->getMessage());
        return [];
    }
}

/**
 * Get tasks due today
 */
function getTodayTasks($userId) {
    $allTasks = getUserTasks($userId, false);
    $todayTasks = [];
    $today = date('Y-m-d');
    
    foreach ($allTasks as $task) {
        if (isset($task['due_date']) && strpos($task['due_date'], $today) === 0) {
            $todayTasks[] = $task;
        }
    }
    
    return $todayTasks;
}

/**
 * Get upcoming tasks (next 7 days)
 */
function getUpcomingTasks($userId) {
    $allTasks = getUserTasks($userId, false);
    $upcomingTasks = [];
    $today = new DateTime();
    $nextWeek = new DateTime();
    $nextWeek->add(new DateInterval('P7D'));
    
    foreach ($allTasks as $task) {
        if (isset($task['due_date']) && !empty($task['due_date'])) {
            $dueDate = new DateTime($task['due_date']);
            if ($dueDate > $today && $dueDate <= $nextWeek) {
                $upcomingTasks[] = $task;
            }
        }
    }
    
    return $upcomingTasks;
}

/**
 * Complete a task
 */
function completeTask($taskId, $userId) {
    global $firebase;
    
    try {
        $taskRef = $firebase->getReference('tasks/' . $taskId);
        $task = $taskRef->getSnapshot()->getValue();
        
        if (!$task || $task['user_id'] !== $userId) {
            return false;
        }
        
        $taskRef->update([
            'completed' => true,
            'completed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error completing task: " . $e->getMessage());
        return false;
    }
}

/**
 * Uncomplete a task
 */
function uncompleteTask($taskId, $userId) {
    global $firebase;
    
    try {
        $taskRef = $firebase->getReference('tasks/' . $taskId);
        $task = $taskRef->getSnapshot()->getValue();
        
        if (!$task || $task['user_id'] !== $userId) {
            return false;
        }
        
        $taskRef->update([
            'completed' => false,
            'completed_at' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error uncompleting task: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a task
 */
function deleteTask($taskId, $userId) {
    global $firebase;
    
    try {
        $taskRef = $firebase->getReference('tasks/' . $taskId);
        $task = $taskRef->getSnapshot()->getValue();
        
        if (!$task || $task['user_id'] !== $userId) {
            return false;
        }
        
        $taskRef->remove();
        return true;
    } catch (Exception $e) {
        error_log("Error deleting task: " . $e->getMessage());
        return false;
    }
}

/**
 * Update a task
 */
function updateTask($taskId, $userId, $data) {
    global $firebase;
    
    try {
        $taskRef = $firebase->getReference('tasks/' . $taskId);
        $task = $taskRef->getSnapshot()->getValue();
        
        if (!$task || $task['user_id'] !== $userId) {
            return false;
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        $taskRef->update($data);
        
        return true;
    } catch (Exception $e) {
        error_log("Error updating task: " . $e->getMessage());
        return false;
    }
}

/**
 * Add a new project
 */
function addProject($userId, $name, $description = '', $color = '#3498db') {
    global $firebase;
    
    try {
        $projectData = [
            'name' => $name,
            'description' => $description,
            'color' => $color,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id' => $userId
        ];
        
        $projectRef = $firebase->getReference('projects')->push($projectData);
        return $projectRef->getKey();
    } catch (Exception $e) {
        error_log("Error adding project: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all projects for a user
 */
function getUserProjects($userId) {
    global $firebase;
    
    try {
        $projectsRef = $firebase->getReference('projects');
        $projectsData = $projectsRef->orderByChild('user_id')->equalTo($userId)->getSnapshot()->getValue();
        
        if (!$projectsData) {
            return [];
        }
        
        $projects = [];
        foreach ($projectsData as $projectId => $project) {
            $project['id'] = $projectId;
            $projects[] = $project;
        }
        
        return $projects;
    } catch (Exception $e) {
        error_log("Error getting user projects: " . $e->getMessage());
        return [];
    }
}

/**
 * Delete a project and all its tasks
 */
function deleteProject($projectId, $userId) {
    global $firebase;
    
    try {
        $projectRef = $firebase->getReference('projects/' . $projectId);
        $project = $projectRef->getSnapshot()->getValue();
        
        if (!$project || $project['user_id'] !== $userId) {
            return false;
        }
        
        // Delete all tasks in this project
        $tasksRef = $firebase->getReference('tasks');
        $tasksData = $tasksRef->orderByChild('project_id')->equalTo($projectId)->getSnapshot()->getValue();
        
        if ($tasksData) {
            foreach ($tasksData as $taskId => $task) {
                $firebase->getReference('tasks/' . $taskId)->remove();
            }
        }
        
        // Delete the project
        $projectRef->remove();
        return true;
    } catch (Exception $e) {
        error_log("Error deleting project: " . $e->getMessage());
        return false;
    }
}

/**
 * Get task count for dashboard
 */
function getTaskCounts($userId) {
    $allTasks = getUserTasks($userId);
    $completed = getUserTasks($userId, true);
    $pending = getUserTasks($userId, false);
    $today = getTodayTasks($userId);
    $upcoming = getUpcomingTasks($userId);
    
    return [
        'total' => count($allTasks),
        'completed' => count($completed),
        'pending' => count($pending),
        'today' => count($today),
        'upcoming' => count($upcoming)
    ];
}
?>
