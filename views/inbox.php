<?php
require_once '../includes/config-firebase.php';
require_once '../includes/firebase-tasks.php';
requireLogin();

$userId = getCurrentUserId();

// Get inbox tasks (tasks with no project assigned)
$tasks = getUserTasks($userId, false);
$inboxTasks = [];

foreach ($tasks as $task) {
    if (empty($task['project_id'])) {
        $inboxTasks[] = $task;
    }
}

$pageTitle = 'Inbox';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title"><?php echo $pageTitle; ?></h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal">
            <i class="bi bi-plus"></i> Add Task
        </button>
    </div>

    <?php if (count($inboxTasks) > 0): ?>
        <ul class="task-list" id="inbox-tasks">
            <?php foreach ($inboxTasks as $task): ?>
                <?php
                $isPastDue = !empty($task['due_date']) && strtotime($task['due_date']) < strtotime('today');
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
                            <?php if (!empty($task['due_date'])): ?>
                                <span class="task-due-date <?php echo $isPastDue ? 'text-danger' : ''; ?>">
                                    Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                                </span>
                            <?php endif; ?>
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
                <i class="bi bi-inbox"></i>
            </div>
            <h3>Your inbox is empty</h3>
            <p>Add some tasks to get started!</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal">
                <i class="bi bi-plus"></i> Add Your First Task
            </button>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/task-modal.php'; ?>
<?php include '../includes/footer.php'; ?>
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title"><?php echo $pageTitle; ?></h1>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Sort by: <?php echo ucfirst(str_replace('_', ' ', $sort)); ?> <?php echo $order === 'asc' ? '↑' : '↓'; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item <?php echo ($sort === 'position' && $order === 'asc') ? 'active' : ''; ?>" href="?sort=position&order=asc">Position (Default)</a></li>
                <li><a class="dropdown-item <?php echo ($sort === 'name' && $order === 'asc') ? 'active' : ''; ?>" href="?sort=name&order=asc">Name (A-Z)</a></li>
                <li><a class="dropdown-item <?php echo ($sort === 'name' && $order === 'desc') ? 'active' : ''; ?>" href="?sort=name&order=desc">Name (Z-A)</a></li>
                <li><a class="dropdown-item <?php echo ($sort === 'due_date' && $order === 'asc') ? 'active' : ''; ?>" href="?sort=due_date&order=asc">Due Date (Earliest first)</a></li>
                <li><a class="dropdown-item <?php echo ($sort === 'due_date' && $order === 'desc') ? 'active' : ''; ?>" href="?sort=due_date&order=desc">Due Date (Latest first)</a></li>
                <li><a class="dropdown-item <?php echo ($sort === 'priority' && $order === 'desc') ? 'active' : ''; ?>" href="?sort=priority&order=desc">Priority (Highest first)</a></li>
                <li><a class="dropdown-item <?php echo ($sort === 'priority' && $order === 'asc') ? 'active' : ''; ?>" href="?sort=priority&order=asc">Priority (Lowest first)</a></li>
            </ul>
        </div>
    </div>

    <?php if ($taskCount > 0): ?>
        <ul class="task-list sortable-tasks" data-section-id="inbox">
            <?php while ($task = $tasksResult->fetch_assoc()): ?>
                <?php
                $isPastDue = !empty($task['due_date']) && isPast($task['due_date']) && !isToday($task['due_date']);
                $priorityClass = 'priority-' . $task['priority'];
                ?>
                <li class="task-item <?php echo $priorityClass; ?>" data-id="<?php echo $task['id']; ?>">
                    <div class="task-header">
                        <div class="task-checkbox">
                            <a href="complete-task.php?id=<?php echo $task['id']; ?>" class="complete-task" data-id="<?php echo $task['id']; ?>">
                                <i class="far fa-circle"></i>
                            </a>
                        </div>
                        <h5 class="task-name"><?php echo htmlspecialchars($task['name']); ?></h5>
                        <?php if (!empty($task['due_date'])): ?>
                            <span class="task-date <?php echo $isPastDue ? 'text-danger' : ''; ?>">
                                <?php if ($isPastDue): ?>
                                    <i class="fas fa-exclamation-circle"></i>
                                <?php endif; ?>
                                <?php echo formatDate($task['due_date']); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($task['description'])): ?>
                        <div class="task-description">
                            <?php echo nl2br(htmlspecialchars($task['description'])); ?>
                        </div>
                    <?php endif; ?>

                    <div class="task-footer">
                        <div class="task-actions">
                            <button class="edit-task" data-id="<?php echo $task['id']; ?>"
                                data-name="<?php echo htmlspecialchars($task['name']); ?>"
                                data-description="<?php echo htmlspecialchars($task['description']); ?>"
                                data-start-date="<?php echo $task['start_date']; ?>"
                                data-due-date="<?php echo $task['due_date']; ?>"
                                data-priority="<?php echo $task['priority']; ?>"
                                data-project-id="<?php echo $task['project_id']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="delete-task.php?id=<?php echo $task['id']; ?>" class="delete-task" onclick="return confirm('Are you sure you want to delete this task?');">
                                <i class="fas fa-trash"></i>
                            </a>
                            <span class="drag-handle" data-bs-toggle="tooltip" title="Drag to reorder">
                                <i class="fas fa-grip-lines"></i>
                            </span>
                        </div>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-info">
            No tasks found in your inbox. Click "Add Task" to create a new task.
        </div>
    <?php endif; ?>

    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#taskModal">
        <i class="fas fa-plus"></i> Add Task
    </button>
</div>

<?php include '../includes/footer.php'; ?>