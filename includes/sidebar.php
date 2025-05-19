<?php
// Get current user's projects
$userId = getCurrentUserId();
$projectsQuery = "SELECT * FROM projects WHERE user_id = ? ORDER BY name ASC";
$stmt = $conn->prepare($projectsQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$projectsResult = $stmt->get_result();

// Get task counts for each section
$inboxCount = 0;
$todayCount = 0;
$upcomingCount = 0;

$countQuery = "SELECT 
                COUNT(CASE WHEN project_id IS NULL THEN 1 END) as inbox_count,
                COUNT(CASE WHEN start_date <= CURDATE() AND due_date >= CURDATE() THEN 1 END) as today_count,
                COUNT(CASE WHEN start_date > CURDATE() AND start_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as upcoming_count
               FROM tasks 
               WHERE user_id = ? AND is_completed = 0";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("i", $userId);
$countStmt->execute();
$countResult = $countStmt->get_result();

if ($countRow = $countResult->fetch_assoc()) {
    $inboxCount = $countRow['inbox_count'];
    $todayCount = $countRow['today_count'];
    $upcomingCount = $countRow['upcoming_count'];
}

// Get current page for active class
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<div class="sidebar">
    <div class="sidebar-content">
        <!-- Main navigation items -->
        <ul class="sidebar-nav">
            <li class="sidebar-item <?php echo ($currentPage == 'inbox') ? 'active' : ''; ?>">
                <a href="inbox.php" class="sidebar-link">
                    <i class="fa fa-inbox"></i>
                    <span>Inbox</span>
                    <?php if ($inboxCount > 0): ?>
                        <span class="badge bg-secondary ms-auto"><?php echo $inboxCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?php echo ($currentPage == 'today') ? 'active' : ''; ?>">
                <a href="today.php" class="sidebar-link">
                    <i class="fa fa-calendar-day"></i>
                    <span>Today</span>
                    <?php if ($todayCount > 0): ?>
                        <span class="badge bg-secondary ms-auto"><?php echo $todayCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?php echo ($currentPage == 'upcoming') ? 'active' : ''; ?>">
                <a href="upcoming.php" class="sidebar-link">
                    <i class="fa fa-calendar"></i>
                    <span>Upcoming</span>
                    <?php if ($upcomingCount > 0): ?>
                        <span class="badge bg-secondary ms-auto"><?php echo $upcomingCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?php echo ($currentPage == 'completed') ? 'active' : ''; ?>">
                <a href="completed.php" class="sidebar-link">
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
            <?php while ($project = $projectsResult->fetch_assoc()): ?>
                <?php
                // Get task count for this project
                $projectTaskQuery = "SELECT COUNT(*) as task_count FROM tasks WHERE project_id = ? AND user_id = ?";
                $projectTaskStmt = $conn->prepare($projectTaskQuery);
                $projectTaskStmt->bind_param("ii", $project['id'], $userId);
                $projectTaskStmt->execute();
                $taskCountResult = $projectTaskStmt->get_result();
                $taskCountRow = $taskCountResult->fetch_assoc();
                $taskCount = $taskCountRow['task_count'];
                ?>
                <li class="sidebar-item <?php echo ($currentPage == 'project' && isset($_GET['id']) && $_GET['id'] == $project['id']) ? 'active' : ''; ?>">
                    <a href="project.php?id=<?php echo $project['id']; ?>" class="sidebar-link">
                        <i class="fa fa-project-diagram" style="color: <?php echo $project['color']; ?>"></i>
                        <span><?php echo htmlspecialchars($project['name']); ?></span>
                        <?php if ($taskCount > 0): ?>
                            <span class="badge bg-secondary ms-auto"><?php echo $taskCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endwhile; ?>
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
            <form action="add-project.php" method="post">
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