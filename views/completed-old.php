<?php
require_once '../includes/config-firebase.php';
requireLogin();

$userId = getCurrentUserId();

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get completed tasks with pagination
$tasksQuery = "
    SELECT t.*, p.name as project_name, p.color as project_color 
    FROM tasks t
    LEFT JOIN projects p ON t.project_id = p.id
    WHERE t.user_id = ? AND t.is_completed = 1
    ORDER BY t.completed_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($tasksQuery);
$stmt->bind_param("iii", $userId, $limit, $offset);
$stmt->execute();
$tasksResult = $stmt->get_result();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM tasks WHERE user_id = ? AND is_completed = 1";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("i", $userId);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalTasks = $countRow['total'];
$totalPages = ceil($totalTasks / $limit);

// Group tasks by completion date
$tasksByDate = [];
while ($task = $tasksResult->fetch_assoc()) {
    $completedDate = date('Y-m-d', strtotime($task['completed_at']));

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
$pageTitle = 'Completed';

// Include header
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title"><?php echo $pageTitle; ?></h1>
        <?php if ($totalTasks > 0): ?>
            <a href="clear-completed.php" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to permanently delete all completed tasks?');">
                <i class="fas fa-trash"></i> Clear All
            </a>
        <?php endif; ?>
    </div>

    <?php if (count($tasksByDate) > 0): ?>
        <?php foreach ($tasksByDate as $date => $dayData): ?>
            <div class="section-container mb-4">
                <div class="section-header">
                    <h4 class="section-title">
                        <?php if (isToday($date)): ?>
                            <span>Today</span>
                        <?php elseif (date('Y-m-d', strtotime('-1 day')) === $date): ?>
                            <span>Yesterday</span>
                        <?php else: ?>
                            <span><?php echo $dayData['formatted']; ?></span>
                        <?php endif; ?>
                    </h4>
                </div>

                <ul class="task-list">
                    <?php foreach ($dayData['tasks'] as $task): ?>
                        <?php $priorityClass = 'priority-' . $task['priority']; ?>
                        <li class="task-item <?php echo $priorityClass; ?>" style="opacity: 0.7;">
                            <div class="task-header">
                                <div class="task-checkbox">
                                    <a href="uncomplete-task.php?id=<?php echo $task['id']; ?>" class="uncomplete-task" data-id="<?php echo $task['id']; ?>">
                                        <i class="fas fa-check-circle text-success"></i>
                                    </a>
                                </div>
                                <h5 class="task-name text-decoration-line-through"><?php echo htmlspecialchars($task['name']); ?></h5>
                                <span class="task-date">
                                    <?php echo date('H:i', strtotime($task['completed_at'])); ?>
                                </span>
                            </div>

                            <?php if (!empty($task['description'])): ?>
                                <div class="task-description">
                                    <?php echo nl2br(htmlspecialchars($task['description'])); ?>
                                </div>
                            <?php endif; ?>

                            <div class="task-footer">
                                <?php if (!empty($task['project_name'])): ?>
                                    <span class="task-project" style="background-color: <?php echo $task['project_color']; ?>20;">
                                        <i class="fa fa-project-diagram" style="color: <?php echo $task['project_color']; ?>"></i>
                                        <?php echo htmlspecialchars($task['project_name']); ?>
                                    </span>
                                <?php endif; ?>

                                <div class="task-actions">
                                    <a href="delete-task.php?id=<?php echo $task['id']; ?>" class="delete-task" onclick="return confirm('Are you sure you want to delete this task?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info">
            You don't have any completed tasks.
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>