// TaskController class manages the business logic for tasks
class TaskController {
    constructor(dataStore, uiUpdater) {
        this.dataStore = dataStore;
        this.uiUpdater = uiUpdater;
    }

    // Get tasks based on current view
    getTasks(view, projectId = null) {
        switch (view) {
            case 'today':
                return this.dataStore.getTasksForToday();
            case 'upcoming':
                return this.dataStore.getTasksForUpcoming();
            case 'completed':
                return this.dataStore.getCompletedTasks();
            case 'project':
                return this.dataStore.getTasksByProject(projectId);
            default:
                return this.dataStore.getTasks();
        }
    }

    // Create a new task
    createTask(name, description, projectId, startDate, deadline, priority) {
        const task = new Task(
            null, // id will be auto-generated
            name,
            description,
            projectId,
            startDate,
            deadline,
            priority,
            false // not completed by default
        );
        
        this.dataStore.addTask(task);
        this.uiUpdater.onTaskAdded(task);
        return task;
    }

    // Get a specific task by ID
    getTask(taskId) {
        return this.dataStore.getTaskById(taskId);
    }

    // Update task details
    updateTask(taskId, updates) {
        const task = this.dataStore.getTaskById(taskId);
        if (!task) return false;
        
        // Apply updates to task object
        if (updates.name !== undefined) task.setName(updates.name);
        if (updates.description !== undefined) task.setDescription(updates.description);
        if (updates.startDate !== undefined) task.setStartDate(updates.startDate);
        if (updates.deadline !== undefined) task.setDeadline(updates.deadline);
        if (updates.priority !== undefined) task.setPriority(updates.priority);
        if (updates.projectId !== undefined) task.setProjectId(updates.projectId);
        
        // Save the updated task
        const success = this.dataStore.updateTask(task);
        if (success) {
            this.uiUpdater.onTaskUpdated(task);
        }
        return success;
    }

    // Toggle task completion status
    toggleTaskCompletion(taskId) {
        const isCompleted = this.dataStore.toggleTaskComplete(taskId);
        if (isCompleted !== null) {
            const task = this.dataStore.getTaskById(taskId);
            this.uiUpdater.onTaskStatusChanged(task, isCompleted);
            return isCompleted;
        }
        return null;
    }

    // Delete a task
    deleteTask(taskId) {
        const success = this.dataStore.deleteTask(taskId);
        if (success) {
            this.uiUpdater.onTaskDeleted(taskId);
        }
        return success;
    }

    // Get quick date options for task form
    getQuickDateOptions() {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        const nextWeek = new Date(today);
        nextWeek.setDate(today.getDate() + 7);
        
        return {
            today: this.formatDate(today),
            tomorrow: this.formatDate(tomorrow),
            nextWeek: this.formatDate(nextWeek)
        };
    }

    // Format date to YYYY-MM-DD for input fields
    formatDate(date) {
        return date.toISOString().split('T')[0];
    }
}
