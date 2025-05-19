<?php
require_once 'includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$projectId = getGetData('id');

// Verify project exists and belongs to user
$projectQuery = "SELECT * FROM projects WHERE id = ? AND user_id = ?";
$projectStmt = $conn->prepare($projectQuery);
$projectStmt->bind_param("ii", $projectId, $userId);
$projectStmt->execute();
$projectResult = $projectStmt->get_result();

if ($projectResult->num_rows === 0) {
    // Project not found or doesn't belong to user
    header("Location: index.php");
    exit;
}

$project = $projectResult->fetch_assoc();

// Get all sections for this project
$sectionsQuery = "SELECT * FROM sections WHERE project_id = ? ORDER BY position ASC";
$sectionsStmt = $conn->prepare($sectionsQuery);
$sectionsStmt->bind_param("i", $projectId);
$sectionsStmt->execute();
$sectionsResult = $sectionsStmt->get_result();

$sections = [];
while ($section = $sectionsResult->fetch_assoc()) {
    $sections[] = $section;
}

// If no sections exist, create a default section
if (count($sections) === 0) {
    $defaultSectionName = "To Do";
    $defaultSectionQuery = "INSERT INTO sections (project_id, name, position) VALUES (?, ?, 0)";
    $defaultSectionStmt = $conn->prepare($defaultSectionQuery);
    $defaultSectionStmt->bind_param("is", $projectId, $defaultSectionName);
    $defaultSectionStmt->execute();
    
    $defaultSectionId = $defaultSectionStmt->insert_id;
    $sections[] = [
        'id' => $defaultSectionId,
        'project_id' => $projectId,
        'name' => $defaultSectionName,
        'position' => 0
    ];
}

// Get all tasks for this project
$tasksQuery = "
    SELECT * FROM tasks 
    WHERE project_id = ? AND user_id = ? AND is_completed = 0
    ORDER BY position ASC
";
$tasksStmt = $conn->prepare($tasksQuery);
$tasksStmt->bind_param("ii", $projectId, $userId);
$tasksStmt->execute();
$tasksResult = $tasksStmt->get_result();

// Group tasks by section
$tasksBySection = [];
foreach ($sections as $section) {
    $tasksBySection[$section['id']] = [];
}

// Add tasks to their sections
while ($task = $tasksResult->fetch_assoc()) {
    $sectionId = $task['section_id'] ?: $sections[0]['id']; // Default to first section if no section
    if (isset($tasksBySection[$sectionId])) {
        $tasksBySection[$sectionId][] = $task;
    } else {
        // If section doesn't exist (shouldn't happen), put task in first section
        $tasksBySection[$sections[0]['id']][] = $task;
    }
}

// Get task counts
$taskCountQuery = "
    SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed_tasks
    FROM tasks 
    WHERE project_id = ? AND user_id = ?
";
$taskCountStmt = $conn->prepare($taskCountQuery);
$taskCountStmt->bind_param("ii", $projectId, $userId);
$taskCountStmt->execute();
$taskCountResult = $taskCountStmt->get_result();
$taskCount = $taskCountResult->fetch_assoc();

$totalTasks = $taskCount['total_tasks'];
$completedTasks = $taskCount['completed_tasks'];
$completionPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

// Page title
$pageTitle = htmlspecialchars($project['name']);

// Include header
include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
            <span style="color: <?php echo $project['color']; ?>">
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
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal">
                <i class="fas fa-plus"></i> Add Task
            </button>
            <button class="btn btn-outline-secondary add-section" data-project-id="<?php echo $projectId; ?>">
                <i class="fas fa-plus"></i> Add Section
            </button>
        </div>
    </div>

    <div class="project-board">
        <div class="sortable-sections">
            <?php foreach ($sections as $section): ?>
                <div class="project-column" data-section-id="<?php echo $section['id']; ?>">
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
                                </a></li>
                                <?php if (count($sections) > 1): ?>
                                    <li><a class="dropdown-item delete-section" href="delete-section.php?id=<?php echo $section['id']; ?>" onclick="return confirm('Are you sure you want to delete this section? All tasks will be moved to the first section.');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <ul class="task-list sortable-tasks column-task-list" data-section-id="<?php echo $section['id']; ?>">
                        <?php if (isset($tasksBySection[$section['id']])): ?>
                            <?php foreach ($tasksBySection[$section['id']] as $task): ?>
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
                                        <div class="task-actions">
                                            <button class="edit-task" data-id="<?php echo $task['id']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($task['name']); ?>" 
                                                    data-description="<?php echo htmlspecialchars($task['description']); ?>"
                                                    data-start-date="<?php echo $task['start_date']; ?>"
                                                    data-due-date="<?php echo $task['due_date']; ?>"
                                                    data-priority="<?php echo $task['priority']; ?>"
                                                    data-project-id="<?php echo $task['project_id']; ?>"
                                                    data-section-id="<?php echo $task['section_id']; ?>">
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
                        <?php endif; ?>
                    </ul>

                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-secondary add-task-to-section" data-section-id="<?php echo $section['id']; ?>">
                            <i class="fas fa-plus"></i> Add task
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
                window.location.href = `rename-section.php?id=${sectionId}&name=${encodeURIComponent(newName)}`;
            }
        });
    });
    
    // Add task to specific section
    const addTaskButtons = document.querySelectorAll('.add-task-to-section');
    addTaskButtons.forEach(button => {
        button.addEventListener('click', function() {
            const sectionId = this.dataset.sectionId;
            document.getElementById('sectionId').value = sectionId;
            document.getElementById('project').value = <?php echo $projectId; ?>;
            
            const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
            taskModal.show();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
