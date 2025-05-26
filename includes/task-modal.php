<?php
// Get user's projects for dropdown
$userId = getCurrentUserId();
$projects = [];

if ($GLOBALS['useFirebase']) {
    // Get projects from Firestore
    $whereConditions = [
        ['user_id', '==', $userId]
    ];
    $projects = getDocuments('projects', $whereConditions, 'name', 'asc');
} else {
    // Get projects from MySQL
    global $conn;
    $projectQuery = "SELECT * FROM projects WHERE user_id = ? ORDER BY name ASC";
    $projectStmt = $conn->prepare($projectQuery);
    $projectStmt->bind_param("i", $userId);
    $projectStmt->execute();
    $projectResult = $projectStmt->get_result();
    
    while ($project = $projectResult->fetch_assoc()) {
        $projects[] = $project;
    }
}

// Get current date for default values
$currentDate = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$nextSaturday = date('Y-m-d', strtotime('next Saturday'));
$nextSunday = date('Y-m-d', strtotime('next Sunday'));
?>

<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalLabel">Add New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="taskForm" action="../tasks/add-task.php" method="post">
                <div class="modal-body">
                    <input type="hidden" id="taskId" name="task_id">
                    <input type="hidden" id="sectionId" name="section_id">

                    <div class="mb-3">
                        <label for="taskName" class="form-label">Task Name</label>
                        <input type="text" class="form-control" id="taskName" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="taskDescription" name="description" rows="3"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" value="<?php echo $currentDate; ?>">
                        </div>
                        <div class="col">
                            <label for="dueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="dueDate" name="due_date" value="<?php echo $currentDate; ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="btn-group date-shortcut-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary date-shortcut" data-start="<?php echo $currentDate; ?>" data-due="<?php echo $currentDate; ?>">Today</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary date-shortcut" data-start="<?php echo $currentDate; ?>" data-due="<?php echo $tomorrow; ?>">Tomorrow</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary date-shortcut" data-start="<?php echo $currentDate; ?>" data-due="<?php echo ($nextSaturday > $nextSunday) ? $nextSunday : $nextSaturday; ?>">This Weekend</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="0">Low</option>
                            <option value="1">Medium</option>
                            <option value="2">High</option>
                            <option value="3">Urgent</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="project" class="form-label">Project</label>
                        <select class="form-select" id="project" name="project_id">
                            <option value="">No Project (Inbox)</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Date validation
        document.getElementById('dueDate').addEventListener('change', function() {
            const startDate = document.getElementById('startDate').value;
            if (this.value < startDate) {
                alert('Due date cannot be before start date');
                this.value = startDate;
            }
        });

        document.getElementById('startDate').addEventListener('change', function() {
            const today = new Date().toISOString().split('T')[0];
            if (this.value < today) {
                alert('Start date cannot be in the past');
                this.value = today;
            }

            const dueDate = document.getElementById('dueDate').value;
            if (dueDate < this.value) {
                document.getElementById('dueDate').value = this.value;
            }
        });

        // Date shortcuts
        const dateShortcuts = document.querySelectorAll('.date-shortcut');
        dateShortcuts.forEach(btn => {
            btn.addEventListener('click', function() {
                const startDate = this.getAttribute('data-start');
                const dueDate = this.getAttribute('data-due');

                document.getElementById('startDate').value = startDate;
                document.getElementById('dueDate').value = dueDate;
            });
        });
    });
</script>