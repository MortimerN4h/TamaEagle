// UIController manages the DOM manipulations and event handling
class UIController {
    constructor() {
        // Element references
        this.sidebar = document.getElementById('sidebar');
        this.contentTitle = document.querySelector('.content-title h1');
        this.tasksList = document.getElementById('tasksList');
        this.addTaskBtn = document.querySelector('.add-task-btn');
        this.addProjectBtn = document.querySelector('.add-project-btn');
        this.navItems = document.querySelectorAll('.nav-item');
        this.projectItems = document.querySelectorAll('.project-item');
        
        // Initialize sortable tasks list if it exists
        if (this.tasksList) {
            this.initSortable();
        }
        
        // Current view tracking
        this.currentView = 'inbox';
        this.currentProjectId = 'inbox';
        
        // Modal elements (will be created dynamically)
        this.taskModal = null;
        this.projectModal = null;
        
        // Task and Project controllers will be set later
        this.taskController = null;
        this.projectController = null;
    }

    setControllers(taskController, projectController) {
        this.taskController = taskController;
        this.projectController = projectController;
    }

    // Initialize the UI
    initialize() {
        this.initEventListeners();
        this.showView('inbox', 'Inbox');
        this.renderProjects();
    }

    // Initialize Sortable.js for drag and drop
    initSortable() {
        this.sortable = new Sortable(this.tasksList, {
            animation: 150,
            handle: '.task-drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: (evt) => {
                // Get the task ID from the dragged element
                const taskId = evt.item.getAttribute('data-id');
                
                // You can save the new order to storage or send to backend
                console.log('Reordered task:', taskId, 'New position:', evt.newIndex);
            }
        });
    }

    // Set up event listeners
    initEventListeners() {
        // Toggle sidebar
        document.getElementById('menuToggle').addEventListener('click', () => {
            this.sidebar.classList.toggle('collapsed');
        });
        
        // Navigation links
        this.navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const view = item.querySelector('.nav-link').getAttribute('data-view');
                const title = item.querySelector('.nav-link span').textContent;
                this.showView(view, title);
            });
        });
        
        // Project items
        this.projectItems.forEach(item => {
            item.addEventListener('click', () => {
                const projectId = item.getAttribute('data-id');
                const projectName = item.querySelector('span').textContent;
                this.showProjectView(projectId, projectName);
            });
        });
        
        // Add task button
        this.addTaskBtn.addEventListener('click', () => {
            this.showAddTaskModal();
        });
        
        // Add project button
        this.addProjectBtn.addEventListener('click', () => {
            this.showAddProjectModal();
        });
    }

    // Show a specific view (today, upcoming, completed, inbox)
    showView(view, title) {
        // Clear active class from all nav items
        this.navItems.forEach(item => item.classList.remove('active'));
        this.projectItems.forEach(item => item.classList.remove('active'));
        
        // Set active class on selected view
        const selectedNav = Array.from(this.navItems).find(
            item => item.querySelector('.nav-link').getAttribute('data-view') === view
        );
        
        if (selectedNav) {
            selectedNav.classList.add('active');
        }
        
        // Update content title
        this.contentTitle.textContent = title;
        
        // Update current view
        this.currentView = view;
        this.currentProjectId = view === 'project' ? this.currentProjectId : null;
        
        // Load tasks based on view
        this.loadTasks();
    }

    // Show a specific project view
    showProjectView(projectId, projectName) {
        // Clear active class from all nav items
        this.navItems.forEach(item => item.classList.remove('active'));
        this.projectItems.forEach(item => item.classList.remove('active'));
        
        // Set active class on selected project
        const selectedProject = Array.from(this.projectItems).find(
            item => item.getAttribute('data-id') === projectId
        );
        
        if (selectedProject) {
            selectedProject.classList.add('active');
        }
        
        // Update content title
        this.contentTitle.textContent = projectName;
        
        // Update current view
        this.currentView = 'project';
        this.currentProjectId = projectId;
        
        // Load tasks for this project
        this.loadTasks();
    }

    // Load tasks based on current view
    loadTasks() {
        // Clear existing tasks
        this.tasksList.innerHTML = '';
        
        // Get tasks for current view
        const tasks = this.taskController.getTasks(this.currentView, this.currentProjectId);
        
        // Render tasks
        if (tasks.length === 0) {
            this.renderEmptyState();
        } else {
            tasks.forEach(task => this.renderTaskItem(task));
        }
    }

    // Render a task item in the list
    renderTaskItem(task) {
        const projectId = task.projectId;
        const project = this.projectController.getProject(projectId);
        const projectName = project ? project.name : 'No Project';
        const projectColor = project ? project.color : '#808080';
        
        const taskElement = document.createElement('div');
        taskElement.classList.add('task-item');
        taskElement.setAttribute('data-id', task.id);
        
        // Add completed class if task is completed
        if (task.completed) {
            taskElement.classList.add('completed');
        }
        
        // Add priority class
        taskElement.classList.add(`priority-${task.priority}`);
        
        // Add overdue class if task is overdue
        if (task.isOverdue()) {
            taskElement.classList.add('overdue');
        }
        
        taskElement.innerHTML = `
            <div class="task-drag-handle">
                <i class="fas fa-grip-lines"></i>
            </div>
            <div class="task-checkbox">
                <input type="checkbox" id="task-${task.id}" ${task.completed ? 'checked' : ''}>
                <label for="task-${task.id}"></label>
            </div>
            <div class="task-content">
                <div class="task-text">${task.name}</div>
                <div class="task-details">
                    <span class="task-project" style="color: ${projectColor}">
                        <span class="color-dot" style="background-color: ${projectColor}"></span>
                        ${projectName}
                    </span>
                    ${task.deadline ? 
                        `<span class="task-date ${task.isOverdue() ? 'overdue' : ''}">${task.getRemainingTime()}</span>` 
                        : ''}
                </div>
            </div>
            <div class="task-actions">
                <button class="task-edit-btn" aria-label="Edit task">
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <button class="task-delete-btn" aria-label="Delete task">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        // Add event listeners
        const checkbox = taskElement.querySelector('input[type="checkbox"]');
        checkbox.addEventListener('change', () => {
            this.taskController.toggleTaskCompletion(task.id);
        });
        
        taskElement.querySelector('.task-edit-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            this.showEditTaskModal(task.id);
        });
        
        taskElement.querySelector('.task-delete-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            this.confirmDeleteTask(task.id);
        });
        
        // Click on task text to edit
        taskElement.querySelector('.task-content').addEventListener('click', () => {
            this.showEditTaskModal(task.id);
        });
        
        this.tasksList.appendChild(taskElement);
    }

    // Render empty state message
    renderEmptyState() {
        const emptyState = document.createElement('div');
        emptyState.classList.add('empty-state');
        
        let message = '';
        switch (this.currentView) {
            case 'today':
                message = 'No tasks due today! 🎉';
                break;
            case 'upcoming':
                message = 'No upcoming tasks';
                break;
            case 'completed':
                message = 'No completed tasks yet';
                break;
            case 'project':
                message = 'No tasks in this project yet';
                break;
            default:
                message = 'No tasks yet';
        }
        
        emptyState.innerHTML = `
            <div class="empty-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <p class="empty-message">${message}</p>
            <button class="add-task-empty-btn">
                <i class="fas fa-plus"></i>
                <span>Add Task</span>
            </button>
        `;
        
        emptyState.querySelector('.add-task-empty-btn').addEventListener('click', () => {
            this.showAddTaskModal();
        });
        
        this.tasksList.appendChild(emptyState);
    }

    // Render the projects list in sidebar
    renderProjects() {
        const projectsList = document.querySelector('.projects-list');
        projectsList.innerHTML = '';
        
        const projects = this.projectController.getProjects();
        
        projects.forEach(project => {
            const projectItem = document.createElement('li');
            projectItem.classList.add('project-item');
            projectItem.setAttribute('data-id', project.id);
            
            if (this.currentView === 'project' && this.currentProjectId === project.id) {
                projectItem.classList.add('active');
            }
            
            projectItem.innerHTML = `
                <div class="color-marker" style="background-color: ${project.color};"></div>
                <span>${project.name}</span>
                <div class="project-actions">
                    <button class="project-edit-btn" aria-label="Edit project">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="project-delete-btn" aria-label="Delete project">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            
            // Add event listeners
            projectItem.addEventListener('click', (e) => {
                if (!e.target.closest('.project-actions')) {
                    this.showProjectView(project.id, project.name);
                }
            });
            
            projectItem.querySelector('.project-edit-btn').addEventListener('click', (e) => {
                e.stopPropagation();
                this.showEditProjectModal(project.id);
            });
            
            projectItem.querySelector('.project-delete-btn').addEventListener('click', (e) => {
                e.stopPropagation();
                this.confirmDeleteProject(project.id);
            });
            
            projectsList.appendChild(projectItem);
        });
    }

    // Show modal for adding a new task
    showAddTaskModal() {
        const modal = document.createElement('div');
        modal.classList.add('modal');
        
        const projects = this.projectController.getProjects();
        const dateOptions = this.taskController.getQuickDateOptions();
        const defaultProjectId = this.currentView === 'project' ? this.currentProjectId : 'inbox';
        
        modal.innerHTML = `
            <div class="modal-backdrop"></div>
            <div class="modal-container">
                <div class="modal-header">
                    <h2>Add New Task</h2>
                    <button class="modal-close-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="add-task-form">
                        <div class="form-group">
                            <label for="task-name">Task Name*</label>
                            <input type="text" id="task-name" class="form-control" placeholder="What needs to be done?" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="task-description">Description</label>
                            <textarea id="task-description" class="form-control" placeholder="Add details"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="task-project">Project</label>
                            <select id="task-project" class="form-control">
                                ${projects.map(project => `
                                    <option value="${project.id}" ${project.id === defaultProjectId ? 'selected' : ''}>
                                        ${project.name}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        
                        <div class="date-fields">
                            <div class="form-group">
                                <label for="task-start-date">Start Date</label>
                                <input type="date" id="task-start-date" class="form-control">
                                <div class="quick-date-buttons">
                                    <button type="button" class="quick-date-btn" data-date="${dateOptions.today}">Today</button>
                                    <button type="button" class="quick-date-btn" data-date="${dateOptions.tomorrow}">Tomorrow</button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="task-deadline">Deadline</label>
                                <input type="date" id="task-deadline" class="form-control">
                                <div class="quick-date-buttons">
                                    <button type="button" class="quick-date-btn" data-date="${dateOptions.today}">Today</button>
                                    <button type="button" class="quick-date-btn" data-date="${dateOptions.tomorrow}">Tomorrow</button>
                                    <button type="button" class="quick-date-btn" data-date="${dateOptions.nextWeek}">Next Week</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Priority</label>
                            <div class="priority-buttons">
                                <label class="priority-btn">
                                    <input type="radio" name="task-priority" value="low">
                                    <span class="priority-label low">Low</span>
                                </label>
                                <label class="priority-btn">
                                    <input type="radio" name="task-priority" value="medium" checked>
                                    <span class="priority-label medium">Medium</span>
                                </label>
                                <label class="priority-btn">
                                    <input type="radio" name="task-priority" value="high">
                                    <span class="priority-label high">High</span>
                                </label>
                                <label class="priority-btn">
                                    <input type="radio" name="task-priority" value="urgent">
                                    <span class="priority-label urgent">Urgent</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-cancel">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Task</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        this.taskModal = modal;
        
        // Focus the name input
        setTimeout(() => {
            modal.querySelector('#task-name').focus();
        }, 100);
        
        // Add event listener for quick date buttons
        modal.querySelectorAll('.quick-date-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const dateValue = btn.getAttribute('data-date');
                const dateField = btn.closest('.form-group').querySelector('input[type="date"]');
                dateField.value = dateValue;
            });
        });
        
        // Close modal on backdrop click or close button
        modal.querySelector('.modal-backdrop').addEventListener('click', () => {
            this.closeTaskModal();
        });
        
        modal.querySelector('.modal-close-btn').addEventListener('click', () => {
            this.closeTaskModal();
        });
        
        modal.querySelector('.btn-cancel').addEventListener('click', () => {
            this.closeTaskModal();
        });
        
        // Form submission
        const form = modal.querySelector('#add-task-form');
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const name = form.querySelector('#task-name').value.trim();
            const description = form.querySelector('#task-description').value.trim();
            const projectId = form.querySelector('#task-project').value;
            const startDate = form.querySelector('#task-start-date').value || null;
            const deadline = form.querySelector('#task-deadline').value || null;
            const priority = form.querySelector('input[name="task-priority"]:checked').value;
            
            if (name) {
                this.taskController.createTask(
                    name,
                    description,
                    projectId,
                    startDate,
                    deadline,
                    priority
                );
                
                this.closeTaskModal();
                this.loadTasks();
            }
        });
    }

    // Show modal for editing an existing task
    showEditTaskModal(taskId) {
        const task = this.taskController.getTask(taskId);
        if (!task) return;
        
        const modal = document.createElement('div');
        modal.classList.add('modal');
        
        const projects = this.projectController.getProjects();
        const dateOptions = this.taskController.getQuickDateOptions();
        
        modal.innerHTML = `
            <div class="modal-backdrop"></div>
            <div class="modal-container">
                <div class="modal-header">
                    <h2>Edit Task</h2>
                    <button class="modal-close-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="edit-task-form">
                        <div class="form-group">
                            <label for="task-name">Task Name*</label>
                            <input type="text" id="task-name" class="form-control" placeholder="What needs to be done?" value="${task.name}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="task-description">Description</label>
                            <textarea id="task-description" class="form-control" placeholder="Add details">${task.description || ''}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="task-project">Project</label>
                            <select id="task-project" class="form-control">
                                ${projects.map(project => `
                                    <option value="${project.id}" ${project.id === task.projectId ? 'selected' : ''}>
                                        ${project.name}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        
                        <div class="date-fields">
                            <div class="form-group">
                                <label for="task-start-date">Start Date</label>
                                <input type="date" id="task-start-date" class="form-control" value="${task.startDate || ''}">
                                <div class="quick-date-buttons">
                                    <button type="button" class="quick-date-btn" data-date="${dateOptions.today}">Today</button>
                                    <button type="button" class="quick-date-btn" data-date="${dateOptions.tomorrow}">Tomorrow</button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="task-deadline">Deadline</label>
                                <input type="date" id="task-deadline" class="form-control" value="${task.deadline || ''}">
                                <div class="quick-date-buttons">
                                    <button type="button" class="quick-date-btn" data-date="${dateOptions.today}">Today</button>
                                    <button type="button" class="quick-date-btn" data-date="${dateOptions.tomorrow}">Tomorrow</button>
                                    <button type="button" class="quick-date-btn" data-date="${dateOptions.nextWeek}">Next Week</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Priority</label>
                            <div class="priority-buttons">
                                <label class="priority-btn">
                                    <input type="radio" name="task-priority" value="low" ${task.priority === 'low' ? 'checked' : ''}>
                                    <span class="priority-label low">Low</span>
                                </label>
                                <label class="priority-btn">
                                    <input type="radio" name="task-priority" value="medium" ${task.priority === 'medium' ? 'checked' : ''}>
                                    <span class="priority-label medium">Medium</span>
                                </label>
                                <label class="priority-btn">
                                    <input type="radio" name="task-priority" value="high" ${task.priority === 'high' ? 'checked' : ''}>
                                    <span class="priority-label high">High</span>
                                </label>
                                <label class="priority-btn">
                                    <input type="radio" name="task-priority" value="urgent" ${task.priority === 'urgent' ? 'checked' : ''}>
                                    <span class="priority-label urgent">Urgent</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Status</label>
                            <div class="status-switch">
                                <label class="switch">
                                    <input type="checkbox" id="task-completed" ${task.completed ? 'checked' : ''}>
                                    <span class="slider round"></span>
                                </label>
                                <span class="status-label">${task.completed ? 'Completed' : 'In Progress'}</span>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-delete">Delete</button>
                            <div class="right-actions">
                                <button type="button" class="btn btn-cancel">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        this.taskModal = modal;
        
        // Add event listener for quick date buttons
        modal.querySelectorAll('.quick-date-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const dateValue = btn.getAttribute('data-date');
                const dateField = btn.closest('.form-group').querySelector('input[type="date"]');
                dateField.value = dateValue;
            });
        });
        
        // Status switch event
        const statusSwitch = modal.querySelector('#task-completed');
        const statusLabel = modal.querySelector('.status-label');
        
        statusSwitch.addEventListener('change', () => {
            statusLabel.textContent = statusSwitch.checked ? 'Completed' : 'In Progress';
        });
        
        // Close modal on backdrop click or close button
        modal.querySelector('.modal-backdrop').addEventListener('click', () => {
            this.closeTaskModal();
        });
        
        modal.querySelector('.modal-close-btn').addEventListener('click', () => {
            this.closeTaskModal();
        });
        
        modal.querySelector('.btn-cancel').addEventListener('click', () => {
            this.closeTaskModal();
        });
        
        // Delete button
        modal.querySelector('.btn-delete').addEventListener('click', () => {
            this.closeTaskModal();
            this.confirmDeleteTask(taskId);
        });
        
        // Form submission
        const form = modal.querySelector('#edit-task-form');
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const updates = {
                name: form.querySelector('#task-name').value.trim(),
                description: form.querySelector('#task-description').value.trim(),
                projectId: form.querySelector('#task-project').value,
                startDate: form.querySelector('#task-start-date').value || null,
                deadline: form.querySelector('#task-deadline').value || null,
                priority: form.querySelector('input[name="task-priority"]:checked').value
            };
            
            // Update completed status directly through the controller
            const isNowCompleted = form.querySelector('#task-completed').checked;
            if (isNowCompleted !== task.completed) {
                this.taskController.toggleTaskCompletion(taskId);
            }
            
            if (updates.name) {
                this.taskController.updateTask(taskId, updates);
                this.closeTaskModal();
                this.loadTasks();
            }
        });
    }

    // Close the task modal
    closeTaskModal() {
        if (this.taskModal) {
            document.body.removeChild(this.taskModal);
            this.taskModal = null;
        }
    }

    // Confirm deletion of a task
    confirmDeleteTask(taskId) {
        if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
            this.taskController.deleteTask(taskId);
            this.loadTasks();
        }
    }

    // Show modal for adding a new project
    showAddProjectModal() {
        const modal = document.createElement('div');
        modal.classList.add('modal');
        
        const randomColor = this.projectController.generateRandomColor();
        
        modal.innerHTML = `
            <div class="modal-backdrop"></div>
            <div class="modal-container">
                <div class="modal-header">
                    <h2>Add New Project</h2>
                    <button class="modal-close-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="add-project-form">
                        <div class="form-group">
                            <label for="project-name">Project Name*</label>
                            <input type="text" id="project-name" class="form-control" placeholder="Enter project name" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Color</label>
                            <div class="color-picker">
                                <input type="color" id="project-color" value="${randomColor}">
                                <div class="color-preview" style="background-color: ${randomColor};"></div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-cancel">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Project</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        this.projectModal = modal;
        
        // Color picker preview
        const colorInput = modal.querySelector('#project-color');
        const colorPreview = modal.querySelector('.color-preview');
        
        colorInput.addEventListener('input', () => {
            colorPreview.style.backgroundColor = colorInput.value;
        });
        
        // Focus the name input
        setTimeout(() => {
            modal.querySelector('#project-name').focus();
        }, 100);
        
        // Close modal on backdrop click or close button
        modal.querySelector('.modal-backdrop').addEventListener('click', () => {
            this.closeProjectModal();
        });
        
        modal.querySelector('.modal-close-btn').addEventListener('click', () => {
            this.closeProjectModal();
        });
        
        modal.querySelector('.btn-cancel').addEventListener('click', () => {
            this.closeProjectModal();
        });
        
        // Form submission
        const form = modal.querySelector('#add-project-form');
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const name = form.querySelector('#project-name').value.trim();
            const color = form.querySelector('#project-color').value;
            
            if (name) {
                this.projectController.createProject(name, color);
                this.closeProjectModal();
                this.renderProjects();
            }
        });
    }

    // Show modal for editing an existing project
    showEditProjectModal(projectId) {
        const project = this.projectController.getProject(projectId);
        if (!project) return;
        
        const modal = document.createElement('div');
        modal.classList.add('modal');
        
        modal.innerHTML = `
            <div class="modal-backdrop"></div>
            <div class="modal-container">
                <div class="modal-header">
                    <h2>Edit Project</h2>
                    <button class="modal-close-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="edit-project-form">
                        <div class="form-group">
                            <label for="project-name">Project Name*</label>
                            <input type="text" id="project-name" class="form-control" placeholder="Enter project name" value="${project.name}" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Color</label>
                            <div class="color-picker">
                                <input type="color" id="project-color" value="${project.color}">
                                <div class="color-preview" style="background-color: ${project.color};"></div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-delete">Delete</button>
                            <div class="right-actions">
                                <button type="button" class="btn btn-cancel">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        this.projectModal = modal;
        
        // Color picker preview
        const colorInput = modal.querySelector('#project-color');
        const colorPreview = modal.querySelector('.color-preview');
        
        colorInput.addEventListener('input', () => {
            colorPreview.style.backgroundColor = colorInput.value;
        });
        
        // Close modal on backdrop click or close button
        modal.querySelector('.modal-backdrop').addEventListener('click', () => {
            this.closeProjectModal();
        });
        
        modal.querySelector('.modal-close-btn').addEventListener('click', () => {
            this.closeProjectModal();
        });
        
        modal.querySelector('.btn-cancel').addEventListener('click', () => {
            this.closeProjectModal();
        });
        
        // Delete button
        modal.querySelector('.btn-delete').addEventListener('click', () => {
            this.closeProjectModal();
            this.confirmDeleteProject(projectId);
        });
        
        // Form submission
        const form = modal.querySelector('#edit-project-form');
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const updates = {
                name: form.querySelector('#project-name').value.trim(),
                color: form.querySelector('#project-color').value
            };
            
            if (updates.name) {
                this.projectController.updateProject(projectId, updates);
                this.closeProjectModal();
                
                if (this.currentView === 'project' && this.currentProjectId === projectId) {
                    this.contentTitle.textContent = updates.name;
                }
                
                this.renderProjects();
            }
        });
    }

    // Close the project modal
    closeProjectModal() {
        if (this.projectModal) {
            document.body.removeChild(this.projectModal);
            this.projectModal = null;
        }
    }

    // Confirm deletion of a project
    confirmDeleteProject(projectId) {
        if (confirm('Are you sure you want to delete this project? All tasks in this project will also be deleted. This action cannot be undone.')) {
            this.projectController.deleteProject(projectId);
            
            if (this.currentView === 'project' && this.currentProjectId === projectId) {
                this.showView('inbox', 'Inbox');
            } else {
                this.renderProjects();
                this.loadTasks();
            }
        }
    }

    // UI Updater methods - called by controllers
    onTaskAdded(task) {
        if ((this.currentView === 'project' && task.projectId === this.currentProjectId) ||
            (this.currentView === 'today' && task.isDueToday()) ||
            (this.currentView === 'upcoming' && task.startDate) ||
            (this.currentView === 'inbox' && task.projectId === 'inbox')) {
            this.loadTasks();
        }
    }

    onTaskUpdated(task) {
        this.loadTasks();
    }

    onTaskStatusChanged(task, isCompleted) {
        const taskElement = this.tasksList.querySelector(`.task-item[data-id="${task.id}"]`);
        
        if (taskElement) {
            if (isCompleted) {
                taskElement.classList.add('completed');
                taskElement.style.textDecoration = 'line-through';
                taskElement.style.color = '#808080';
                
                // If in completed view, keep it; otherwise, animate it away
                if (this.currentView !== 'completed') {
                    setTimeout(() => {
                        taskElement.style.opacity = '0';
                        setTimeout(() => {
                            this.loadTasks();
                        }, 300);
                    }, 1000);
                }
            } else {
                taskElement.classList.remove('completed');
                taskElement.style.textDecoration = 'none';
                taskElement.style.color = '';
            }
        } else {
            this.loadTasks();
        }
    }

    onTaskDeleted(taskId) {
        const taskElement = this.tasksList.querySelector(`.task-item[data-id="${taskId}"]`);
        
        if (taskElement) {
            taskElement.style.opacity = '0';
            setTimeout(() => {
                this.loadTasks();
            }, 300);
        } else {
            this.loadTasks();
        }
    }

    onProjectAdded(project) {
        this.renderProjects();
    }

    onProjectUpdated(project) {
        this.renderProjects();
        this.loadTasks(); // To update project colors on tasks
    }

    onProjectDeleted(projectId) {
        this.renderProjects();
        this.loadTasks();
    }
}
