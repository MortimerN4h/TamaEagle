<?php
require_once '../includes/config-firebase.php';
requireLogin();

$userId = getCurrentUserId();
$currentDate = getCurrentDate();

// Determine the current week's start date
$weekStart = getGetData('week', $currentDate);

// Calculate the week's end date (7 days from start)
$weekEnd = date('Y-m-d', strtotime('+6 days', strtotime($weekStart)));

// Previous and next week dates for navigation
$prevWeekStart = date('Y-m-d', strtotime('-7 days', strtotime($weekStart)));
$nextWeekStart = date('Y-m-d', strtotime('+7 days', strtotime($weekStart)));

// Only allow navigating to future weeks, not past weeks
if ($prevWeekStart < $currentDate) {
    $prevWeekStart = $currentDate;
}

// Get all tasks within the week range
$tasksQuery = "
    SELECT t.*, p.name as project_name, p.color as project_color 
    FROM tasks t
    LEFT JOIN projects p ON t.project_id = p.id
    WHERE t.user_id = ? 
        AND (
            (t.start_date BETWEEN ? AND ?) OR
            (t.due_date BETWEEN ? AND ?) OR
            (t.start_date <= ? AND t.due_date >= ?)
        )
        AND t.is_completed = 0
    ORDER BY t.due_date ASC, t.priority DESC
";

$stmt = $conn->prepare($tasksQuery);
$stmt->bind_param("issssss", $userId, $weekStart, $weekEnd, $weekStart, $weekEnd, $weekStart, $weekEnd);
$stmt->execute();
$tasksResult = $stmt->get_result();

// Organize tasks by date
$tasksByDate = [];

// Initialize all 7 days
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("+$i days", strtotime($weekStart)));
    $tasksByDate[$date] = [
        'date' => $date,
        'day' => date('l', strtotime($date)),
        'formatted' => date('M j', strtotime($date)),
        'tasks' => []
    ];
}

// Group tasks by due date
while ($task = $tasksResult->fetch_assoc()) {
    $taskDate = $task['due_date'] ?: $task['start_date'];

    // Only add tasks to dates within our range
    if (isset($tasksByDate[$taskDate])) {
        $tasksByDate[$taskDate]['tasks'][] = $task;
    }
}

// Page title
$pageTitle = 'Upcoming';

// Include header
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="date-navigation mb-4">
        <h1 class="date-nav-title"><?php echo $pageTitle; ?></h1>
        <div class="date-nav-actions">
            <?php if ($weekStart != $currentDate): ?>
                <a href="upcoming.php" class="btn btn-sm btn-outline-secondary date-nav-today">
                    Today
                </a>
            <?php endif; ?>
            <?php if ($prevWeekStart != $weekStart): ?>
                <a href="upcoming.php?week=<?php echo $prevWeekStart; ?>" class="btn btn-sm btn-outline-secondary date-nav-prev" data-current="<?php echo $prevWeekStart; ?>">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            <a href="upcoming.php?week=<?php echo $nextWeekStart; ?>" class="btn btn-sm btn-outline-secondary date-nav-next" data-next="<?php echo $nextWeekStart; ?>">
                Next <i class="fas fa-chevron-right"></i>
            </a>
            <button class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#taskModal">
                <i class="fas fa-plus"></i> Add Task
            </button>
        </div>
    </div>

    <?php foreach ($tasksByDate as $date => $dayData): ?>
        <div class="section-container mb-4">
            <div class="section-header">
                <h4 class="section-title">
                    <?php if (isToday($date)): ?>
                        <span class="text-success"><?php echo $dayData['day']; ?> (Today)</span>
                    <?php elseif (isTomorrow($date)): ?>
                        <span><?php echo $dayData['day']; ?> (Tomorrow)</span>
                    <?php else: ?>
                        <span><?php echo $dayData['day']; ?></span>
                    <?php endif; ?>
                    <span class="ms-2 text-muted"><?php echo $dayData['formatted']; ?></span>
                </h4>
            </div>

            <?php if (count($dayData['tasks']) > 0): ?>
                <ul class="task-list sortable-tasks" data-section-id="<?php echo $date; ?>">
                    <?php foreach ($dayData['tasks'] as $task): ?>
                        <?php $priorityClass = 'priority-' . $task['priority']; ?>
                        <li class="task-item <?php echo $priorityClass; ?>" data-id="<?php echo $task['id']; ?>">
                            <div class="task-header">
                                <div class="task-checkbox">
                                    <a href="complete-task.php?id=<?php echo $task['id']; ?>" class="complete-task" data-id="<?php echo $task['id']; ?>">
                                        <i class="far fa-circle"></i>
                                    </a>
                                </div>
                                <h5 class="task-name"><?php echo htmlspecialchars($task['name']); ?></h5>
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
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="alert alert-light">
                    <p class="mb-0">No tasks scheduled for this day.</p>
                </div>
            <?php endif; ?>

            <div class="mt-2">
                <button class="btn btn-sm btn-outline-secondary" data-date="<?php echo $date; ?>" onclick="addTaskForDay('<?php echo $date; ?>')">
                    <i class="fas fa-plus"></i> Add task
                </button>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<script>
    function addTaskForDay(date) {
        // Set the date in the task modal and open it
        document.getElementById('startDate').value = date;
        document.getElementById('dueDate').value = date;

        // Open the modal
        const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
        taskModal.show();
    }
</script>

<?php include '../includes/footer.php'; ?>