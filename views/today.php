<?php
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$currentDate = getCurrentDate();

// Get overdue tasks using Firestore
$overdueWhereConditions = [
    ['user_id', '==', $userId],
    ['due_date', '<', $currentDate],
    ['is_completed', '==', false]
];

$overdueTasks = getDocuments('tasks', $overdueWhereConditions, 'due_date', 'asc');
$overdueCount = count($overdueTasks);

// Get today's tasks using Firestore
$todayWhereConditions = [
    ['user_id', '==', $userId],
    ['start_date', '<=', $currentDate],
    ['due_date', '>=', $currentDate],
    ['is_completed', '==', false]
];

$todayTasks = getDocuments('tasks', $todayWhereConditions, 'priority', 'desc');
$todayCount = count($todayTasks);

// Process tasks with project data
foreach ($overdueTasks as &$task) {
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
    }
}

foreach ($todayTasks as &$task) {
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
    }
}

// Page title
$pageTitle = 'Today';

// Include header
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
            <?php echo $pageTitle; ?>
            <span class="ms-2 badge bg-light text-dark">
                <?php echo date('l, F j'); ?>
            </span>
        </h1>
    </div>

    <!-- Overdue Tasks Section -->
    <?php if ($overdueCount > 0): ?>
        <div class="task-section mb-5">
            <div class="section-header mb-3">
                <h2 class="section-title text-danger">
                    Overdue <span class="badge bg-danger"><?php echo $overdueCount; ?></span>
                </h2>
            </div>
            <ul class="task-list">
                <?php foreach ($overdueTasks as $task): ?>
                    <?php
                    $priorityClass = 'priority-' . $task['priority'];
                    $projectStyle = !empty($task['project_color']) ? 'style="background-color: ' . $task['project_color'] . ';"' : '';
                    ?> <li class="task-item <?php echo $priorityClass; ?> overdue" data-id="<?php echo $task['id']; ?>">
                        <div class="task-header">
                            <div class="task-checkbox">
                                <a href="../tasks/complete-task.php?id=<?php echo $task['id']; ?>" class="complete-task" data-id="<?php echo $task['id']; ?>">
                                    <i class="far fa-circle"></i>
                                </a>
                            </div>
                            <h5 class="task-name"><?php echo htmlspecialchars($task['name']); ?></h5>
                            <span class="task-date text-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo formatDate($task['due_date']); ?>
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
                                <button class="edit-task" data-id="<?php echo $task['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($task['name']); ?>"
                                    data-description="<?php echo htmlspecialchars($task['description']); ?>"
                                    data-start-date="<?php echo isset($task['start_date']) ? $task['start_date'] : ''; ?>"
                                    data-due-date="<?php echo isset($task['due_date']) ? $task['due_date'] : ''; ?>"
                                    data-priority="<?php echo $task['priority']; ?>"
                                    data-project-id="<?php echo isset($task['project_id']) ? $task['project_id'] : ''; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="../tasks/delete-task.php?id=<?php echo $task['id']; ?>" class="delete-task" onclick="return confirm('Are you sure you want to delete this task?');">
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
        </div>
    <?php endif; ?>

    <!-- Today's Tasks Section -->
    <div class="task-section">
        <div class="section-header mb-3">
            <h2 class="section-title">
                Today <span class="badge bg-primary"><?php echo $todayCount; ?></span>
            </h2>
        </div>

        <?php if ($todayCount > 0): ?>
            <ul class="task-list">
                <?php foreach ($todayTasks as $task): ?>
                    <?php
                    $priorityClass = 'priority-' . $task['priority'];
                    $projectStyle = !empty($task['project_color']) ? 'style="background-color: ' . $task['project_color'] . ';"' : '';
                    ?> <li class="task-item <?php echo $priorityClass; ?>" data-id="<?php echo $task['id']; ?>">
                        <div class="task-header">
                            <div class="task-checkbox">
                                <a href="../tasks/complete-task.php?id=<?php echo $task['id']; ?>" class="complete-task" data-id="<?php echo $task['id']; ?>">
                                    <i class="far fa-circle"></i>
                                </a>
                            </div>
                            <h5 class="task-name"><?php echo htmlspecialchars($task['name']); ?></h5>
                            <?php if (!empty($task['due_date'])): ?>
                                <span class="task-date <?php echo isToday($task['due_date']) ? 'text-success' : ''; ?>">
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
                            <?php if (!empty($task['project_name'])): ?>
                                <span class="task-project" style="background-color: <?php echo $task['project_color']; ?>20;">
                                    <i class="fa fa-project-diagram" style="color: <?php echo $task['project_color']; ?>"></i>
                                    <?php echo htmlspecialchars($task['project_name']); ?>
                                </span>
                            <?php endif; ?>

                            <div class="task-actions">
                                <button class="edit-task" data-id="<?php echo $task['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($task['name']); ?>"
                                    data-description="<?php echo htmlspecialchars($task['description']); ?>"
                                    data-start-date="<?php echo isset($task['start_date']) ? $task['start_date'] : ''; ?>"
                                    data-due-date="<?php echo isset($task['due_date']) ? $task['due_date'] : ''; ?>"
                                    data-priority="<?php echo $task['priority']; ?>"
                                    data-project-id="<?php echo isset($task['project_id']) ? $task['project_id'] : ''; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="../tasks/delete-task.php?id=<?php echo $task['id']; ?>" class="delete-task" onclick="return confirm('Are you sure you want to delete this task?');">
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
                    <i class="bi bi-calendar-check"></i>
                </div>
                <h3>No tasks for today</h3>
                <p>Enjoy your day off or add some tasks</p>
            </div>
        <?php endif; ?>
    </div>

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