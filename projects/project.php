<?php
require_once '../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$projectId = getGetData('id');

$project = null;
$sections = [];
$tasksBySection = [];
$totalTasks = 0;
$completedTasks = 0;
$completionPercentage = 0;

// Verify project exists and belongs to user
$project = getDocument('projects', $projectId);

if (!$project || $project['user_id'] !== $userId) {
    // Project not found or doesn't belong to user
    header("Location: ../index.php");
    exit;
}

// Get all sections for this project
$whereConditions = [
    ['project_id', '==', $projectId]
];
$sections = getDocuments('sections', $whereConditions, 'position', 'asc');

// If no sections exist, create a default section
if (count($sections) === 0) {
    $defaultSectionData = [
        'project_id' => $projectId,
        'name' => 'To Do',
        'position' => 0
    ];

    $defaultSectionId = addDocument('sections', $defaultSectionData);
    $sections[] = [
        'id' => $defaultSectionId,
        'project_id' => $projectId,
        'name' => 'To Do',
        'position' => 0
    ];
}

// Initialize tasksBySection with empty arrays for each section
foreach ($sections as $section) {
    $tasksBySection[$section['id']] = [];
}

// Get all tasks for this project
$taskWhereConditions = [
    ['project_id', '==', $projectId],
    ['user_id', '==', $userId],
    ['is_completed', '==', false]
];
$tasks = getDocuments('tasks', $taskWhereConditions, 'position', 'asc');

// Add tasks to their sections
foreach ($tasks as $task) {
    $sectionId = (isset($task['section_id']) && !empty($task['section_id'])) ? $task['section_id'] : $sections[0]['id']; // Default to first section if no section
    if (isset($tasksBySection[$sectionId])) {
        $tasksBySection[$sectionId][] = $task;
    } else {
        // If section doesn't exist (shouldn't happen), put task in first section
        $tasksBySection[$sections[0]['id']][] = $task;
    }
}

// Get task counts
$allTasksWhereConditions = [
    ['project_id', '==', $projectId],
    ['user_id', '==', $userId]
];
$allTasks = getDocuments('tasks', $allTasksWhereConditions);

$totalTasks = count($allTasks);
$completedTasks = 0;

foreach ($allTasks as $task) {
    if (isset($task['is_completed']) && $task['is_completed'] === true) {
        $completedTasks++;
    }
}

$completionPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

