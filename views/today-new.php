<?php
require_once '../includes/config-firebase.php';
require_once '../includes/firebase-tasks.php';
requireLogin();

$userId = getCurrentUserId();

// Get today's tasks
$todayTasks = getTodayTasks($userId);

$pageTitle = 'Today';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title"><?php echo $pageTitle; ?></h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal">
            <i class="bi bi-plus"></i> Add Task
        </button>
    </div>

    <?php if (count($todayTasks) > 0): ?>
        <ul class="task-list" id="today-tasks">
            <?php foreach ($todayTasks as $task): ?>
                <?php
                $priorityClass = 'priority-' . ($task['priority'] ?? 'medium');
                ?>
                <li class="task-item <?php echo $priorityClass; ?>" data-id="<?php echo $task['id']; ?>">
                    <div class="task-header">
                        <div class="task-checkbox">
                            <input type="checkbox" class="complete-task" data-task-id="<?php echo $task['id']; ?>">
                        </div>
                        <div class="task-content">
                            <h5 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h5>
                            <?php if (!empty($task['description'])): ?>
                                <p class="task-description"><?php echo htmlspecialchars($task['description']); ?></p>
                            <?php endif; ?>
                            <span class="task-due-date">
                                Due: Today
                            </span>
                        </div>
                        <div class="task-actions">
                            <button class="btn btn-sm btn-outline-secondary edit-task" data-task-id="<?php echo $task['id']; ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
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
                <i class="bi bi-calendar-day"></i>
            </div>
            <h3>No tasks due today</h3>
            <p>You're all caught up for today!</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal">
                <i class="bi bi-plus"></i> Add Task for Today
            </button>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/task-modal.php'; ?>
<?php include '../includes/footer.php'; ?>
