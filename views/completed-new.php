<?php
require_once '../includes/config-firebase.php';
require_once '../includes/firebase-tasks.php';
requireLogin();

$userId = getCurrentUserId();

// Get completed tasks
$completedTasks = getUserTasks($userId, true);

$pageTitle = 'Completed';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title"><?php echo $pageTitle; ?></h1>
        <?php if (count($completedTasks) > 0): ?>
            <button class="btn btn-outline-danger" onclick="clearAllCompleted()">
                <i class="bi bi-trash"></i> Clear All
            </button>
        <?php endif; ?>
    </div>

    <?php if (count($completedTasks) > 0): ?>
        <ul class="task-list" id="completed-tasks">
            <?php foreach ($completedTasks as $task): ?>
                <?php
                $priorityClass = 'priority-' . ($task['priority'] ?? 'medium');
                ?>
                <li class="task-item completed <?php echo $priorityClass; ?>" data-id="<?php echo $task['id']; ?>">
                    <div class="task-header">
                        <div class="task-checkbox">
                            <input type="checkbox" class="uncomplete-task" data-task-id="<?php echo $task['id']; ?>" checked>
                        </div>
                        <div class="task-content">
                            <h5 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h5>
                            <?php if (!empty($task['description'])): ?>
                                <p class="task-description"><?php echo htmlspecialchars($task['description']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($task['completed_at'])): ?>
                                <span class="task-completed-date">
                                    Completed: <?php echo date('M j, Y', strtotime($task['completed_at'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="task-actions">
                            <button class="btn btn-sm btn-outline-danger delete-task" data-task-id="<?php echo $task['id']; ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <h3>No completed tasks</h3>
            <p>Tasks you complete will appear here.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function clearAllCompleted() {
    if (confirm('Are you sure you want to delete all completed tasks? This action cannot be undone.')) {
        // Implementation for clearing all completed tasks
        window.location.href = '../tasks/clear-completed.php';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
