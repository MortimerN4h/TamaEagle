<?php
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$currentDate = getCurrentDate();

// Get overdue tasks
$overdueQuery = "
    SELECT t.*, p.name as project_name, p.color as project_color 
    FROM tasks t
    LEFT JOIN projects p ON t.project_id = p.id
    WHERE t.user_id = ? 
        AND t.due_date < ?
        AND t.is_completed = 0
    ORDER BY t.due_date ASC
";

$overdueStmt = $conn->prepare($overdueQuery);
$overdueStmt->bind_param("is", $userId, $currentDate);
$overdueStmt->execute();
$overdueResult = $overdueStmt->get_result();
$overdueCount = $overdueResult->num_rows;

// Get today's tasks
$todayQuery = "
    SELECT t.*, p.name as project_name, p.color as project_color 
    FROM tasks t
    LEFT JOIN projects p ON t.project_id = p.id
    WHERE t.user_id = ? 
        AND (t.start_date <= ? AND t.due_date >= ?)
        AND t.is_completed = 0
    ORDER BY t.priority DESC, t.due_date ASC
";

$todayStmt = $conn->prepare($todayQuery);
$todayStmt->bind_param("iss", $userId, $currentDate, $currentDate);
$todayStmt->execute();
$todayResult = $todayStmt->get_result();
$todayCount = $todayResult->num_rows;

// Page title
$pageTitle = 'Today';

// Include header
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
            <?php echo $pageTitle; ?>
            <span class="text-muted fs-6 ms-2"><?php echo date('F j, Y'); ?></span>
        </h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal">
            <i class="fas fa-plus"></i> Add Task
        </button>
    </div>

    <?php if ($overdueCount > 0): ?>
        <div class="section-container mb-4">
            <div class="section-header">
                <h4 class="section-title text-danger">
                    <i class="fas fa-exclamation-circle"></i> Overdue
                    <span class="badge bg-danger ms-2"><?php echo $overdueCount; ?></span>
                </h4>
            </div>
            <ul class="task-list sortable-tasks" data-section-id="overdue">
                <?php while ($task = $overdueResult->fetch_assoc()): ?>
                    <?php $priorityClass = 'priority-' . $task['priority']; ?>
                    <li class="task-item <?php echo $priorityClass; ?>" data-id="<?php echo $task['id']; ?>">
                        <div class="task-header">
                            <div class="task-checkbox">
                                <a href="complete-task.php?id=<?php echo $task['id']; ?>" class="complete-task" data-id="<?php echo $task['id']; ?>">
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
        </div>
    <?php endif; ?>

    <div class="section-container">
        <div class="section-header">
            <h4 class="section-title">
                <i class="fas fa-calendar-day"></i> Today
                <?php if ($todayCount > 0): ?>
                    <span class="badge bg-primary ms-2"><?php echo $todayCount; ?></span>
                <?php endif; ?>
            </h4>
        </div>

        <?php if ($todayCount > 0): ?>
            <ul class="task-list sortable-tasks" data-section-id="today">
                <?php while ($task = $todayResult->fetch_assoc()): ?>
                    <?php $priorityClass = 'priority-' . $task['priority']; ?>
                    <li class="task-item <?php echo $priorityClass; ?>" data-id="<?php echo $task['id']; ?>">
                        <div class="task-header">
                            <div class="task-checkbox">
                                <a href="complete-task.php?id=<?php echo $task['id']; ?>" class="complete-task" data-id="<?php echo $task['id']; ?>">
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
                <p class="mb-0">No tasks scheduled for today. Click "Add Task" to create a new task.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>