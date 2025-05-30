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

// Get overdue tasks and sort by priority first, then by due date
$overdueTasks = getDocuments('tasks', $overdueWhereConditions);

// Sort by priority (high to low) and then by due date (ascending)
usort($overdueTasks, function ($a, $b) {
    // First compare by priority (higher priority comes first)
    $priorityA = isset($a['priority']) ? $a['priority'] : 0;
    $priorityB = isset($b['priority']) ? $b['priority'] : 0;

    if ($priorityA != $priorityB) {
        return $priorityB - $priorityA; // Descending priority
    }

    // If priorities are the same, sort by due date (ascending)
    $dateA = isset($a['due_date']) ? $a['due_date'] : '';
    $dateB = isset($b['due_date']) ? $b['due_date'] : '';

    return strcmp($dateA, $dateB);
});

$overdueCount = count($overdueTasks);

// Get today's tasks using Firestore
// Due to Firestore limitations on complex queries without indexes, we'll do part of the filtering in PHP

// Get all uncompleted tasks for the user
$allUserTasksConditions = [
    ['user_id', '==', $userId],
    ['is_completed', '==', false]
];

$allUserTasks = getDocuments('tasks', $allUserTasksConditions, 'priority', 'desc');

// Filter today's tasks in PHP
$todayTasks = [];
foreach ($allUserTasks as $task) {
    if (isset($task['due_date']) && $task['due_date'] < $currentDate) {
        continue;
    }

    $start = $task['start_date'] ?? null;
    $due = $task['due_date'] ?? null;

    if (
        ($start && $due && $start <= $currentDate && $due >= $currentDate) ||
        ($start && $start == $currentDate) ||
        ($due && $due == $currentDate)
    ) {
        $todayTasks[] = $task;
    }
}

// Sort today's tasks by priority (high to low)
usort($todayTasks, function ($a, $b) {
    $priorityA = isset($a['priority']) ? $a['priority'] : 0;
    $priorityB = isset($b['priority']) ? $b['priority'] : 0;

    return $priorityB - $priorityA; // Descending priority order (high to low)
});
$todayCount_main = count($todayTasks);
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
unset($task);

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
unset($task);

// Page title
$pageTitle = 'Today';
// Include header
include '../includes/header.php';
?>

<div class="container-fluid py-1">
    <div class="d-flex justify-content-between align-items-center mb-0">
        <h1 class="page-title mb-0">
            <?php echo $pageTitle; ?>
            <span class="ms-2 badge text-dark">
                <?php echo date('l, F j'); ?>
            </span>
        </h1>
    </div>
    <div class="pt-3 d-flex flex-row justify-content-between mb-3 gap-3">

        <!-- Overdue Tasks Section -->
        <?php if ($overdueCount > 0): ?>
        <div class="w-100">
            <div class="task-section mb-5 w-100 bg-light p-3 rounded shadow-sm me-3">
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
                                    <button class="view-task"
                                        data-id="<?php echo $task['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($task['name']); ?>"
                                        data-description="<?php echo htmlspecialchars($task['description']); ?>"
                                        data-start-date="<?php echo isset($task['start_date']) ? $task['start_date'] : ''; ?>"
                                        data-due-date="<?php echo isset($task['due_date']) ? $task['due_date'] : ''; ?>"
                                        data-priority="<?php echo $task['priority']; ?>"
                                        data-project-id="<?php echo isset($task['project_id']) ? $task['project_id'] : ''; ?>"
                                        data-project-name="<?php echo isset($task['project_name']) ? htmlspecialchars($task['project_name']) : ''; ?>"
                                        data-project-color="<?php echo isset($task['project_color']) ? $task['project_color'] : ''; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="edit-task"
                                        data-id="<?php echo $task['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($task['name']); ?>"
                                        data-description="<?php echo htmlspecialchars($task['description']); ?>"
                                        data-start-date="<?php echo isset($task['start_date']) ? $task['start_date'] : ''; ?>"
                                        data-due-date="<?php echo isset($task['due_date']) ? $task['due_date'] : ''; ?>"
                                        data-priority="<?php echo $task['priority']; ?>"
                                        data-project-id="<?php echo isset($task['project_id']) ? $task['project_id'] : ''; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <div class="delete-task-container d-flex align-items-center">
                                        <a href="../tasks/delete-task.php?id=<?php echo $task['id']; ?>" class="delete-task" onclick="return confirm('Are you sure you want to delete this task?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Today's Tasks Section -->
        <div class="w-100">
        <div class="task-section w-100 bg-light p-3 rounded shadow-sm">
            <div class="section-header mb-3">
                <h2 class="section-title">
                    Today <span class="badge bg-primary"><?php echo $todayCount_main; ?></span>
                </h2>
            </div>
            <?php if ($todayCount_main > 0): ?>
                <ul class="task-list">
                    <?php foreach ($todayTasks as $task): ?>
                        <?php
                        $priorityClass = 'priority-' . $task['priority'];
                        $projectStyle = !empty($task['project_color']) ? 'style="background-color: ' . $task['project_color'] . ';"' : '';
                        ?>
                        <li class="task-item <?php echo $priorityClass; ?>" data-id="<?php echo $task['id']; ?>">
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
                                    <button class="view-task"
                                        data-id="<?php echo $task['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($task['name']); ?>"
                                        data-description="<?php echo htmlspecialchars($task['description']); ?>"
                                        data-start-date="<?php echo isset($task['start_date']) ? $task['start_date'] : ''; ?>"
                                        data-due-date="<?php echo isset($task['due_date']) ? $task['due_date'] : ''; ?>"
                                        data-priority="<?php echo $task['priority']; ?>"
                                        data-project-id="<?php echo isset($task['project_id']) ? $task['project_id'] : ''; ?>"
                                        data-project-name="<?php echo isset($task['project_name']) ? htmlspecialchars($task['project_name']) : ''; ?>"
                                        data-project-color="<?php echo isset($task['project_color']) ? $task['project_color'] : ''; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="edit-task"
                                        data-id="<?php echo $task['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($task['name']); ?>"
                                        data-description="<?php echo htmlspecialchars($task['description']); ?>"
                                        data-start-date="<?php echo isset($task['start_date']) ? $task['start_date'] : ''; ?>"
                                        data-due-date="<?php echo isset($task['due_date']) ? $task['due_date'] : ''; ?>"
                                        data-priority="<?php echo $task['priority']; ?>"
                                        data-project-id="<?php echo isset($task['project_id']) ? $task['project_id'] : ''; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <div class="delete-task-container d-flex align-items-center">
                                        <a href="../tasks/delete-task.php?id=<?php echo $task['id']; ?>" class="delete-task" onclick="return confirm('Are you sure you want to delete this task?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No tasks for today</h3>
                    <p>Enjoy your day off or add some tasks</p>
                </div> <?php endif; ?>
        </div>
        </div>
    </div>
</div>

<!-- Include footer -->
<?php include '../includes/footer.php'; ?>