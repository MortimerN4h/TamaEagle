<?php
// Get current user's projects
$userId = getCurrentUserId();
$projects = getDocuments('projects', [
    ['user_id', '==', $userId]
], 'name', 'asc');

// Get all uncompleted tasks for this user
$tasks = getDocuments('tasks', [
    ['user_id', '==', $userId],
    ['is_completed', '==', false]
]);

// Get current page for active class
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-content">
        <div class="d-flex justify-content-end d-md-none p-2">
            <button id="closeSidebar" class="btn btn-sm btn-link text-dark">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <!-- Main navigation items -->
        <ul class="sidebar-nav">
            <li class="sidebar-item <?php echo ($currentPage == 'today') ? 'active' : ''; ?>">
                <a href="../views/today.php" class="sidebar-link">
                    <i class="fa fa-calendar-day"></i>
                    <span>Today</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo ($currentPage == 'upcoming') ? 'active' : ''; ?>">
                <a href="../views/upcoming.php" class="sidebar-link">
                    <i class="fa fa-calendar"></i>
                    <span>Upcoming</span>
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
                    ['user_id', '==', $userId],
                    ['is_completed', '==', false]
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