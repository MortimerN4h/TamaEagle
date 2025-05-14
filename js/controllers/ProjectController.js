// ProjectController class manages the business logic for projects
class ProjectController {
    constructor(dataStore, uiUpdater) {
        this.dataStore = dataStore;
        this.uiUpdater = uiUpdater;
    }

    // Get all projects
    getProjects() {
        return this.dataStore.getProjects();
    }

    // Get project by ID
    getProject(projectId) {
        return this.dataStore.getProjectById(projectId);
    }

    // Create a new project
    createProject(name, color) {
        const project = new Project(
            null, // id will be auto-generated
            name,
            color
        );
        
        this.dataStore.addProject(project);
        this.uiUpdater.onProjectAdded(project);
        return project;
    }

    // Update project details
    updateProject(projectId, updates) {
        const project = this.dataStore.getProjectById(projectId);
        if (!project) return false;
        
        // Apply updates to project object
        if (updates.name !== undefined) project.setName(updates.name);
        if (updates.color !== undefined) project.setColor(updates.color);
        
        // Save the updated project
        const success = this.dataStore.updateProject(project);
        if (success) {
            this.uiUpdater.onProjectUpdated(project);
        }
        return success;
    }

    // Delete a project
    deleteProject(projectId) {
        const success = this.dataStore.deleteProject(projectId);
        if (success) {
            this.uiUpdater.onProjectDeleted(projectId);
        }
        return success;
    }

    // Generate a random color for new projects
    generateRandomColor() {
        const colors = [
            '#246fe0', // blue
            '#ff9900', // orange
            '#5297ff', // light blue
            '#fc5a5a', // red
            '#9059ff', // purple
            '#4eccc6', // teal
            '#d876e3', // pink
            '#a2a1a1'  // gray
        ];
        
        return colors[Math.floor(Math.random() * colors.length)];
    }
}
