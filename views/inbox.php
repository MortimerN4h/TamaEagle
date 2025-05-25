<?php
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();

// Process task sorting
$sort = getGetData('sort', 'position');
$order = getGetData('order', 'asc');

// Valid sort options
$validSortOptions = ['position', 'name', 'due_date', 'priority'];
if (!in_array($sort, $validSortOptions)) {
    $sort = 'position';
}

// Valid order options
$validOrderOptions = ['asc', 'desc'];
if (!in_array($order, $validOrderOptions)) {
    $order = 'asc';
}

// Get inbox tasks (tasks with no project assigned)
$tasksQuery = "
    SELECT t.*, p.name as project_name, p.color as project_color 
    FROM tasks t
    LEFT JOIN projects p ON t.project_id = p.id
    WHERE t.user_id = ? AND t.project_id IS NULL AND t.is_completed = 0
    ORDER BY t.$sort $order
";

$stmt = $conn->prepare($tasksQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$tasksResult = $stmt->get_result();

// Get tasks count
$taskCount = $tasksResult->num_rows;

// Page title
$pageTitle = 'Inbox';

// Include header and sidebar
include '../includes/header.php';
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