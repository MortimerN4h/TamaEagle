<?php
// Get current user's projects
$userId = getCurrentUserId();
$projects = getDocuments('projects', [
    ['user_id', '==', $userId]
], 'name', 'asc');

// Get task counts for each section
$inboxCount = 0;
$todayCount = 0;
$upcomingCount = 0;

// Get all uncompleted tasks for this user
$tasks = getDocuments('tasks', [
    ['user_id', '==', $userId],
    ['is_completed', '==', false]
]);

// Count tasks by type
$today = date('Y-m-d');
$upcomingEnd = date('Y-m-d', strtotime('+7 days'));

foreach ($tasks as $task) {
    // Inbox count (tasks with no project)
    if (!isset($task['project_id']) || empty($task['project_id'])) {
        $inboxCount++;
    }

    // Today count (tasks due today or overdue)
    if (isset($task['due_date']) && $task['due_date'] <= $today) {
        $todayCount++;
    }

    // Upcoming count (tasks due in the next 7 days)
    if (isset($task['start_date']) && $task['start_date'] > $today && $task['start_date'] <= $upcomingEnd) {
        $upcomingCount++;
    }
}

// Get current page for active class
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<div class="sidebar">
    <div class="sidebar-content">
        <!-- Main navigation items -->
        <ul class="sidebar-nav">
            <li class="sidebar-item <?php echo ($currentPage == 'inbox') ? 'active' : ''; ?>">
                <a href="../views/inbox.php" class="sidebar-link">
                    <i class="fa fa-inbox"></i>
                    <span>Inbox</span>
                    <?php if ($inboxCount > 0): ?>
                        <span class="badge bg-secondary ms-auto"><?php echo $inboxCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?php echo ($currentPage == 'today') ? 'active' : ''; ?>">
                <a href="../views/today.php" class="sidebar-link">
                    <i class="fa fa-calendar-day"></i>
                    <span>Today</span>
                    <?php if ($todayCount > 0): ?>
                        <span class="badge bg-secondary ms-auto"><?php echo $todayCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?php echo ($currentPage == 'upcoming') ? 'active' : ''; ?>">
                <a href="../views/upcoming.php" class="sidebar-link">
                    <i class="fa fa-calendar"></i>
                    <span>Upcoming</span>
                    <?php if ($upcomingCount > 0): ?>
                        <span class="badge bg-secondary ms-auto"><?php echo $upcomingCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?php echo ($currentPage == 'completed') ? 'active' : ''; ?>">
                <a href="../views/completed.php" class="sidebar-link">
                    <i class="fa fa-check-circle"></i>
                    <span>Completed</span>
                </a>
            </li>
        </ul>

        <!-- Projects section -->
        <div class="sidebar-divider my-3"></div>
        <div class="sidebar-heading d-flex justify-content-between align-items-center px-3">
            <span>My Projects</span>
            <a href="#" class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#projectModal">
                <i class="fa fa-plus"></i>
            </a>
        </div>
        <ul class="sidebar-nav">
            <?php foreach ($projects as $project): ?>
                <?php
                // Get task count for this project
                $projectTasks = getDocuments('tasks', [
                    ['project_id', '==', $project['id']],
                    ['user_id', '==', $userId]
                ]);
                $taskCount = count($projectTasks);
                ?>
                <li class="sidebar-item <?php echo ($currentPage == 'project' && isset($_GET['id']) && $_GET['id'] == $project['id']) ? 'active' : ''; ?>">
                    <a href="../projects/project.php?id=<?php echo $project['id']; ?>" class="sidebar-link">
                        <i class="fa fa-project-diagram" style="color: <?php echo htmlspecialchars($project['color']); ?>"></i>
                        <span><?php echo htmlspecialchars($project['name']); ?></span>
                        <?php if ($taskCount > 0): ?>
                            <span class="badge bg-secondary ms-auto"><?php echo $taskCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Project Modal -->
<div class="modal fade" id="projectModal" tabindex="-1" aria-labelledby="projectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="projectModalLabel">Add New Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../projects/add-project.php" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="projectName" class="form-label">Project Name</label>
                        <input type="text" class="form-control" id="projectName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="projectColor" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" id="projectColor" name="color" value="#ff0000" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Project</button>
                </div>
            </form>
        </div>
    </div>
</div>