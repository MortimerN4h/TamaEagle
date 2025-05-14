// Data store for managing tasks and projects using localStorage
class DataStore {
    constructor() {
        this.tasks = [];
        this.projects = [];
        this.loadFromLocalStorage();
        
        // If no projects exist, create default projects
        if (this.projects.length === 0) {
            this.initializeDefaultProjects();
        }
    }

    initializeDefaultProjects() {
        const inboxProject = new Project('inbox', 'Inbox', '#246fe0');
        this.addProject(inboxProject);
        
        const personalProject = new Project('personal', 'Personal', '#ff9900');
        this.addProject(personalProject);
        
        const workProject = new Project('work', 'Work', '#5297ff');
        this.addProject(workProject);
        
        const shoppingProject = new Project('shopping', 'Shopping', '#fc5a5a');
        this.addProject(shoppingProject);
        
        this.saveToLocalStorage();
    }

    // Load data from localStorage
    loadFromLocalStorage() {
        try {
            // Load projects
            const projectsJSON = localStorage.getItem('todoistProjects');
            if (projectsJSON) {
                const projectsData = JSON.parse(projectsJSON);
                this.projects = projectsData.map(project => Project.fromJSON(project));
            }
            
            // Load tasks
            const tasksJSON = localStorage.getItem('todoistTasks');
            if (tasksJSON) {
                const tasksData = JSON.parse(tasksJSON);
                this.tasks = tasksData.map(task => Task.fromJSON(task));
            }
        } catch (e) {
            console.error('Error loading data from localStorage:', e);
            this.tasks = [];
            this.projects = [];
        }
    }

    // Save data to localStorage
    saveToLocalStorage() {
        try {
            localStorage.setItem('todoistProjects', JSON.stringify(this.projects));
            localStorage.setItem('todoistTasks', JSON.stringify(this.tasks));
        } catch (e) {
            console.error('Error saving data to localStorage:', e);
        }
    }

    // Project methods
    getProjects() {
        return [...this.projects];
    }

    getProjectById(id) {
        return this.projects.find(project => project.id === id) || null;
    }

    addProject(project) {
        this.projects.push(project);
        this.saveToLocalStorage();
        return project;
    }

    updateProject(updatedProject) {
        const index = this.projects.findIndex(project => project.id === updatedProject.id);
        if (index !== -1) {
            this.projects[index] = updatedProject;
            this.saveToLocalStorage();
            return true;
        }
        return false;
    }

    deleteProject(projectId) {
        const initialLength = this.projects.length;
        this.projects = this.projects.filter(project => project.id !== projectId);
        
        // If project was deleted, also delete all tasks in that project
        if (initialLength > this.projects.length) {
            this.tasks = this.tasks.filter(task => task.projectId !== projectId);
            this.saveToLocalStorage();
            return true;
        }
        return false;
    }

    // Task methods
    getTasks() {
        return [...this.tasks];
    }

    getTaskById(id) {
        return this.tasks.find(task => task.id === id) || null;
    }

    getTasksByProject(projectId) {
        return this.tasks.filter(task => task.projectId === projectId);
    }

    getCompletedTasks() {
        return this.tasks.filter(task => task.completed);
    }

    getTasksForToday() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        return this.tasks.filter(task => {
            if (task.completed) return false;
            
            if (task.deadline) {
                const deadlineDate = new Date(task.deadline);
                deadlineDate.setHours(0, 0, 0, 0);
                
                // Include tasks due today or overdue
                return deadlineDate <= today;
            }
            return false;
        });
    }

    getTasksForUpcoming() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        return this.tasks.filter(task => {
            if (task.completed) return false;
            
            if (task.startDate) {
                const startDate = new Date(task.startDate);
                startDate.setHours(0, 0, 0, 0);
                return startDate >= today;
            } else if (task.deadline) {
                const deadlineDate = new Date(task.deadline);
                deadlineDate.setHours(0, 0, 0, 0);
                return deadlineDate > today;
            }
            return false;
        }).sort((a, b) => {
            // Sort by start date first, then by deadline
            const aDate = a.startDate ? new Date(a.startDate) : (a.deadline ? new Date(a.deadline) : new Date(9999, 11, 31));
            const bDate = b.startDate ? new Date(b.startDate) : (b.deadline ? new Date(b.deadline) : new Date(9999, 11, 31));
            return aDate - bDate;
        });
    }

    getOverdueTasks() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        return this.tasks.filter(task => {
            if (task.completed) return false;
            
            if (task.deadline) {
                const deadlineDate = new Date(task.deadline);
                deadlineDate.setHours(0, 0, 0, 0);
                return deadlineDate < today;
            }
            return false;
        });
    }

    addTask(task) {
        this.tasks.push(task);
        this.saveToLocalStorage();
        return task;
    }

    updateTask(updatedTask) {
        const index = this.tasks.findIndex(task => task.id === updatedTask.id);
        if (index !== -1) {
            this.tasks[index] = updatedTask;
            this.saveToLocalStorage();
            return true;
        }
        return false;
    }

    toggleTaskComplete(taskId) {
        const task = this.getTaskById(taskId);
        if (task) {
            task.toggleComplete();
            this.saveToLocalStorage();
            return task.completed;
        }
        return null;
    }

    deleteTask(taskId) {
        const initialLength = this.tasks.length;
        this.tasks = this.tasks.filter(task => task.id !== taskId);
        
        if (initialLength > this.tasks.length) {
            this.saveToLocalStorage();
            return true;
        }
        return false;
    }
}
