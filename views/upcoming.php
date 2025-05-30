<?php
require_once '../includes/config.php';
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

// Get all tasks within the week range using Firestore
// Note: Firestore doesn't support complex date range queries directly, so we'll get all tasks and filter
$allUserTasksConditions = [
    ['user_id', '==', $userId],
    ['is_completed', '==', false]
];

$allTasks = getDocuments('tasks', $allUserTasksConditions);

// Filter tasks that fall within the week
$weekTasks = [];
foreach ($allTasks as $task) {
    $startDate = isset($task['start_date']) ? $task['start_date'] : null;
    $dueDate = isset($task['due_date']) ? $task['due_date'] : null;

    if (
        // Task starts within the week range
        ($startDate && $startDate >= $weekStart && $startDate <= $weekEnd) ||
        // Task is due within the week range
        ($dueDate && $dueDate >= $weekStart && $dueDate <= $weekEnd) ||
        // Task spans across the week
        ($startDate && $dueDate && $startDate <= $weekStart && $dueDate >= $weekEnd)
    ) {
        // Add project data to task
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

        $weekTasks[] = $task;
    }
}

// Sort tasks by due date then priority
usort($weekTasks, function ($a, $b) {
    $aDueDate = $a['due_date'] ?? '9999-12-31';
    $bDueDate = $b['due_date'] ?? '9999-12-31';

    if ($aDueDate == $bDueDate) {
        $aPriority = $a['priority'] ?? 0;
        $bPriority = $b['priority'] ?? 0;
        return $bPriority - $aPriority; // Higher priority first
    }

    return strcmp($aDueDate, $bDueDate);
});

// Organize tasks by date
$tasksByDate = [];

// Initialize all 7 days
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("+$i days", strtotime($weekStart)));
    $tasksByDate[$date] = [
        'date' => $date,
        'day' => date('l', strtotime($date)),
        'day_short' => date('D', strtotime($date)),
        'day_number' => date('j', strtotime($date)),
        'is_today' => $date == $currentDate,
        'is_tomorrow' => $date == date('Y-m-d', strtotime('+1 day', strtotime($currentDate))),
        'tasks' => []
    ];
}

// Add tasks to their respective days - now showing on all days between start and end date
foreach ($weekTasks as $task) {
    $startDate = isset($task['start_date']) ? $task['start_date'] : null;
    $dueDate = isset($task['due_date']) ? $task['due_date'] : null;

    // If the task only has a due date, show it only on the due date
    if ($dueDate && !$startDate) {
        if (array_key_exists($dueDate, $tasksByDate)) {
            $tasksByDate[$dueDate]['tasks'][] = $task;
        }
    }
    // If the task only has a start date, show it only on the start date
    else if ($startDate && !$dueDate) {
        if (array_key_exists($startDate, $tasksByDate)) {
            $tasksByDate[$startDate]['tasks'][] = $task;
        }
    }
    // If the task has both start and end dates, show it on every day in between
    else if ($startDate && $dueDate) {
        $current = $startDate;
        while ($current <= $dueDate) {
            if (array_key_exists($current, $tasksByDate)) {
                $tasksByDate[$current]['tasks'][] = $task;
            }
            $current = date('Y-m-d', strtotime('+1 day', strtotime($current)));
        }
    }
}

// Page title
$pageTitle = 'Upcoming';
$weekDateRange = date('M j', strtotime($weekStart)) . ' - ' . date('M j', strtotime($weekEnd));

// Include header
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="page-title"><?php echo $pageTitle; ?></h1>

        <div class="btn-group">
            <a href="?week=<?php echo $prevWeekStart; ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-chevron-left"></i>
            </a>
            <span class="btn btn-sm btn-outline-secondary disabled">
                <?php echo $weekDateRange; ?>
            </span>
            <a href="?week=<?php echo $nextWeekStart; ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
    <div class="upcoming-container">
        <div class="upcoming-week d-flex flex-nowrap">
            <?php foreach ($tasksByDate as $date => $dayData): ?>
                <div class="card upcoming-day me-3 <?php echo $dayData['is_today'] ? 'today' : ''; ?>" style="min-width: 400px; inline-block;">
                    <div class="day-header card-header bg-light d-flex justify-content-between align-items-center">
                        <div class="fw-bold <?php echo $dayData['is_today'] ? 'text-primary' : ''; ?>">
                            <?php echo $dayData['day_short']; ?>
                        </div>
                        <div class="badge rounded-pill <?php echo $dayData['is_today'] ? 'bg-primary text-white' : 'bg-secondary'; ?>">
                            <?php echo $dayData['day_number']; ?>
                        </div>
                    </div>

                    <div class="card-body day-tasks overflow-y-auto">
                        <?php if (count($dayData['tasks']) > 0): ?>
                            <ul class="task-list list-group list-group-flush">
                                <?php foreach ($dayData['tasks'] as $task): ?>
                                    <?php
                                    $priorityClass = 'priority-' . $task['priority'];
                                    $projectStyle = !empty($task['project_color']) ? 'style="background-color: ' . $task['project_color'] . ';"' : '';
                                    ?>
                                    <li class="task-item <?php echo $priorityClass; ?>" data-id="<?php echo $task['id']; ?>">
                                        <div class="task-container d-flex flex-row justify-content-between">
                                            <div class="task-content">
                                                <div class="task-details">
                                                    <div class="task-title">
                                                        <?php echo htmlspecialchars($task['name']); ?>
                                                    </div>
                                                    <?php if (!empty($task['description'])): ?>
                                                        <div class="task-description">
                                                            <?php echo nl2br(htmlspecialchars($task['description'])); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="task-meta">
                                                    <?php if (!empty($task['project_name'])): ?>
                                                        <span class="task-project" style="background-color: <?php echo $task['project_color']; ?>20;">
                                                            <i class="fa fa-project-diagram" style="color: <?php echo $task['project_color']; ?>"></i>
                                                            <?php echo htmlspecialchars($task['project_name']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="task-actions d-flex flex-column align-items-center gap-1">
                                                <a href="../tasks/complete-task.php?id=<?php echo $task['id']; ?>" class="complete-task checkbox-round" style="height: 3px;">
                                                    <i class="far fa-circle"></i>
                                                </a>
                                                <button type="button" class="view-task" style="height: 15px;"
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
                                                <button class="edit-task" style="height: 20px;"
                                                    data-id="<?php echo $task['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($task['name']); ?>"
                                                    data-description="<?php echo htmlspecialchars($task['description']); ?>" data-start-date="<?php echo $task['start_date']; ?>"
                                                    data-due-date="<?php echo $task['due_date']; ?>"
                                                    data-priority="<?php echo $task['priority']; ?>"
                                                    data-project-id="<?php echo isset($task['project_id']) ? $task['project_id'] : ''; ?>">
                                                    <i class="fas fa-edit"></i> </button>
                                                <a href="../tasks/delete-task.php?id=<?php echo $task['id']; ?>" class="delete-task" style="height: 20px; align-self: center" onclick="return confirm('Are you sure you want to delete this task?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-day">
                                <small>No tasks</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- Include footer -->
<?php include '../includes/footer.php'; ?>