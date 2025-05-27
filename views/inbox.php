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
$whereConditions = [
    ['user_id', '==', $userId],
    ['project_id', '==', null],
    ['is_completed', '==', false]
];

// Get tasks from Firestore
$tasks = getDocuments('tasks', $whereConditions, $sort, $order);

// If we got empty results due to missing index, show a helpful message
if (empty($tasks)) {
    // Try a simpler query without ordering to check if it's an index issue
    $simpleConditions = [
        ['user_id', '==', $userId]
    ];
    $simpleTasks = getDocuments('tasks', $simpleConditions);

    // If simple query works but complex doesn't, it's likely an index issue
    if (!empty($simpleTasks)) {
        // Filter and sort manually as a temporary workaround
        $tasks = array_filter($simpleTasks, function ($task) {
            return (!isset($task['project_id']) || $task['project_id'] === null) &&
                (!isset($task['is_completed']) || $task['is_completed'] === false);
        });

        // Manual sorting by position
        usort($tasks, function ($a, $b) {
            $posA = isset($a['position']) ? $a['position'] : 0;
            $posB = isset($b['position']) ? $b['position'] : 0;
            return $posA <=> $posB;
        });
    }
}

// Process tasks with project data
foreach ($tasks as &$task) {
    $task['project_name'] = null;
    $task['project_color'] = null;
}

// Get tasks count
$taskCount = count($tasks);

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
            <?php foreach ($tasks as $task): ?>
                <?php
                $isPastDue = !empty($task['due_date']) && isPast($task['due_date']) && !isToday($task['due_date']);
                $priorityClass = 'priority-' . $task['priority'];
                ?>
                <li class="task-item <?php echo $priorityClass; ?>" data-id="<?php echo $task['id']; ?>">
                    <div class="task-header">
                        <div class="task-checkbox"> <a href="../tasks/complete-task.php?id=<?php echo $task['id']; ?>" class="complete-task" data-id="<?php echo $task['id']; ?>">
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
                            <button class="edit-task" data-id="<?php echo $task['id']; ?>" data-name="<?php echo htmlspecialchars($task['name']); ?>"
                                data-description="<?php echo htmlspecialchars($task['description'] ?? ''); ?>"
                                data-start-date="<?php echo $task['start_date'] ?? ''; ?>"
                                data-due-date="<?php echo $task['due_date'] ?? ''; ?>"
                                data-priority="<?php echo $task['priority'] ?? 'normal'; ?>"
                                data-project-id="<?php echo $task['project_id'] ?? ''; ?>">
                                <i class="fas fa-edit"></i>
                            </button> <a href="../tasks/delete-task.php?id=<?php echo $task['id']; ?>" class="delete-task" onclick="return confirm('Are you sure you want to delete this task?');">
                                <i class="fas fa-trash"></i>
                            </a>
                            <span class="drag-handle" data-bs-toggle="tooltip" title="Drag to reorder">
                                <i class="fas fa-grip-lines"></i>
                            </span>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-check2-all"></i>
            </div>
            <h3>No tasks yet</h3>
            <p>Add some tasks to get started</p>
        </div>
    <?php endif; ?>

    <div class="add-task-btn">
        <button type="button" class="btn btn-primary btn-circle btn-floating" data-bs-toggle="modal" data-bs-target="#taskModal">
            <i class="bi bi-plus"></i>
        </button>
    </div>
</div>

<!-- Include task modal -->
<?php include '../includes/task-modal.php'; ?>

<!-- Include footer -->
<?php include '../includes/footer.php'; ?>