// Page title
$pageTitle = htmlspecialchars($project['name']);
error_log("Project color: " . $project['color']);
// Include header
$temp = $project;
include '../includes/header.php';
?>
<?php $project = $temp; ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title mb-0">
            <span style="color: <?php echo $project['color']; ?>">
                <?php error_log("Project color: " . $project['color']); ?>
                <i class="fa fa-project-diagram"></i>
                <?php echo $pageTitle; ?>
            </span>
            <?php if ($totalTasks > 0): ?>
                <span class="ms-2 fs-6 text-muted">
                    <?php echo $completionPercentage; ?>% complete
                    (<?php echo $completedTasks; ?>/<?php echo $totalTasks; ?>)
                </span>
            <?php endif; ?>
        </h1>
        <div class="gap-2 p-1">
            <button class="btn btn-outline-warning me-2" data-bs-toggle="modal" data-bs-target="#editProjectModal" title="Edit Project">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-outline-danger me-2" onclick="confirmDeleteProject('<?php echo $projectId; ?>', '<?php echo addslashes($project['name']); ?>')" title="Delete Project">
                <i class="fas fa-trash"></i> Delete
            </button> <button class="btn btn-primary" id="addMainTask" data-project-id="<?php echo $projectId; ?>">
                <i class="fas fa-plus"></i> Add Task
            </button> <button class="btn btn-outline-secondary add-section" data-project-id="<?php echo $projectId; ?>">
                <i class="fas fa-plus"></i> Add Section
            </button>
        </div>

        <!-- Backup script for Add Section functionality -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const addSectionBtn = document.querySelector('.add-section');
                if (addSectionBtn) {
                    console.log('Add Section button found - attaching inline handler');
                    addSectionBtn.addEventListener('click', function(e) {
                        console.log('Add Section clicked (inline handler)');
                        const projectId = this.getAttribute('data-project-id');
                        console.log('Project ID:', projectId);

                        if (sectionName && projectId) {
                            const redirectUrl = '../sections/add-section.php?project_id=' + projectId +
                                '&name=' + encodeURIComponent(sectionName);
                            console.log('Redirecting to:', redirectUrl);
                            window.location.href = redirectUrl;
                        }
                    });
                } else {
                    console.error('Add Section button not found in the document');
                }
            });
        </script>
    </div>

    <div class="project-board p-2 px-0">
        <div class="sortable-sections d-flex flex-row gap-3">
            <?php foreach ($sections as $section): ?>
                <div class="project-column bg-white p-3" data-section-id="<?php echo $section['id']; ?>">
                    <div class="project-column-header">
                        <h5 class="project-column-title">
                            <span class="section-drag-handle">
                                <i class="fas fa-grip-lines me-2"></i>
                            </span>
                            <?php echo htmlspecialchars($section['name']); ?>
                        </h5>
                        <div class="dropdown">
                            <button class="btn btn-sm" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item edit-section" href="#" data-id="<?php echo $section['id']; ?>" data-name="<?php echo htmlspecialchars($section['name']); ?>">
                                        <i class="fas fa-edit"></i> Rename
                                    </a></li> <?php if (count($sections) > 1): ?>
                                    <li><a class="dropdown-item delete-section" href="../sections/delete-section.php?id=<?php echo $section['id']; ?>" onclick="return confirm('Are you sure you want to delete this section? All tasks will be moved to the first section.');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="task-container-scrollable">
                        <ul class="task-list sortable-tasks column-task-list" data-section-id="<?php echo $section['id']; ?>">
                            <?php if (isset($tasksBySection[$section['id']])): ?>
                                <?php foreach ($tasksBySection[$section['id']] as $task): ?>
                                    <?php $priorityClass = 'priority-' . $task['priority']; ?> <li class="task-item <?php echo $priorityClass; ?>" data-id="<?php echo $task['id']; ?>">
                                        <div class="task-header">
                                            <div class="task-checkbox">
                                                <a href="../tasks/complete-task.php?id=<?php echo $task['id']; ?>" class="complete-task" data-id="<?php echo $task['id']; ?>">
                                                    <i class="far fa-circle"></i>
                                                </a>
                                            </div>
                                            <h5 class="task-name"><?php echo htmlspecialchars($task['name']); ?></h5>
                                            <?php if (!empty($task['due_date'])): ?>
                                                <span class="task-date <?php echo isPast($task['due_date']) && !isToday($task['due_date']) ? 'text-danger' : ''; ?>">
                                                    <?php if (isPast($task['due_date']) && !isToday($task['due_date'])): ?>
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
                                            <div class="task-actions display-flex align-items-center">
                                                <span class="drag-handle p-0" data-bs-toggle="tooltip" title="Drag">
                                                    <i class="fas fa-grip-lines"></i>
                                                </span>
                                                <button type="button" class="view-task "
                                                    data-id="<?php echo $task['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($task['name']); ?>"
                                                    data-description="<?php echo htmlspecialchars($task['description']); ?>"
                                                    data-start-date="<?php echo isset($task['start_date']) ? $task['start_date'] : ''; ?>"
                                                    data-due-date="<?php echo isset($task['due_date']) ? $task['due_date'] : ''; ?>" data-priority="<?php echo $task['priority']; ?>"
                                                    data-project-id="<?php echo $task['project_id']; ?>"
                                                    data-project-name="<?php echo htmlspecialchars($project['name']); ?>"
                                                    data-project-color="<?php echo $project['color']; ?>"
                                                    data-section-id="<?php echo isset($task['section_id']) ? $task['section_id'] : ''; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="edit-task"
                                                    data-id="<?php echo $task['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($task['name']); ?>"
                                                    data-description="<?php echo htmlspecialchars($task['description']); ?>" data-start-date="<?php echo isset($task['start_date']) ? $task['start_date'] : ''; ?>"
                                                    data-due-date="<?php echo isset($task['due_date']) ? $task['due_date'] : ''; ?>"
                                                    data-priority="<?php echo $task['priority']; ?>"
                                                    data-project-id="<?php echo $task['project_id']; ?>"
                                                    data-section-id="<?php echo isset($task['section_id']) ? $task['section_id'] : ''; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="../tasks/delete-task.php?id=<?php echo $task['id']; ?>" class="delete-task" onclick="return confirm('Are you sure you want to delete this task?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-secondary add-task-to-section" data-section-id="<?php echo $section['id']; ?>">
                            <i class="fas fa-plus "></i> Add task
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit section name
        const editSectionLinks = document.querySelectorAll('.edit-section');
        editSectionLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const sectionId = this.dataset.id;
                const currentName = this.dataset.name;

                const newName = prompt('Enter new section name:', currentName);
                if (newName && newName.trim() !== '' && newName !== currentName) {
                    window.location.href = `../sections/rename-section.php?id=${sectionId}&name=${encodeURIComponent(newName)}`;
                }
            });
        }); // Add task to specific section
        const addTaskButtons = document.querySelectorAll('.add-task-to-section');
        addTaskButtons.forEach(button => {
            button.addEventListener('click', function() {
                const sectionId = this.dataset.sectionId;
                document.getElementById('sectionId').value = sectionId;
                // Project ID is automatically set via the hidden field in the modified task-modal.php

                // Reset form to ensure we're adding a new task
                document.getElementById('taskForm').reset();
                document.getElementById('taskForm').action = '../tasks/add-task.php';
                document.getElementById('taskModalLabel').textContent = 'Add New Task';

                // Set today's date as default for date fields
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('startDate').value = today;
                document.getElementById('dueDate').value = today;

                const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
                taskModal.show();
            });
        }); // Complete task handlers
        const completeTaskLinks = document.querySelectorAll('.complete-task');
        completeTaskLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const taskId = this.dataset.id;
                if (confirm('Mark this task as complete?')) {
                    window.location.href = `../tasks/complete-task.php?id=${taskId}`;
                }
            });
        }); // Main Add Task button handler
        document.getElementById('addMainTask').addEventListener('click', function() {
            // Reset form
            document.getElementById('taskForm').reset();
            document.getElementById('taskForm').action = '../tasks/add-task.php';
            document.getElementById('taskModalLabel').textContent = 'Add New Task';

            // Project ID is automatically set via the hidden field in the modified task-modal.php

            // Use default section if available
            const defaultSection = document.querySelector('.project-column');
            if (defaultSection) {
                const sectionId = defaultSection.dataset.sectionId;
                document.getElementById('sectionId').value = sectionId;
            } else {
                document.getElementById('sectionId').value = '';
            }

            // Set today's date as default
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('startDate').value = today;
            document.getElementById('dueDate').value = today;

            // Show modal
            const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
            taskModal.show();
        });
    });
