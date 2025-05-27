<?php
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20;

// Get completed tasks using Firestore
$completedTasksConditions = [
    ['user_id', '==', $userId],
    ['is_completed', '==', true]
];

// Get tasks from Firestore
// Note: Firestore doesn't support direct pagination with OFFSET like SQL
// We'll get all tasks and then handle pagination in PHP
$allCompletedTasks = getDocuments('tasks', $completedTasksConditions, 'completed_at', 'desc');

// Get total count for pagination
$totalTasks = count($allCompletedTasks);
$totalPages = ceil($totalTasks / $limit);

// Handle pagination manually
$start = ($page - 1) * $limit;
$end = min($start + $limit, $totalTasks);
$paginatedTasks = array_slice($allCompletedTasks, $start, $limit);

// Process tasks with project data
foreach ($paginatedTasks as &$task) {
    if (isset($task['project_id'])) {
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
    }    // Format the completed_at timestamp for grouping
    if (isset($task['completed_at'])) {
        // Firestore timestamps are automatically converted to PHP \DateTimeImmutable
        if (is_object($task['completed_at'])) {
            $dateTime = $task['completed_at']->format('Y-m-d H:i:s');
        } 
        // Handle array-based timestamp (Firestore format)
        else if (is_array($task['completed_at'])) {
            // Check if it's a Firestore timestamp array
            if (isset($task['completed_at']['@type']) && $task['completed_at']['@type'] === 'firestore.googleapis.com/Timestamp') {
                if (isset($task['completed_at']['value']['seconds'])) {
                    $seconds = $task['completed_at']['value']['seconds'];
                    $dateTime = date('Y-m-d H:i:s', $seconds);
                } else {
                    $dateTime = date('Y-m-d H:i:s'); // fallback to current time
                }
            } else {
                $dateTime = date('Y-m-d H:i:s'); // fallback to current time
            }
        } 
        // Handle string timestamp
        else if (is_string($task['completed_at'])) {
            $dateTime = date('Y-m-d H:i:s', strtotime($task['completed_at']));
        }
        // Default fallback
        else {
            $dateTime = date('Y-m-d H:i:s');
        }
        $task['completed_at_formatted'] = $dateTime;
    } else {
        $task['completed_at_formatted'] = date('Y-m-d H:i:s');
    }
}

// Group tasks by completion date
$tasksByDate = [];
foreach ($paginatedTasks as $task) {
    // Extract the date part from the completed_at timestamp
    $completedDate = substr($task['completed_at_formatted'], 0, 10);

    if (!isset($tasksByDate[$completedDate])) {
        $tasksByDate[$completedDate] = [
            'date' => $completedDate,
            'formatted' => date('F j, Y', strtotime($completedDate)),
            'tasks' => []
        ];
    }

    $tasksByDate[$completedDate]['tasks'][] = $task;
}

// Page title
$pageTitle = 'Completed Tasks';

// Include header
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title"><?php echo $pageTitle; ?></h1>

        <?php if ($totalTasks > 0): ?>
            <div class="btn-group">
                <a href="../tasks/clear-completed.php" class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('Are you sure you want to delete all completed tasks? This action cannot be undone.');">
                    Clear All
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (count($tasksByDate) > 0): ?>
        <?php foreach ($tasksByDate as $dateData): ?>
            <div class="completed-date-group mb-4">
                <h3 class="date-header"><?php echo $dateData['formatted']; ?></h3>

                <ul class="task-list completed-list">
                    <?php foreach ($dateData['tasks'] as $task): ?>
                        <li class="task-item completed" data-id="<?php echo $task['id']; ?>">
                            <div class="task-header">
                                <div class="task-checkbox">
                                    <button class="btn-checkbox completed" data-id="<?php echo $task['id']; ?>">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </button>
                                </div>

                                <div class="task-name">
                                    <span class="text-decoration-line-through"><?php echo htmlspecialchars($task['name']); ?></span>
                                </div>

                                <?php if (!empty($task['project_name'])): ?>
                                    <div class="task-project">
                                        <span class="project-badge" style="background-color: <?php echo $task['project_color']; ?>"></span>
                                        <?php echo htmlspecialchars($task['project_name']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="task-actions">
                                    <button class="btn btn-sm delete-task" data-id="<?php echo $task['id']; ?>" title="Delete Task">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <?php if ($totalPages > 1): ?>
            <div class="pagination justify-content-center">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($page - 1); ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($page + 1); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-check2-circle"></i>
            </div>
            <h3>No completed tasks yet</h3>
            <p>Tasks you complete will appear here</p>
        </div>
    <?php endif; ?>
</div>

<!-- Include footer -->
<?php include '../includes/footer.php'; ?>