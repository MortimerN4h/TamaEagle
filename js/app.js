// Main JavaScript for Todoist Clone
document.addEventListener('DOMContentLoaded', function() {
    // Initialize data store
    const dataStore = new DataStore();
    
    // Initialize UI controller
    const uiController = new UIController();
    
    // Create UI updater object to handle UI updates from controllers
    const uiUpdater = {
        onTaskAdded: (task) => uiController.onTaskAdded(task),
        onTaskUpdated: (task) => uiController.onTaskUpdated(task),
        onTaskStatusChanged: (task, isCompleted) => uiController.onTaskStatusChanged(task, isCompleted),
        onTaskDeleted: (taskId) => uiController.onTaskDeleted(taskId),
        onProjectAdded: (project) => uiController.onProjectAdded(project),
        onProjectUpdated: (project) => uiController.onProjectUpdated(project),
        onProjectDeleted: (projectId) => uiController.onProjectDeleted(projectId)
    };
    
    // Initialize task and project controllers
    const taskController = new TaskController(dataStore, uiUpdater);
    const projectController = new ProjectController(dataStore, uiUpdater);
    
    // Set controllers in UI
    uiController.setControllers(taskController, projectController);
    
    // Initialize UI
    uiController.initialize();
    
    // Log initialization
    console.log('Todoist clone application initialized!');
});