</script>

<!-- Include task modal with project context -->
<?php
// Always get the current project ID from the URL parameter to ensure consistency
$currentProjectId = getGetData('id');
// Get project details from Firestore to ensure fresh data
$currentProject = getDocument('projects', $currentProjectId);
$currentProjectName = $currentProject ? $currentProject['name'] : 'Unknown Project';
include '../includes/task-modal.php';
?>

<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProjectModalLabel">Edit Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="edit-project.php" method="post">
                <input type="hidden" name="project_id" value="<?php echo $projectId; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editProjectName" class="form-label">Project Name</label>
                        <input type="text" class="form-control" id="editProjectName" name="name" value="<?php echo htmlspecialchars($project['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="editProjectColor" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" id="editProjectColor" name="color" value="<?php echo $project['color']; ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDeleteProject(projectId, projectName) {
        if (confirm('Are you sure you want to delete the project "' + projectName + '"? This will permanently delete all tasks and sections in this project. This action cannot be undone.')) {
            window.location.href = 'delete-project.php?id=' + projectId;
        }
    }
</script>

<!-- Include project-specific sortable CSS and script -->
<link rel="stylesheet" href="../assets/css/project-sortable.css">
<script src="../assets/js/project-sortable.js"></script>
<script src="../assets/js/project-task-handler.js"></script>

<?php include '../includes/footer.php'; ?>